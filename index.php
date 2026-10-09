<?php
/**
 * 前台首页(上传/今日/关于/联系/条款)
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

$title=$config['web']['title'];
$skin=isset($config['web']['default_skin'])?$config['web']['default_skin']:'light';
$accent=isset($config['web']['accent'])?$config['web']['accent']:'';

//模板里需要的转义输出
$e_title=htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$e_skin=htmlspecialchars($skin, ENT_QUOTES, 'UTF-8');
$e_accent=htmlspecialchars($accent, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN" data-default-skin="<?php echo $e_skin; ?>" data-default-accent="<?php echo $e_accent; ?>">
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $e_title; ?> · 简单免费的图床</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $e_title; ?> - 简单·免费的图床服务,支持拖拽上传、自动压缩、秒传去重">
    <link href="view/site.css" rel="stylesheet">
    <script src="view/theme.js"></script>
</head>
<body>
    <nav class="site-nav">
        <div class="nav-inner">
            <a class="brand" href="/"><span class="logo-dot"></span><?php echo $e_title; ?></a>
            <div class="nav-links" id="nav-links">
                <a href="/" class="active" data-nav="home">首页</a>
                <a href="#today" id="today" data-nav="today">今日</a>
                <a href="#about" id="about" data-nav="about">关于</a>
                <a href="#contact" id="contact" data-nav="contact">联系</a>
                <a href="#tos" id="tos" data-nav="tos">条款</a>
            </div>
            <div class="nav-right">
                <a href="api.php">API</a>
                <a href="https://github.com/lenyuadmin/1mg" target="_blank" rel="noopener">GitHub</a>
<?php if (!empty($_SESSION['user_id'])): ?>
                <a href="user/center.php"><b><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></b></a>
                <a href="user/logout.php">退出</a>
<?php else: ?>
                <a href="user/login.php">登录/注册</a>
<?php endif; ?>
                <button type="button" class="theme-toggle" data-mg-theme-toggle title="切换明暗皮肤">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2 12h2.5M19.5 12H22M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- 上传区 -->
        <section id="sec-upload">
            <div class="hero">
                <h1>简单 · <em>免费</em> · 好用的图床</h1>
                <p>拖拽上传 · 自动压缩 · 相同文件秒传 · 单张 5MB,一次最多 10 张</p>
            </div>
            <div class="card upload-card">
                <input id="file" type="file" multiple class="file"
                    data-overwrite-initial="false" data-min-file-count="1" data-max-file-count="10" name="file" accept="image/*"
                    data-api-token="<?php echo htmlspecialchars($config['web']['api_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="card" id="showurl" style="display:none">
                <div class="tabs" id="result-tabs">
                    <button type="button" class="active" data-tab="urlcode">URL</button>
                    <button type="button" data-tab="htmlcode">HTML</button>
                    <button type="button" data-tab="bbcode">BBCode</button>
                    <button type="button" data-tab="markdown">Markdown</button>
                    <button type="button" data-tab="markdownlinks">Markdown+链接</button>
                    <button type="button" data-tab="deletecode">删除链接</button>
                </div>
                <div class="tab-pane active" id="pane-urlcode"><pre><code id="urlcode"></code></pre></div>
                <div class="tab-pane" id="pane-htmlcode"><pre><code id="htmlcode"></code></pre></div>
                <div class="tab-pane" id="pane-bbcode"><pre><code id="bbcode"></code></pre></div>
                <div class="tab-pane" id="pane-markdown"><pre><code id="markdown"></code></pre></div>
                <div class="tab-pane" id="pane-markdownlinks"><pre><code id="markdownlinks"></code></pre></div>
                <div class="tab-pane" id="pane-deletecode"><pre><code id="deletecode"></code></pre></div>
            </div>
        </section>

        <!-- 其他内容区(今日/关于/联系/条款) -->
        <section id="qita" class="prose" style="display:none"></section>
    </div>

    <footer class="site-footer">
        <div class="container">
            © <?php echo date('Y'); ?> <?php echo $e_title; ?> · Powered by 1mg
        </div>
    </footer>

    <script src="https://cdnjs.loli.net/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="view/bootstrap-fileinput-4.4.9/js/fileinput.min.js"></script>
    <script src="view/bootstrap-fileinput-4.4.9/js/locales/zh.js"></script>
    <script src="https://cdnjs.loli.net/ajax/libs/spark-md5/3.0.2/spark-md5.min.js"></script>
    <script src="/view/index.js"></script>
</body>
</html>
