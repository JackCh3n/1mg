<!DOCTYPE html>
<html lang="zh-CN" data-default-skin="{$default_skin}" data-default-accent="{$default_accent}">
<head>
	<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title|escape} - 后台登录</title>
    <link href="../view/site.css" rel="stylesheet">
    <script src="../view/theme.js"></script>
</head>
<body>
    <div class="login-box">
        <h2><span class="logo-dot"></span> {$title|escape} · 后台登录</h2>
        {if $login_error}
        <div class="alert alert-danger">{$login_error|escape}</div>
        {/if}
        {if $step=='otp'}
        <p class="login-tip">已验证账号 <b>{$otp_user|escape}</b>,请输入验证器上的 6 位动态码</p>
        <form method="post" action="login.php">
            <input type="hidden" name="_csrf" value="{$csrf}">
            <div class="form-group">
                <input type="text" class="form-control otp-input" name="code" placeholder="6 位验证码" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">验证并登录</button>
        </form>
        {else}
        <form method="post" action="login.php">
            <input type="hidden" name="_csrf" value="{$csrf}">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="用户名" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="密码" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">下一步</button>
        </form>
        {/if}
    </div>
</body>
</html>
