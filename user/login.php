<?php
/**
 * 用户登录/注册
 */
require '../system/config.php';
require_once SYSTEM_ROOT.'auth.php';
require_once SYSTEM_ROOT.'user_auth.php';

//已登录直接进用户中心
if (!empty($_SESSION['user_id'])) {
	header('Location: center.php');
	exit();
}

$mode=isset($_GET['mode']) && $_GET['mode']==='register' ? 'register' : 'login';
$error='';

if (!empty($_POST) && isset($_POST['mode'])) {
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$error='页面已过期,请重新提交';
	}elseif ($_POST['mode']==='register') {
		$mode='register';
		$error=user_register($_POST['username'], $_POST['password'], $_POST['confirm']);
		if ($error==='') {
			//注册成功直接登录
			user_login($_POST['username'], $_POST['password']);
			header('Location: center.php');
			exit();
		}
	}else{
		$error=user_login($_POST['username'], $_POST['password']);
		if ($error==='') {
			header('Location: center.php');
			exit();
		}
	}
}

$e_title=htmlspecialchars($config['web']['title'],ENT_QUOTES,'UTF-8');
$e_skin=htmlspecialchars($config['web']['default_skin']??'light',ENT_QUOTES,'UTF-8');
$e_accent=htmlspecialchars($config['web']['accent']??'',ENT_QUOTES,'UTF-8');
$e_error=$error?htmlspecialchars($error,ENT_QUOTES,'UTF-8'):'';
$e_csrf=htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');
?><!DOCTYPE html>
<html lang="zh-CN" data-default-skin="<?php echo $e_skin; ?>" data-default-accent="<?php echo $e_accent; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $e_title; ?> · <?php echo $mode==='register'?'注册':'登录'; ?></title>
	<link href="../view/site.css" rel="stylesheet">
	<script src="../view/theme.js"></script>
</head>
<body>
<div class="login-wrap">
	<div class="login-box">
		<h2><span class="logo-dot"></span><?php echo $e_title; ?></h2>
		<div class="tabs" style="margin-bottom:18px">
			<a href="login.php" style="text-decoration:none"><button type="button" class="<?php echo $mode==='login'?'active':''; ?>">登录</button></a>
			<a href="login.php?mode=register" style="text-decoration:none"><button type="button" class="<?php echo $mode==='register'?'active':''; ?>">注册</button></a>
		</div>
		<?php if ($e_error): ?><div class="alert alert-danger"><?php echo $e_error; ?></div><?php endif; ?>
		<form method="post" action="login.php<?php echo $mode==='register'?'?mode=register':''; ?>">
			<input type="hidden" name="_csrf" value="<?php echo $e_csrf; ?>">
			<input type="hidden" name="mode" value="<?php echo $mode; ?>">
			<div class="form-group">
				<label for="username">用户名</label>
				<input type="text" class="form-control" id="username" name="username" placeholder="3-32位字母/数字/下划线" required autofocus>
			</div>
			<div class="form-group">
				<label for="password">密码</label>
				<input type="password" class="form-control" id="password" name="password" placeholder="至少6位" required>
			</div>
			<?php if ($mode==='register'): ?>
			<div class="form-group">
				<label for="confirm">确认密码</label>
				<input type="password" class="form-control" id="confirm" name="confirm" placeholder="再输入一次" required>
			</div>
			<?php endif; ?>
			<button type="submit" class="btn btn-primary btn-block"><?php echo $mode==='register'?'注册并登录':'登录'; ?></button>
		</form>
		<p class="login-foot"><a href="../">← 返回首页</a></p>
	</div>
</div>
</body>
</html>
