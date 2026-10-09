<?php
/**
 * 图片秒传预检
 * 前端先算好文件md5,若服务器已存在同一图片则无需上传,直接返回地址
 * GET/POST: md5 (32位十六进制)
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';

header('Content-Type: application/json; charset=utf-8');

$file_md5=isset($_REQUEST['md5'])?strtolower(trim((string)$_REQUEST['md5'])):'';
if (!preg_match('/^[0-9a-f]{32}$/',$file_md5)) {
	json_exit(['code'=>110,'error'=>'md5格式不正确']);
}

$db_md5=$db->get('imginfo',['path','see'],['md5'=>$file_md5]);
if (empty($db_md5) || !$db_md5['see'] || !is_file(ROOT.url_path($db_md5['path']))) {
	json_exit(['code'=>404,'error'=>'文件不存在,请直接上传']);
}

json_exit(['code'=>'success','data'=>[
	'url'=>$config['web']['cdn'].url_path($db_md5['path']),
	'md5'=>$file_md5,
	'reused'=>true,
]]);
