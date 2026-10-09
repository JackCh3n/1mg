<?php
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

$data=[
	'today_upload'=>$db->count('imginfo',['date[~]'=>date('Y-m-d')]),
	'today_illegal'=>$db->count('imginfo',['date[~]'=>date('Y-m-d'),'see'=>0]),
	'all_upload'=>$db->count('imginfo'),
	'all_illegal'=>$db->count('imginfo',['see'=>0]),
];
$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','index');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('page_js','index');
$smarty->assign('data',$data);
$smarty->display('tpl_index.php');
