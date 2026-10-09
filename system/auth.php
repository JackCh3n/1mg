<?php
/**
 * 管理员会话鉴权 + CSRF
 * 用法:在 admin 下每个页面顶部 require 后调用 admin_require_login()
 * 依赖 config.php(已 session_start, 提供 $db)
 */

/**
 * 当前CSRF token(会话级)
 * @return string
 */
function csrf_token(){
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token']=bin2hex(random_bytes(16));
	}
	return $_SESSION['csrf_token'];
}

/**
 * 校验请求携带的CSRF token
 * @param  string $token 表单里的 _csrf
 * @return bool
 */
function csrf_verify($token){
	return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'],$token);
}

/**
 * 所有后台页面与写操作统一入口校验,未登录跳转到登录页
 */
function admin_require_login(){
	if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_user'])) {
		header('Location: login.php');
		exit();
	}
	//每次请求都校验登录是否还有效(密码修改/账号被删后旧会话立即失效)
	$row=$GLOBALS['db']->get('admin',['id'],['id'=>$_SESSION['admin_id'],'username'=>$_SESSION['admin_user']]);
	if (empty($row) || $GLOBALS['db']->error()[1]) {
		session_destroy();
		header('Location: login.php');
		exit();
	}
}

/**
 * 登录第一步:校验账号密码,带简单的会话级限速:连续失败5次锁定10分钟
 * @param  string $username
 * @param  string $password
 * @return string ok=登录成功 otp=需要两步验证 bad=账号或密码错误/被锁定
 */
function admin_login($username,$password){
	$username=(string)$username;
	//限速
	$now=time();
	$fails=isset($_SESSION['login_fails'])?$_SESSION['login_fails']:0;
	$lock_until=isset($_SESSION['login_lock_until'])?$_SESSION['login_lock_until']:0;
	if ($fails>=5 && $now<$lock_until) {
		return 'bad';
	}
	if ($fails>=5) {
		$_SESSION['login_fails']=0;
	}
	$row=$GLOBALS['db']->get('admin',['id','username','password_hash','otp_enabled'],['username'=>$username]);
	if (empty($row) || !password_verify((string)$password,$row['password_hash'])) {
		$_SESSION['login_fails']=$fails+1;
		if ($_SESSION['login_fails']>=5) {
			$_SESSION['login_lock_until']=$now+600;
		}
		return 'bad';
	}
	//账号密码通过,重置限速
	$_SESSION['login_fails']=0;
	unset($_SESSION['login_lock_until']);
	if (!empty($row['otp_enabled'])) {
		//需要第二步验证,暂不建立完整登录态
		session_regenerate_id(true);
		$_SESSION['otp_pending']=['id'=>(int)$row['id'],'user'=>$row['username'],'time'=>time()];
		return 'otp';
	}
	admin_finish_login($row);
	return 'ok';
}

/**
 * 登录第二步:校验验证器验证码,完成登录
 * @param  string $code 6位TOTP验证码
 * @return bool
 */
function admin_verify_otp($code){
	if (empty($_SESSION['otp_pending'])) {
		return false;
	}
	$pending=$_SESSION['otp_pending'];
	if (time()-$pending['time']>300) {
		//两步验证超时,回到第一步
		unset($_SESSION['otp_pending']);
		return false;
	}
	require_once SYSTEM_ROOT.'totp.php';
	$secret=admin_get_otp_secret_by_id($pending['id']);
	if ($secret==='' || !totp_verify($secret,$code)) {
		return false;
	}
	$row=$GLOBALS['db']->get('admin',['id','username'],['id'=>$pending['id']]);
	if (empty($row)) {
		unset($_SESSION['otp_pending']);
		return false;
	}
	unset($_SESSION['otp_pending']);
	admin_finish_login($row);
	return true;
}

/**
 * 管理员操作审计(登录后才有身份;写入失败不影响主流程)
 * @param string $action 动作
 * @param string $target 对象
 */
function admin_log($action, $target=''){
	if (empty($_SESSION['admin_id'])) {
		return;
	}
	try {
		$GLOBALS['db']->insert('admin_log',[
			'username'=>isset($_SESSION['admin_user'])?$_SESSION['admin_user']:'',
			'action'=>(string)$action,
			'target'=>mb_substr((string)$target,0,200),
			'ip'=>get_client_ip(),
			'date'=>date('Y-m-d H:i:s'),
		]);
	} catch (Exception $e) {
		//审计失败不阻塞业务
	}
}

/**
 * 建立完整登录态(账号密码+验证码都通过后调用)
 * @param array $row admin表行
 */
function admin_finish_login($row){
	session_regenerate_id(true);
	$_SESSION['admin_id']=(int)$row['id'];
	$_SESSION['admin_user']=$row['username'];
	$GLOBALS['db']->update('admin',['last_login'=>date('Y-m-d H:i:s')],['id'=>$row['id']]);
	admin_log('login', $row['username']);
}

/**
 * 当前登录管理员已绑定的TOTP密钥(解密)
 * @return string 未绑定时返回空串
 */
function admin_get_otp_secret(){
	if (empty($_SESSION['admin_id'])) {
		return '';
	}
	return admin_get_otp_secret_by_id($_SESSION['admin_id']);
}

/**
 * 按id取TOTP密钥(解密)
 * @param  int $id
 * @return string
 */
function admin_get_otp_secret_by_id($id){
	$row=$GLOBALS['db']->get('admin',['otp_secret','otp_enabled'],['id'=>(int)$id]);
	if (empty($row) || empty($row['otp_enabled']) || $row['otp_secret']==='') {
		return '';
	}
	$secret=encdec_openssl($row['otp_secret'],'de');
	return is_string($secret)?$secret:'';
}

/**
 * 当前管理员是否开启两步验证
 * @return int
 */
function admin_get_otp_enabled(){
	if (empty($_SESSION['admin_id'])) {
		return 0;
	}
	$row=$GLOBALS['db']->get('admin',['otp_enabled'],['id'=>$_SESSION['admin_id']]);
	return empty($row)?0:(int)$row['otp_enabled'];
}

/**
 * 修改管理员密码
 * @param  string $oldpass 原密码
 * @param  string $newpass 新密码
 * @return bool
 */
function admin_change_password($oldpass,$newpass){
	if (empty($_SESSION['admin_id'])) {
		return false;
	}
	if (!is_string($newpass) || strlen($newpass)<6 || strlen($newpass)>64) {
		return false;
	}
	$row=$GLOBALS['db']->get('admin',['password_hash'],['id'=>$_SESSION['admin_id']]);
	if (empty($row) || !password_verify((string)$oldpass,$row['password_hash'])) {
		return false;
	}
	$GLOBALS['db']->update('admin',['password_hash'=>password_hash($newpass,PASSWORD_DEFAULT)],['id'=>$_SESSION['admin_id']]);
	return true;
}

/**
 * 注销
 */
function admin_logout(){
	$_SESSION=[];
	if (ini_get('session.use_cookies')) {
		$p=session_get_cookie_params();
		setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
	}
	session_destroy();
}
