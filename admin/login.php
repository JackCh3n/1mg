<?php
/**
 * 管理员登录(账号+密码+两步验证)
 * 默认账号 admin / admin123456 (首次登录后请在"网站设置"里修改密码并绑定验证器)
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';

//已登录直接进后台
if (!empty($_SESSION['admin_id'])) {
	header('Location: index.php');
	exit();
}

$login_error='';
$step='password';
//会话里有待两步验证的状态时(如刷新页面),直接进入第二步
if (!empty($_SESSION['otp_pending'])) {
	$step='otp';
}

if (!empty($_POST)) {
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$login_error='页面已过期,请重新提交';
	}elseif (isset($_POST['code'])) {
		//第二步:两步验证码
		$step='otp';
		if (admin_verify_otp((string)$_POST['code'])) {
			header('Location: index.php');
			exit();
		}
		if (empty($_SESSION['otp_pending'])) {
			$step='password';
			$login_error='验证已超时,请重新登录';
		}else{
			$login_error='验证码不正确,请重试';
		}
	}else{
		//第一步:账号密码
		$result=admin_login(
			isset($_POST['username'])?$_POST['username']:'',
			isset($_POST['password'])?$_POST['password']:''
		);
		if ($result==='ok') {
			header('Location: index.php');
			exit();
		}elseif ($result==='otp') {
			$step='otp';
		}else{
			$lock=isset($_SESSION['login_lock_until'])?$_SESSION['login_lock_until']:0;
			if (isset($_SESSION['login_fails']) && $_SESSION['login_fails']>=5 && time()<$lock) {
				$login_error='失败次数过多,请10分钟后再试';
			}else{
				$login_error='用户名或密码错误';
			}
		}
	}
}

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('default_skin',$config['web']['default_skin']);
$smarty->assign('default_accent',$config['web']['accent']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('login_error',$login_error);
$smarty->assign('step',$step);
$smarty->assign('otp_user',isset($_SESSION['otp_pending']['user'])?$_SESSION['otp_pending']['user']:'');
$smarty->display('tpl_login.php');
