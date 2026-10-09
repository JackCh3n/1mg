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
 * 登录校验,带简单的会话级限速:连续失败5次锁定10分钟
 * @param  string $username
 * @param  string $password
 * @return bool
 */
function admin_login($username,$password){
	$username=(string)$username;
	//限速
	$now=time();
	$fails=isset($_SESSION['login_fails'])?$_SESSION['login_fails']:0;
	$lock_until=isset($_SESSION['login_lock_until'])?$_SESSION['login_lock_until']:0;
	if ($fails>=5 && $now<$lock_until) {
		return false;
	}
	if ($fails>=5) {
		$_SESSION['login_fails']=0;
	}
	$row=$GLOBALS['db']->get('admin',['id','username','password_hash'],['username'=>$username]);
	if (empty($row) || !password_verify((string)$password,$row['password_hash'])) {
		$_SESSION['login_fails']=$fails+1;
		if ($_SESSION['login_fails']>=5) {
			$_SESSION['login_lock_until']=$now+600;
		}
		return false;
	}
	//登录成功,重置限速并更换会话ID防会话固定
	$_SESSION['login_fails']=0;
	unset($_SESSION['login_lock_until']);
	session_regenerate_id(true);
	$_SESSION['admin_id']=(int)$row['id'];
	$_SESSION['admin_user']=$row['username'];
	$GLOBALS['db']->update('admin',['last_login'=>date('Y-m-d H:i:s')],['id'=>$row['id']]);
	return true;
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
