<?php
/**
 * 管理员登录
 * 默认账号 admin / admin123456 (首次登录后请在"网站设置"里修改密码)
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';

//已登录直接进后台
if (!empty($_SESSION['admin_id'])) {
	header('Location: index.php');
	exit();
}

$login_error='';
if (!empty($_POST)) {
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$login_error='页面已过期,请重新提交';
	}else{
		if (admin_login(isset($_POST['username'])?$_POST['username']:'',isset($_POST['password'])?$_POST['password']:'')) {
			header('Location: index.php');
			exit();
		}
		$lock=isset($_SESSION['login_lock_until'])?$_SESSION['login_lock_until']:0;
		if (isset($_SESSION['login_fails']) && $_SESSION['login_fails']>=5 && time()<$lock) {
			$login_error='失败次数过多,请10分钟后再试';
		}else{
			$login_error='用户名或密码错误';
		}
	}
}
$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('login_error',$login_error);
$smarty->display('tpl_login.php');
