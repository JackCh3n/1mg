<?php
/**
 * 前台用户会话(注册/登录/注销),与管理员会话相互独立
 * 依赖 config.php(已session_start,提供$db)与 auth.php(csrf_token/csrf_verify)
 */

/**
 * 用户注册
 * @param  string $username
 * @param  string $password
 * @param  string $confirm
 * @return string 错误信息，空=成功
 */
function user_register($username, $password, $confirm){
	$username=trim((string)$username);
	if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
		return '用户名格式不正确，需为 3-32 位字母、数字或下划线';
	}
	// 强化密码策略：至少 12 字符，包含字母和数字
	$password = (string)$password;
	if (strlen($password) < 12 || strlen($password) > 64) {
		return '密码长度需在 12 到 64 位之间';
	}
	if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
		return '密码必须同时包含字母和数字';
	}
	if ($password !== $confirm) {
		return '两次输入的密码不一致';
	}
	// 检查常见弱密码（简单字典）
	$weak_passwords = ['password', '12345678', 'qwerty', 'abc123', 'admin123', 'letmein', 'welcome', 'monkey', 'dragon', 'master'];
	if (in_array(strtolower($password), $weak_passwords)) {
		return '密码过于简单，请使用更复杂的组合';
	}
	//注册限速：每 IP 每小时最多 5 次 (复用 rate_limit 表，槽位加 reg 前缀)
	try {
		$pdo=$GLOBALS['db']->pdo;
		$slot='reg'.date('YmdH');
		$st=$pdo->prepare("INSERT INTO rate_limit (ip, slot, count) VALUES (?, ?, 1)
			ON CONFLICT(ip, slot) DO UPDATE SET count = count + 1");
		$st->execute([(string)get_client_ip(), $slot]);
		$q=$pdo->prepare("SELECT count FROM rate_limit WHERE ip = ? AND slot = ?");
		$q->execute([(string)get_client_ip(), $slot]);
		if ((int)$q->fetchColumn() > 5) {
			return '注册请求太频繁，请稍后再试';
		}
	} catch (Exception $e) {
		//限速故障不阻塞注册
	}
	$exists=$GLOBALS['db']->get('users','id',['username'=>$username]);
	if (!empty($exists)) {
		return '该用户名已被占用，请更换其他用户名';
	}
	try {
		$GLOBALS['db']->insert('users',[
			'username'=>$username,
			'password_hash'=>password_hash($password, PASSWORD_DEFAULT),
			'created'=>date('Y-m-d H:i:s'),
		]);
	} catch (Exception $e) {
		//并发注册同名：唯一约束兜底
		return '该用户名已被占用，请更换其他用户名';
	}
	return '';
}

/**
 * 用户登录 (会话级限速：连续失败 5 次锁定 10 分钟)
 * @param  string $username
 * @param  string $password
 * @return string 错误信息，空=成功
 */
function user_login($username, $password){
	$now=time();
	$fails=isset($_SESSION['user_fails'])?$_SESSION['user_fails']:0;
	$lock_until=isset($_SESSION['user_lock_until'])?$_SESSION['user_lock_until']:0;
	if ($fails>=5 && $now<$lock_until) {
		return '登录尝试过多，请 10 分钟后再试';
	}
	if ($fails>=5) {
		$_SESSION['user_fails']=0;
	}
	$row=$GLOBALS['db']->get('users',['id','username','password_hash'],['username'=>trim((string)$username)]);
	if (empty($row) || !password_verify((string)$password, $row['password_hash'])) {
		$_SESSION['user_fails']=$fails+1;
		if ($_SESSION['user_fails']>=5) {
			$_SESSION['user_lock_until']=$now+600;
		}
		return '用户名或密码错误';
	}
	$_SESSION['user_fails']=0;
	unset($_SESSION['user_lock_until']);
	session_regenerate_id(true);
	$_SESSION['user_id']=(int)$row['id'];
	$_SESSION['user_name']=$row['username'];
	$GLOBALS['db']->update('users',['last_login'=>date('Y-m-d H:i:s')],['id'=>$row['id']]);
	return '';
}

/**
 * 当前登录用户名(未登录返回空)
 * @return string
 */
function user_name(){
	return isset($_SESSION['user_name'])?$_SESSION['user_name']:'';
}

/**
 * 用户注销
 */
function user_logout(){
	unset($_SESSION['user_id'], $_SESSION['user_name']);
	session_regenerate_id(true);
}
