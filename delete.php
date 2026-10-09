<?php
/**
 * 匿名删除:上传时返回的 delete.php?token=xxx 链接
 * GET 展示确认页, POST token 执行删除(软删记录+删除文件)
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

$token=isset($_REQUEST['token'])?trim((string)$_REQUEST['token']):'';
$e_title=htmlspecialchars($config['web']['title'],ENT_QUOTES,'UTF-8');

function del_page($title, $body){
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html lang="zh-CN" data-default-skin="light"><head><meta charset="utf-8">'
		.'<meta name="viewport" content="width=device-width, initial-scale=1">'
		.'<title>'.$title.'</title><link href="view/site.css" rel="stylesheet"><script src="view/theme.js"></script></head>'
		.'<body><div class="login-wrap"><div class="login-box" style="text-align:center">'.$body.'</div></div></body></html>';
	exit();
}

if (!preg_match('/^[0-9a-f]{32}$/',$token)) {
	del_page('无效链接', '<h2>删除链接无效</h2><p class="text-muted">链接不完整或已损坏</p>');
}

$row=$db->get('imginfo',['id','path','name','see'],['delete_token'=>$token]);
if (empty($row)) {
	del_page('无效链接', '<h2>删除链接无效</h2><p class="text-muted">该链接不存在,图片可能已被处理</p>');
}
if (!$row['see']) {
	del_page('已删除', '<h2>图片已删除</h2><p class="text-muted">这张图片此前已被删除</p>');
}

if (isset($_POST['do']) && $_POST['do']==='yes') {
	if (!isset($_POST['_csrf']) || !csrf_verify($_POST['_csrf'])) {
		del_page('请重试', '<h2>页面已过期</h2><p class="text-muted"><a href="delete.php?token='.htmlspecialchars($token,ENT_QUOTES,'UTF-8').'">点此重试</a></p>');
	}
	$db->update('imginfo',['see'=>0],['id'=>$row['id']]);
	$real_path=url_path($row['path']);
	if (strpos($real_path,'i/')===0 && is_file(ROOT.$real_path)) {
		@unlink(ROOT.$real_path);
	}
	del_page('删除成功', '<h2>删除成功</h2><p class="text-muted">图片已从本站移除,感谢您的反馈</p><p><a class="btn btn-primary" href="/">返回首页</a></p>');
}

//确认页
$e_path=htmlspecialchars(url_path($row['path']),ENT_QUOTES,'UTF-8');
csrf_token();
$e_csrf=htmlspecialchars($_SESSION['csrf_token'],ENT_QUOTES,'UTF-8');
del_page('确认删除图片', 
	'<h2>确认删除图片?</h2>'
	.'<p style="margin:14px 0"><img src="'.htmlspecialchars($config['web']['cdn'],ENT_QUOTES,'UTF-8').$e_path.'" style="max-width:100%;max-height:220px;border-radius:10px;border:1px solid var(--border)" alt=""></p>'
	.'<p class="text-muted" style="font-size:13px">'.($row['name']?$e_path:'').'</p>'
	.'<form method="post" action="delete.php">'
	.'<input type="hidden" name="token" value="'.htmlspecialchars($token,ENT_QUOTES,'UTF-8').'">'
	.'<input type="hidden" name="_csrf" value="'.$e_csrf.'">'
	.'<input type="hidden" name="do" value="yes">'
	.'<button type="submit" class="btn btn-danger btn-block">确认删除</button></form>'
	.'<p style="margin-top:12px"><a href="/">取消并返回</a></p>');
