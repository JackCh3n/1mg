<?php
/**
 * 图片详情/分享页(公开):展示图片信息与各种引用代码、二维码
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

$id=isset($_GET['id'])?(int)$_GET['id']:0;
$row=$id?$db->get('imginfo',['id','path','name','size','date','user_id','md5'],['id'=>$id,'see'=>1]):null;

$e_title=htmlspecialchars($config['web']['title'],ENT_QUOTES,'UTF-8');
$e_skin=htmlspecialchars($config['web']['default_skin']??'light',ENT_QUOTES,'UTF-8');
$e_accent=htmlspecialchars($config['web']['accent']??'',ENT_QUOTES,'UTF-8');

if (empty($row)) {
	header('HTTP/1.0 404 Not Found');
	$body='<div class="card" style="text-align:center"><h2>图片不存在或已被删除</h2><p class="text-muted">该图片可能已被删除,或链接不完整。</p><p style="margin-top:16px"><a class="btn btn-primary" href="/">返回首页</a></p></div>';
} else {
	$rel=url_path($row['path']);
	$url=$config['web']['cdn'].$rel;
	$file=ROOT.$rel;
	$dim='';
	if (is_file($file)) {
		$info=@getimagesize($file);
		if ($info) {
			$dim=$info[0].' × '.$info[1];
		}
	}
	$uploader='匿名';
	if ((int)$row['user_id']>0) {
		$u=$db->get('users','username',['id'=>(int)$row['user_id']]);
		if ($u) {
			$uploader=$u;
		}
	}
	$name=htmlspecialchars($row['name'],ENT_QUOTES,'UTF-8');
	$e_url=htmlspecialchars($url,ENT_QUOTES,'UTF-8');
	$html_code='&lt;img src="'.$e_url.'" alt="'.$name.'" /&gt;';
	$md='!['.$name.']('.$url.')';
	$bb='[img]'.$url.'[/img]';
	$body='<div class="card" style="padding:14px">'
		.'<img src="'.$e_url.'" alt="'.$name.'" style="max-width:100%;max-height:60vh;display:block;margin:0 auto;border-radius:10px">'
		.'</div>'
		.'<div class="card"><h2 style="margin:0 0 14px">图片信息</h2>'
		.'<table class="info-table">'
		.'<tr><th>文件名</th><td>'.$name.'</td></tr>'
		.'<tr><th>尺寸</th><td>'.($dim?$dim:'未知').'</td></tr>'
		.'<tr><th>大小</th><td>'.format_size((int)$row['size']).'</td></tr>'
		.'<tr><th>上传时间</th><td>'.htmlspecialchars($row['date'],ENT_QUOTES,'UTF-8').'</td></tr>'
		.'<tr><th>上传者</th><td>'.htmlspecialchars($uploader,ENT_QUOTES,'UTF-8').'</td></tr>'
		.'<tr><th>MD5</th><td style="font-family:var(--mono);font-size:12.5px">'.htmlspecialchars($row['md5'],ENT_QUOTES,'UTF-8').'</td></tr>'
		.'</table></div>'
		.'<div class="card"><h2 style="margin:0 0 14px">引用代码</h2>'
		.'<div class="tabs" id="share-tabs">'
		.'<button type="button" class="active" data-tab="s-url">URL</button>'
		.'<button type="button" data-tab="s-html">HTML</button>'
		.'<button type="button" data-tab="s-md">Markdown</button>'
		.'<button type="button" data-tab="s-bb">BBCode</button>'
		.'</div>'
		.'<div class="tab-pane active" id="pane-s-url"><pre><code>'.$e_url.'</code></pre></div>'
		.'<div class="tab-pane" id="pane-s-html"><pre><code>'.$html_code.'</code></pre></div>'
		.'<div class="tab-pane" id="pane-s-md"><pre><code>'.htmlspecialchars($md,ENT_QUOTES,'UTF-8').'</code></pre></div>'
		.'<div class="tab-pane" id="pane-s-bb"><pre><code>'.htmlspecialchars($bb,ENT_QUOTES,'UTF-8').'</code></pre></div>'
		.'<p style="margin-top:14px"><button type="button" class="btn btn-primary" id="copy-share">复制当前代码</button>'
		.'<a class="btn" href="'.$e_url.'" target="_blank">查看原图</a>'
		.'<a class="btn" href="/">上传新图片</a></p>'
		.'</div>'
		.'<div class="card"><h2 style="margin:0 0 14px">扫码分享</h2>'
		.'<div id="qrcode" style="background:#fff;padding:10px;border-radius:10px;width:max-content"></div>'
		.'<p class="text-muted" style="font-size:12.5px">用手机扫码即可打开/保存这张图片</p></div>';
}
?><!DOCTYPE html>
<html lang="zh-CN" data-default-skin="<?php echo $e_skin; ?>" data-default-accent="<?php echo $e_accent; ?>">
<head>
	<meta charset="UTF-8"/>
	<title><?php echo $e_title; ?> · 图片详情</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="view/site.css" rel="stylesheet">
	<script src="view/theme.js"></script>
</head>
<body>
<nav class="site-nav">
	<div class="nav-inner">
		<a class="brand" href="/"><span class="logo-dot"></span><?php echo $e_title; ?></a>
		<div class="nav-links"><a href="/">上传图片</a></div>
		<div class="nav-right">
			<button type="button" class="theme-toggle" data-mg-theme-toggle title="切换明暗皮肤">
				<svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				<svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2 12h2.5M19.5 12H22M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
			</button>
		</div>
	</div>
</nav>
<div class="container"><?php echo $body; ?></div>
<footer class="site-footer"><div class="container">© <?php echo (int)($config['web']['since_year'] ?? 2018); ?><?php if ((int)date('Y') > (int)($config['web']['since_year'] ?? 2018)) echo '-'.date('Y'); ?> <?php echo $e_title; ?> · Powered by 1mg</div></footer>
<?php if (!empty($row)): ?>
<script src="view/vendor/jquery.min.js"></script>
<script src="view/vendor/qrcode.min.js"></script>
<script>
(function(){
    var tabs=document.getElementById('share-tabs');
    if (tabs) {
        tabs.addEventListener('click', function(e){
            var b=e.target.closest('button'); if(!b) return;
            tabs.querySelectorAll('button').forEach(function(x){x.classList.remove('active');});
            b.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(function(p){p.classList.remove('active');});
            var pane=document.getElementById('pane-'+b.getAttribute('data-tab'));
            if(pane) pane.classList.add('active');
        });
    }
    var cp=document.getElementById('copy-share');
    if (cp) {
        cp.addEventListener('click', function(){
            var pane=document.querySelector('.tab-pane.active code');
            var txt=pane?pane.textContent:'';
            var done=function(){ cp.textContent='已复制 ✓'; setTimeout(function(){cp.textContent='复制当前代码';},1500); };
            if (navigator.clipboard&&navigator.clipboard.writeText) { navigator.clipboard.writeText(txt).then(done,function(){prompt('复制:',txt);}); }
            else { prompt('复制:',txt); }
        });
    }
    var qr=document.getElementById('qrcode');
    if (qr && window.QRCode) { new QRCode(qr, <?php echo json_encode($url, JSON_UNESCAPED_SLASHES); ?>); }
})();
</script>
<?php endif; ?>
</body>
</html>
