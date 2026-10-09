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
 * @return string 错误信息,空=成功
 */
function user_register($username, $password, $confirm){
	$username=trim((string)$username);
	if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
		return '用户名需为3-32位字母/数字/下划线';
	}
	if (strlen((string)$password)<6 || strlen((string)$password)>64) {
		return '密码长度需在6到64位之间';
	}
	if ($password!==$confirm) {
		return '两次输入的密码不一致';
	}
	$exists=$GLOBALS['db']->get('users','id',['username'=>$username]);
	if (!empty($exists)) {
		return '用户名已被占用';
	}
	$GLOBALS['db']->insert('users',[
		'username'=>$username,
		'password_hash'=>password_hash($password, PASSWORD_DEFAULT),
		'created'=>date('Y-m-d H:i:s'),
	]);
	return '';
}

/**
 * 用户登录(会话级限速:连续失败5次锁定10分钟)
 * @param  string $username
 * @param  string $password
 * @return string 错误信息,空=成功
 */
function user_login($username, $password){
	$now=time();
	$fails=isset($_SESSION['user_fails'])?$_SESSION['user_fails']:0;
	$lock_until=isset($_SESSION['user_lock_until'])?$_SESSION['user_lock_until']:0;
	if ($fails>=5 && $now<$lock_until) {
		return '失败次数过多,请10分钟后再试';
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
