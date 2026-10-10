<?php
/**
 * 健康检查端点(监控探活用)
 * 公开访问只返回总体状态;管理员会话下返回详细信息
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'auth.php';
require_once SYSTEM_ROOT.'archive.php';

header('Content-Type: application/json; charset=utf-8');

$health=['status'=>'ok','time'=>date('Y-m-d H:i:s')];

//数据库可读性
try {
	$count=$db->count('imginfo');
	$health['db']='ok';
} catch (Exception $e) {
	$health['status']='error';
	$health['db']='unreadable';
	$count=0;
}

//数据库文件可写
$dbf=db_file_path();
$health['db_writable']=is_file($dbf) && is_writable($dbf) ? true : false;
if (!$health['db_writable']) {
	$health['status']='error';
}

//磁盘剩余空间
$free=@disk_free_space(ROOT);
$health['disk_free']=$free!==false ? format_size((int)$free) : 'unknown';
if ($free!==false && $free < 104857600) { //低于100MB告警
	$health['status']='warning';
	$health['disk_warning']='磁盘剩余空间不足100MB';
}

//管理员可见详情
if (!empty($_SESSION['admin_id'])) {
	$usage=img_disk_usage();
	$meta=archive_meta();
	$trash=trash_stat();
	$health['detail']=[
		'images_online'=>$count,
		'images_disk'=>format_size($usage['size']).' / '.$usage['files'].' files',
		'trash'=>format_size($trash['size']).' / '.$trash['files'].' files',
		'archive_last_run'=>$meta['last_run']?:'never',
		'php_version'=>PHP_VERSION,
		'memory_usage'=>format_size(memory_get_usage(true)),
	];
}

if ($health['status']==='error') {
	header('HTTP/1.0 503 Service Unavailable');
}
exit(json_encode($health, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));