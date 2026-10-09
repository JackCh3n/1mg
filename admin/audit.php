<?php
/**
 * 管理员操作审计日志
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

$page=isset($_GET['page'])?(int)$_GET['page']:1;
if ($page<1) {
	$page=1;
}
$per_page=50;

$count=$db->count('admin_log');
$total_pages=max(1,(int)ceil($count/$per_page));
if ($page>$total_pages) {
	$page=$total_pages;
}
$logs=$db->select('admin_log',['id','username','action','target','ip','date'],
	['ORDER'=>['id'=>'DESC'],'LIMIT'=>[$page*$per_page-$per_page,$per_page]]);

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','audit');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('logs',$logs);
$smarty->assign('count',$count);
$smarty->assign('page',$page);
$smarty->assign('total_pages',$total_pages);
$smarty->display('tpl_audit.php');
