<?php
/**
 *  今日上传榜
 */
require '../system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

$today_data=$db->select('imginfo',['id','path','name','size'],['date[~]'=>date('Y-m-d'),'see'=>1,'ORDER'=>['id'=>'DESC'],'LIMIT'=>20]);
if (empty($today_data)) {
	json_exit([
		'initialPreview'=>[],
		'initialPreviewConfig'=>[],
		'initialPreviewAsData'=>true,
		//uploadUrl相对于消费该json的页面(站点根目录的index.html)
		'uploadUrl'=>'upload.php',
		'overwriteInitial'=>false,
	]);
}
shuffle($today_data);
$today_data=array_slice($today_data,0,12);

$today_out=['url'=>[],'iPC'=>[]];
foreach ($today_data as $i=>$row) {
	$today_out['url'][$i]=$config['web']['cdn'].url_path($row['path']);
	$today_out['iPC'][$i]=[
		'caption'=>$row['name']?:url_path($row['path']),
		'size'=>(int)$row['size'],
		//用真实记录id,原来用的循环下标无法对应到记录
		'key'=>$row['id'],
	];
}
json_exit([
	'initialPreview'=>$today_out['url'],
	'initialPreviewConfig'=>$today_out['iPC'],
	'initialPreviewAsData'=>true,
	'uploadUrl'=>'upload.php',
	'overwriteInitial'=>false,
]);
