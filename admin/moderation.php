<?php
/**
 * 内容审核: 待鉴定图片人工审核 + 一键自动检测
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();
require_once SYSTEM_ROOT.'moderate.php';

$msg='';
//人工审核操作
if (!empty($_POST['do']) && !empty($_POST['key'])) {
	if (csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$key=(int)$_POST['key'];
		$row=$db->get('imginfo',['id','path'],['id'=>$key,'see'=>1]);
		if (!empty($row)) {
			if ($_POST['do']==='adult') {
				$real_path=url_path($row['path']);
				if (strpos($real_path,'i/')===0 && is_file(ROOT.$real_path)) {
					@unlink(ROOT.$real_path);
				}
				$db->update('imginfo',['see'=>0],['id'=>$key]);
				admin_log('moderation_remove', $row['path']);
				$msg='已删除违规图片';
			}elseif ($_POST['do']==='ok') {
				$db->update('imginfo',['level'=>1],['id'=>$key]);
				admin_log('moderation_ok', $row['path']);
				$msg='已标记为正常';
			}
		}
	}else{
		$msg='页面已过期,请重试';
	}
}

//一键自动检测
if (!empty($_GET['action']) && $_GET['action']=='auto' && isset($_GET['token'])) {
	if (hash_equals(csrf_token(),(string)$_GET['token'])) {
		$stat=moderate_run(12);
		admin_log('moderation_auto', 'ok='.$stat['ok'].',adult='.$stat['adult'].',skip='.$stat['skip']);
		$msg='自动检测完成: 正常 '.$stat['ok'].' 张,违规删除 '.$stat['adult'].' 张,跳过 '.$stat['skip'].' 张';
	}else{
		$msg='非法请求';
	}
}

$pending=$db->select('imginfo',['id','path','date'],['level'=>0,'see'=>1,'ORDER'=>['id'=>'DESC'],'LIMIT'=>60]);
$pending_count=$db->count('imginfo',['level'=>0,'see'=>1]);
$checked_count=$db->count('imginfo',['level[>]'=>0]);

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','moderation');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('msg',$msg);
$smarty->assign('pending',$pending);
$smarty->assign('pending_count',$pending_count);
$smarty->assign('checked_count',$checked_count);
$smarty->display('tpl_moderation.php');
