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

//从归档备份导入记录(需登录):缺失的记录重新入库
if ($type==='import') {
	admin_require_login();
	$file=isset($_GET['file'])?$_GET['file']:'';
	if (!isset($_GET['token']) || !hash_equals(csrf_token(),(string)$_GET['token'])) {
		header('Location: index.php');
		exit();
	}
	$csv=archive_read($file);
	if ($csv===null) {
		header('Location: index.php');
		exit();
	}
	$imported=0;
	$skipped=0;
	$lines=preg_split('/\r?\n/', trim($csv));
	foreach ($lines as $line) {
		if ($line==='') { continue; }
		$cols=str_getcsv($line);
		if (count($cols)<12) { continue; }
		list($oid,$opath,$oip,$oua,$odate,$odir,$ocompress,$olevel,$osee,$omd5,$oname,$osize)=$cols;
		if ($omd5!=='' && $db->count('imginfo',['md5'=>$omd5])>0) { $skipped++; continue; }
		try {
			$db->insert('imginfo',['path'=>$opath,'ip'=>$oip,'ua'=>$oua,'date'=>$odate,'dir'=>$odir,'compress'=>(int)$ocompress,'level'=>(int)$olevel,'see'=>(int)$osee,'md5'=>$omd5,'name'=>$oname,'size'=>$osize]);
			$imported++;
		} catch (Exception $e) { $skipped++; }
	}
	admin_log('archive_import',$file.': +'.$imported.' skip='.$skipped);
	header('Location: index.php?imported='.$imported.'&skipped='.$skipped);
	exit();
}

//下载数据库备份(需登录):sqlite文件gzip压缩
if ($type==='backup') {
	admin_require_login();
	$dbf=db_file_path();
	if (!is_file($dbf)) {
		header('HTTP/1.0 404 Not Found');
		exit('404');
	}
	admin_log('db_backup','');
	$name='1mg-'.date('Ymd-His').'.sqlite.gz';
	header('Content-Type: application/gzip');
	header('Content-Disposition: attachment; filename="'.$name.'"');
	header('X-Content-Type-Options: nosniff');
	//流式gzip压缩输出(zlib.deflate 输出 gzip 格式)
	$fp=fopen($dbf,'rb');
	if ($fp) {
		stream_filter_append($fp,'zlib.deflate',STREAM_FILTER_READ,['level'=>6,'window'=>31]);
		fpassthru($fp);
		fclose($fp);
	}
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
