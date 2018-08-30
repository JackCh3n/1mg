<?php 
if (!empty($_POST)) {
    print_r($_POST);
    exit();
}
require '../system/config.php';
$smarty = new Smarty;
$smarty->debugging = false;//debug
$smarty->caching = false;//缓存
$smarty->cache_lifetime = 120;//缓存有效时间 秒
$smarty->assign("title",'qwe');
$smarty->assign('config',$config);
// $smarty->assign("data",$data);


$smarty->display('tpl_seting.php');