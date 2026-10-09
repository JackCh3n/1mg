<?php
/**
 * 后台仪表盘:统计概览 + 活跃日历 + 归档管理
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();
require_once SYSTEM_ROOT.'archive.php';

//到点自动归档(6小时一次),失败静默
try {
	archive_auto_run((int)$config['web']['retention_online'],(int)$config['web']['retention_archive']);
} catch (Exception $e) {
	//归档失败不影响页面
}

//手动归档
$archive_msg='';
if (isset($_GET['action']) && $_GET['action']=='archive_run' && isset($_GET['token'])) {
	if (hash_equals(csrf_token(),(string)$_GET['token'])) {
		archive_run((int)$config['web']['retention_online'],(int)$config['web']['retention_archive']);
		admin_log('archive_run','retention='.$config['web']['retention_online'].'d/'.$config['web']['retention_archive'].'d');
		header('Location: index.php');
		exit();
	}
	$archive_msg='非法请求';
}

//孤儿文件清理
$orphan_msg='';
if (isset($_GET['action']) && $_GET['action']=='orphan' && isset($_GET['token'])) {
	if (hash_equals(csrf_token(),(string)$_GET['token'])) {
		$r=orphan_clean();
		admin_log('orphan_clean','removed='.$r['removed'].',freed='.format_size($r['freed']));
		$orphan_msg='清理完成: 移除 '.$r['removed'].' 个孤儿文件,释放 '.format_size($r['freed']);
	}else{
		$orphan_msg='非法请求';
	}
}

//磁盘用量
$usage=img_disk_usage();

//统计
$data=[
	'today_upload'=>$db->count('imginfo',['date[~]'=>date('Y-m-d')]),
	'week_upload'=>$db->count('imginfo',['date[>=]'=>date('Y-m-d 00:00:00',strtotime('-6 days'))]),
	'online_total'=>$db->count('imginfo'),
	'see_deleted'=>$db->count('imginfo',['see'=>0]),
];

//活跃日历:最近371天(53周)的每日上传统计
$cal=[];
$start=date('Y-m-d', strtotime('-370 days'));
$rows=$db->select('stats_daily',['date','count'],['date[>=]'=>$start]);
foreach ($rows as $r) {
	$cal[$r['date']]=(int)$r['count'];
}
$max=1;
foreach ($cal as $c) {
	if ($c>$max) {
		$max=$c;
	}
}
//按连续日期补齐输出(便于前端直接渲染)
$calendar=[];
for ($i=370; $i>=0; $i--) {
	$d=date('Y-m-d', strtotime("-$i days"));
	$c=isset($cal[$d])?(int)$cal[$d]:0;
	//0-4级颜色
	$lv=0;
	if ($c>0) {
		$lv=1;
	}
	if ($c>=$max*0.25) {
		$lv=2;
	}
	if ($c>=$max*0.5) {
		$lv=3;
	}
	if ($c>=$max*0.75) {
		$lv=4;
	}
	$calendar[]=['d'=>$d,'c'=>$c,'lv'=>$lv];
}

//归档信息
$meta=archive_meta();
$archive_files=archive_list(30);
$archive_rows_total=0;
foreach ($meta['files'] as $n) {
	$archive_rows_total+=(int)$n;
}

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','index');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('page_js','index');
$smarty->assign('data',$data);
$smarty->assign('calendar_json',json_encode($calendar));
$smarty->assign('retention_online',(int)$config['web']['retention_online']);
$smarty->assign('retention_archive',(int)$config['web']['retention_archive']);
$smarty->assign('archive_last_run',$meta['last_run']);
$smarty->assign('archive_total',$archive_rows_total);
$smarty->assign('archive_files',$archive_files);
$smarty->assign('csrf',csrf_token());
$smarty->assign('archive_msg',$archive_msg);
$smarty->assign('orphan_msg',$orphan_msg);
$smarty->assign('disk_size',format_size($usage['size']));
$smarty->assign('disk_files',$usage['files']);
$smarty->display('tpl_index.php');
