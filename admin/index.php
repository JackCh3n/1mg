<?php
require '../system/config.php';
$data=[
	'today_upload'=>$db->count('imginfo',['date[~]'=>date('Y-m-d')]),
	'today_illegal'=>$db->count('imginfo',['date[~]'=>date('Y-m-d'),'see'=>0]),
	'all_upload'=>$db->count('imginfo'),
	'all_illegal'=>$db->count('imginfo',['see'=>0]),
];
$smarty = new Smarty;
$smarty->debugging = false;//debug
$smarty->caching = false;//缓存
$smarty->cache_lifetime = 120;//缓存有效时间 秒
$smarty->assign('title',$config['web']['title']);
$smarty->assign('page_js','index');
$smarty->assign('data',$data);


$smarty->display('tpl_index.php');
