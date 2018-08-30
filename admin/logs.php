<?php
require '../system/config.php';
$data=[
	'logs'=>$db->select('imginfo',['id','ip','ua','size','path','see','level'],['id[<]'=>100,'ORDER'=>['id'=>'DESC']]),
	'count'=>$db->count('imginfo'),
];

$smarty = new Smarty;
$smarty->debugging = false;//debug
$smarty->caching = false;//缓存
$smarty->cache_lifetime = 120;//缓存有效时间 秒
$smarty->assign("title",'qwe');
// $smarty->assign("page_js",'logs');
$smarty->assign("data",$data);


$smarty->display('tpl_logs.php');