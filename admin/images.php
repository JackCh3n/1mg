<?php
require '../system/config.php';

if (empty($_GET)) {
	$smarty = new Smarty;
	$smarty->debugging = false;//debug
	$smarty->caching = false;//缓存
	$smarty->cache_lifetime = 120;//缓存有效时间 秒
	$smarty->assign('title',$config['web']['title']);
	$smarty->assign("page_jscode",'<script src="../view/bootstrap-fileinput-4.4.9/js/fileinput.min.js" type="text/javascript"></script><script src="../view/bootstrap-fileinput-4.4.9/js/locales/zh.js" type="text/javascript"></script><script src="../view/admin/js/images.js" type="text/javascript"></script>');
	// $smarty->assign("data",$data);


	$smarty->display('tpl_images.php');
	exit();
}
if (isset($_GET['type'])) {
	if ($_GET['type']=='json') {
		$today_data=$db->select('imginfo',['id','size','path'],['see'=>1,'id[<]'=>100,'ORDER'=>['id'=>'DESC']]);
		$for_n=count($today_data)>12?12:count($today_data);
		for ($i=0; $i <$for_n ; $i++) { 
			$today_out['url'][$i]=$config['web']['cdn'].$today_data[$i]['path'];
			$today_out['iPC'][$i]=[
				'caption'=>$today_data[$i]['path'],
				'size'=>$today_data[$i]['size'],
				'key'=>$today_data[$i]['id'],
				'url'=>'images.php?type=del',
			];
		}
		exit(json_encode([
			'initialPreview'=>$today_out['url'],
			'initialPreviewConfig'=>$today_out['iPC'],
			'initialPreviewAsData'=>true,
			'uploadUrl'=>'upload.php',
			'overwriteInitial'=>true
		]));
	}
	if ($_GET['type']=='del'&&isset($_POST['key'])) {
		$del_id=(int)$_POST['key'];
		$del_path=$db->get('imginfo','path',['id'=>$del_id]);
		// print_r(ROOT.$del_path);a
		$db->update('imginfo',['see'=>0],['id'=>$del_id]);
		// /Volumes/Soft/wwwroot/1mg/i/1808/30/004510526.jpg
		if (file_exists(ROOT.$del_path)) {
			unlink(ROOT.$del_path);
		}
		exit(json_encode(['code'=>'success','error'=>'删除成功']));
	}

}

// print_r($data);exit();
