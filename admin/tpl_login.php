<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} - 后台登录</title>
    <link href="https://o.qcloud.com/static_api/v3/assets/bootstrap-3.3.4/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://o.qcloud.com/static_api/v3/assets/fontawesome/css/font-awesome.css" rel="stylesheet">
    <style>
    {literal}
        body{background-color:#f5f5f5}
        .login-box{max-width:360px;margin:10% auto 0;background:#fff;border:1px solid #ddd;border-radius:4px;padding:30px 30px 20px}
        .login-box h2{text-align:center;margin-top:0;margin-bottom:25px}
    {/literal}
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fa fa-leaf"></i> {$title} - 后台登录</h2>
        {if $login_error}
        <div class="alert alert-danger">{$login_error|escape}</div>
        {/if}
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
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-sign-in"></i> 登录</button>
        </form>
    </div>
</body>
</html>
