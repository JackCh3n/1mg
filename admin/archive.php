<?php
/**
 * 归档操作:下载备份 / cron触发归档
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
require_once SYSTEM_ROOT.'archive.php';

$type=isset($_GET['type'])?$_GET['type']:'';

//下载归档备份(需登录)
if ($type==='download') {
	admin_require_login();
	$file=isset($_GET['file'])?$_GET['file']:'';
	list($dir,)=archive_paths();
	$path=$dir.'/'.$file.'.csv.gz';
	if (!preg_match('/^\d{8}$/',$file) || !is_file($path)) {
		header('HTTP/1.0 404 Not Found');
		exit('404');
	}
	header('Content-Type: application/gzip');
	header('Content-Disposition: attachment; filename="'.$file.'.csv.gz"');
	header('Content-Length: '.filesize($path));
	header('X-Content-Type-Options: nosniff');
	readfile($path);
	exit();
}

//cron触发归档: ?type=cron&who=<img_level_pass>(与鉴黄口令一致)
if ($type==='cron') {
	$allowed=isset($_GET['who']) && hash_equals((string)$config['web']['img_level_pass'],(string)$_GET['who']);
	header('Content-Type: application/json; charset=utf-8');
	if (!$allowed) {
		exit(json_encode(['code'=>404,'error'=>'404']));
	}
	$result=archive_run((int)$config['web']['retention_online'],(int)$config['web']['retention_archive']);
	exit(json_encode(['code'=>'success','data'=>$result], JSON_UNESCAPED_UNICODE));
}

//其他访问回仪表盘
header('Location: index.php');
