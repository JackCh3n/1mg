<?php
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

//真分页
$page=isset($_GET['page'])?(int)$_GET['page']:1;
if ($page<1) {
	$page=1;
}
$per_page=50;

//按IP搜索(只做格式校验,参数由Medoo参数化查询处理,不存在注入)
$search_ip=isset($_GET['ip'])?trim((string)$_GET['ip']):'';
$where=['ORDER'=>['id'=>'DESC']];
if ($search_ip!=='') {
	if (!filter_var($search_ip, FILTER_VALIDATE_IP)) {
		$search_ip='';
	}else{
		$where['ip']=$search_ip;
	}
}

$count=$db->count('imginfo',$where);
$total_pages=max(1,(int)ceil($count/$per_page));
if ($page>$total_pages) {
	$page=$total_pages;
}
$where['LIMIT']=[$page*$per_page-$per_page,$per_page];
$logs=$db->select('imginfo',['id','ip','ua','size','path','see','level','date'],$where);

$data=[
	'logs'=>$logs,
	'count'=>$count,
	'page'=>$page,
	'total_pages'=>$total_pages,
	'search_ip'=>$search_ip,
];

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','logs');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('data',$data);

$smarty->display('tpl_logs.php');
