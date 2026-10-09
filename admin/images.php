<?php
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

if (empty($_GET)) {
	$smarty = admin_smarty();
	$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','images');
	$smarty->assign('admin_user',$_SESSION['admin_user']);
	$smarty->assign('csrf',csrf_token());
	$smarty->assign("page_jscode",'<script src="https://cdnjs.loli.net/ajax/libs/jquery/2.1.4/jquery.min.js" type="text/javascript"></script><script src="../view/bootstrap-fileinput-4.4.9/js/fileinput.min.js" type="text/javascript"></script><script src="../view/bootstrap-fileinput-4.4.9/js/locales/zh.js" type="text/javascript"></script><script src="../view/admin/js/images.js" type="text/javascript"></script>');
	$smarty->display('tpl_images.php');
	exit();
}
if (isset($_GET['type'])) {
	if ($_GET['type']=='json') {
		//最近上传的12张
		$today_data=$db->select('imginfo',['id','size','path'],['see'=>1,'ORDER'=>['id'=>'DESC'],'LIMIT'=>12]);
		$today_out=['url'=>[],'iPC'=>[]];
		foreach ($today_data as $i=>$row) {
			$today_out['url'][$i]=$config['web']['cdn'].url_path($row['path']);
			$today_out['iPC'][$i]=[
				'caption'=>url_path($row['path']),
				'size'=>(int)$row['size'],
				'key'=>$row['id'],
				'url'=>'images.php?type=del',
			];
		}
		json_exit([
			'initialPreview'=>$today_out['url'],
			'initialPreviewConfig'=>$today_out['iPC'],
			'initialPreviewAsData'=>true,
			//uploadUrl相对于消费该json的页面(admin/目录)
			'uploadUrl'=>'../upload.php',
			'deleteExtraData'=>['_csrf'=>csrf_token()],
			'overwriteInitial'=>true
		]);
	}
	//删除(软删:标记see=0并删除文件)
	if ($_GET['type']=='del' && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['key'])) {
		if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
			json_exit(['code'=>110,'error'=>'非法请求']);
		}
		$del_id=(int)$_POST['key'];
		$del_path=$db->get('imginfo','path',['id'=>$del_id]);
		if (empty($del_path)) {
			json_exit(['code'=>110,'error'=>'记录不存在']);
		}
		$db->update('imginfo',['see'=>0],['id'=>$del_id]);
		//只允许删除图片目录下的文件,防止路径被篡改后误删任意文件
		$real_path=url_path($del_path);
		if (strpos($real_path,'i/')===0 && is_file(ROOT.$real_path)) {
			@unlink(ROOT.$real_path);
		}
		json_exit(['code'=>'success','error'=>'删除成功']);
	}
	//恢复(重新显示被删除的图片记录;文件已删除的记录保留,重新上传同md5文件会自动补回文件)
	if ($_GET['type']=='restore' && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['key'])) {
		if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
			json_exit(['code'=>110,'error'=>'非法请求']);
		}
		$rest_id=(int)$_POST['key'];
		$db->update('imginfo',['see'=>1],['id'=>$rest_id]);
		json_exit(['code'=>'success','error'=>'恢复成功']);
	}
}
