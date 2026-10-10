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

//批量删除(多选)
if (!empty($_POST['batch_del']) && !empty($_POST['ids'])) {
	if (csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$n=0;
		foreach ((array)$_POST['ids'] as $bid) {
			$bid=(int)$bid;
			$bpath=$db->get('imginfo','path',['id'=>$bid]);
			if (empty($bpath)) { continue; }
			trash_put($bpath);
			$db->update('imginfo',['see'=>0],['id'=>$bid]);
			$n++;
		}
		admin_log('image_batch_delete','count='.$n);
	}
	header('Location: logs.php?'.http_build_query(array_filter(['ip'=>$_GET['ip'] ?? '','kw'=>$_GET['kw'] ?? '','md5'=>$_GET['md5'] ?? ''])));
	exit();
}

//筛选条件(参数由Medoo参数化查询处理,不存在注入)
$search_ip=isset($_GET['ip'])?trim((string)$_GET['ip']):'';
$search_kw=isset($_GET['kw'])?trim((string)$_GET['kw']):'';
$search_md5=isset($_GET['md5'])?strtolower(trim((string)$_GET['md5'])):'';
$where=['ORDER'=>['id'=>'DESC']];
if ($search_ip!=='') {
	if (!filter_var($search_ip, FILTER_VALIDATE_IP)) {
		$search_ip='';
	}else{
		$where['ip']=$search_ip;
	}
}
if ($search_kw!=='') {
	$where['name[~]']='%'.$search_kw.'%';
}
if ($search_md5!=='' && preg_match('/^[0-9a-f]{32}$/',$search_md5)) {
	$where['md5']=$search_md5;
}else{
	$search_md5='';
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
	'search_kw'=>$search_kw,
	'search_md5'=>$search_md5,
];

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','logs');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('data',$data);

$smarty->display('tpl_logs.php');
