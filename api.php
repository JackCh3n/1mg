<?php
/**
 * 开放 API 文档(公开页面)
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

$e_title=htmlspecialchars($config['web']['title'],ENT_QUOTES,'UTF-8');
$e_server=htmlspecialchars(($config['web']['server']?:''),ENT_QUOTES,'UTF-8');
$need_token = isset($config['web']['api_token']) && trim((string)$config['web']['api_token'])!=='';
?>
<!DOCTYPE html>
<html lang="zh-CN" data-default-skin="<?php echo htmlspecialchars($config['web']['default_skin']??'light',ENT_QUOTES,'UTF-8'); ?>" data-default-accent="<?php echo htmlspecialchars($config['web']['accent']??'',ENT_QUOTES,'UTF-8'); ?>">
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $e_title; ?> · API 文档</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="view/site.css" rel="stylesheet">
    <script src="view/theme.js"></script>
    <style>
    .api-doc h2{margin-top:34px}
    .api-doc table{width:100%;border-collapse:collapse;font-size:14px}
    .api-doc th,.api-doc td{border:1px solid var(--border);padding:9px 13px;text-align:left}
    .api-doc code{background:var(--panel-2);border:1px solid var(--border);border-radius:6px;padding:2px 7px;font-size:13px}
    .api-doc pre{background:var(--panel-2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;overflow:auto;font-size:13px;line-height:1.7}
    </style>
</head>
<body>
    <nav class="site-nav">
        <div class="nav-inner">
            <a class="brand" href="/"><span class="logo-dot"></span><?php echo $e_title; ?></a>
            <div class="nav-links"><a href="/">返回上传</a></div>
            <div class="nav-right">
                <button type="button" class="theme-toggle" data-mg-theme-toggle title="切换明暗皮肤">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2 12h2.5M19.5 12H22M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container api-doc prose">
        <div class="hero" style="padding-bottom:0">
            <h1>开放 <em>API</em></h1>
            <p>兼容 PicGo / uTools 等工具的"自定义 Web 上传",以下地址均可直接使用</p>
        </div>

        <div class="card" style="margin-top:26px">
            <h2 style="margin-top:0">1. 上传图片</h2>
            <p><code>POST <?php echo $e_server; ?>/upload.php</code></p>
            <table>
                <tr><th>参数</th><th>位置</th><th>说明</th></tr>
                <tr><td><code>file</code></td><td>multipart 表单字段(必填)</td><td>图片文件,≤5MB,支持 jpg/png/gif/bmp</td></tr>
                <tr><td><code>api_token</code></td><td>表单字段或请求头 <code>X-API-Token</code></td><td><?php echo $need_token ? '必填(本站已开启接口鉴权)' : '本站未开启鉴权,可不传'; ?></td></tr>
            </table>
            <p>成功响应:</p>
            <pre>{
  "code": "success",
  "data": {
    "url":    "<?php echo $e_server; ?>/i/2610/09/1535477902570.webp",
    "md5":    "文件md5",
    "delete": "<?php echo $e_server; ?>/delete.php?token=...",  //匿名删除链接
    "reused": false,      //true 表示命中秒传,未实际存储
    "size":   12345,
    "compress": 1         //1 表示经过了压缩/转码
  }
}</pre>
            <p>失败响应:<code>{"code":110,"error":"原因"}</code>(429 表示触发频率限制)</p>
        </div>

        <div class="card">
            <h2 style="margin-top:0">2. 秒传预检(可选)</h2>
            <p><code>GET <?php echo $e_server; ?>/check.php?md5=&lt;文件md5&gt;</code></p>
            <p>若服务器已存在相同文件,直接返回 <code>data.url</code>,无需上传。文件不存在返回 404。</p>
        </div>

        <div class="card">
            <h2 style="margin-top:0">3. PicGo 配置</h2>
            <p>插件市场安装「custom web-uploader」,按下面填写:</p>
            <pre>API 地址:  <?php echo $e_server; ?>/upload.php
POST 参数名: file
JSON 路径:  data.url
自定义请求头: <?php echo $need_token ? '{ "X-API-Token": "你的令牌" }' : '(留空,本站未开启鉴权)'; ?>
自定义Body: <?php echo $need_token ? '{ "api_token": "你的令牌" }' : '(留空)'; ?></pre>
            <p>令牌可向站长索取;站长可在后台「网站设置 → 接口令牌」生成或关闭鉴权。</p>
        </div>

        <div class="card">
            <h2 style="margin-top:0">4. 使用约定</h2>
            <ul>
                <li>仅允许上传图片,单张 ≤5MB;每 IP 每小时有上传次数限制</li>
                <li>相同内容的文件自动去重(秒传),不会重复占用空间</li>
                <li>严禁上传违法图片,站方可随时通过删除链接或后台移除</li>
            </ul>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container">© <?php echo date('Y'); ?> <?php echo $e_title; ?> · Powered by 1mg</div>
    </footer>
</body>
</html>
