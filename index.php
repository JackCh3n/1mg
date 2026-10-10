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
                <a href="#help" data-nav="help">帮助</a>
                <a href="#contact" id="contact" data-nav="contact">联系</a>
                <a href="#tos" id="tos" data-nav="tos">条款</a>
            </div>
            <div class="nav-right">
                <a href="api.php">API</a>
<?php if (!empty($config['web']['show_github']) && !empty($config['web']['github_url'])): ?>
                <a href="<?php echo htmlspecialchars($config['web']['github_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">GitHub</a>
<?php endif; ?>
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
                <p>拖拽上传 · 自动压缩 · 相同文件秒传 · 单张 <?php echo (int)($config['web']['max_size_mb'] ?? 5); ?>MB,一次最多 <?php echo (int)($config['web']['max_files'] ?? 10); ?> 张</p>
            </div>
            <div class="card upload-card">
                <input id="file" type="file" multiple class="mg-upload"
                    data-overwrite-initial="false" data-min-file-count="1" data-max-file-count="10" name="file" accept="image/*"
                    data-api-token="<?php echo htmlspecialchars(api_token_for_web(), ENT_QUOTES, 'UTF-8'); ?>"
                    data-max-size="<?php echo (int)($config['web']['max_size_mb'] ?? 5); ?>"
                    data-max-count="<?php echo (int)($config['web']['max_files'] ?? 10); ?>">
            </div>
<?php if (!empty($config['web']['ttl_enabled'])): ?>
            <div class="card" style="padding:14px 18px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="text-muted" style="font-size:13.5px">文件有效期:</span>
                <select class="form-control" id="ttl-days" style="max-width:200px">
                    <option value="0">永久保存</option>
                    <option value="1">1 天后自动删除</option>
                    <option value="7">7 天后自动删除</option>
                    <option value="30">30 天后自动删除</option>
                </select>
                <span class="text-muted" style="font-size:12.5px">到期后图片会移入回收站,可由管理员恢复</span>
            </div>
<?php endif; ?>
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
        <section id="qita" class="prose" style="display:none" data-github="<?php echo htmlspecialchars($config['web']['github_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></section>

        <!-- 常用帮助 -->
        <section id="help" class="prose">
            <h2 style="margin-top:8px">常用帮助</h2>
            <div class="card">
                <h3 style="margin-top:0">怎么上传图片?</h3>
                <ul style="margin:0">
                    <li><b>点击上传</b>:点击绿色「选择图片」按钮选择图片,再点「开始上传」开始上传</li>
                    <li><b>拖拽上传</b>:把图片直接拖到本页面的任意位置,松手即传</li>
                    <li><b>粘贴上传</b>:截图后在页面任意位置按 <code>Ctrl + V</code> 直接上传</li>
                    <li>上传完成后,下方会自动生成 <b>URL / HTML / BBCode / Markdown / 删除链接</b> 五种格式,点击对应标签切换复制</li>
                    <li>相同内容的图片会自动<b>秒传</b>(不重复占用空间,直接返回已有链接)</li>
                </ul>
            </div>
            <div class="card">
                <h3 style="margin-top:0">图片限制</h3>
                <ul style="margin:0">
                    <li>单张不超过 <b><?php echo (int)($config['web']['max_size_mb'] ?? 5); ?>MB</b>,一次最多 <b><?php echo (int)($config['web']['max_files'] ?? 10); ?> 张</b></li>
                    <li>支持格式:<b>jpg / png / gif / bmp / webp</b>(gif 动图保持原样,其余自动压缩并转存为更小的 WebP)</li>
                    <li>超大图片会自动等比缩放(最长边 2560px),手机竖拍照片会按 EXIF 自动转正</li>
                    <li>为防滥用,每 IP 每小时有上传次数限制(默认 60 次,超限会提示稍后再试)</li>
                </ul>
            </div>
            <div class="card">
                <h3 style="margin-top:0">图片保存多久?</h3>
                <ul style="margin:0">
                    <li><b>图片外链长期有效</b>:文件本身不会被自动删除,外链可以一直引用</li>
                    <li>上传<b>记录</b>在线保留 <?php echo (int)($config['web']['retention_online'] ?? 7); ?> 天(可在后台调整),超期后转入压缩归档备份,备份保留 <?php echo (int)($config['web']['retention_archive'] ?? 180); ?> 天</li>
                    <li>违规图片会被随时删除;上传时返回的「删除链接」可随时自主删除自己的图片</li>
                </ul>
            </div>
            <div class="card">
                <h3 style="margin-top:0">API 与第三方软件接入</h3>
                <ul style="margin:0">
                    <li>提供标准 HTTP 上传接口,详见 <a href="api.php">API 文档</a>(含参数说明与响应格式)</li>
                    <li>已支持/可接入的工具:<b>PicGo</b>(安装 custom web-uploader 插件)、<b>uTools</b>、以及任何支持"自定义 Web 上传"的软件</li>
                    <li>若站点开启了接口令牌,在工具中配置 <code>api_token</code> 或请求头 <code>X-API-Token</code> 即可</li>
                </ul>
            </div>
            <div class="card">
                <h3 style="margin-top:0">哪些内容不允许上传?</h3>
                <ul style="margin:0">
                    <li>含有色情、暴力、恐怖、血腥内容的图片</li>
                    <li>侵犯版权、隐私或未经授权的图片;含有违规信息/二维码的图片</li>
                    <li>其他违反中华人民共和国法律法规的图片</li>
                    <li>完整条款见 <a href="#tos" onclick="document.getElementById('tos').click();return false;">服务条款</a>;违规图片将被删除,严重者封禁来源 IP</li>
                </ul>
            </div>
        </section>
    </div>

    <footer class="site-footer">
        <div class="container">
            © <?php echo (int)($config['web']['since_year'] ?? 2018); ?><?php if ((int)date('Y') > (int)($config['web']['since_year'] ?? 2018)) echo '-'.date('Y'); ?> <?php echo $e_title; ?> · Powered by 1mg
        </div>
    </footer>

    <script src="view/vendor/jquery.min.js"></script>
    <script src="view/bootstrap-fileinput-4.4.9/js/fileinput.min.js"></script><script src="view/bootstrap-fileinput-4.4.9/js/plugins/piexif.min.js" type="text/javascript"></script>
    <script src="view/bootstrap-fileinput-4.4.9/js/locales/zh.js"></script>
    <script src="view/vendor/spark-md5.min.js"></script>
    <script src="/view/index.js"></script>
</body>
</html>
