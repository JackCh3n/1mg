<?php
/**
 * 图片上传
 * 1.先完整校验(上传错误/大小/扩展名/MIME/内容扫描)再入库
 * 2.md5相同且未被删除的图片直接返回已有URL(秒传)
 * 3.GD重编码压缩(jpg/png),BMP转PNG,GIF原样保存
 */
require 'system'.DIRECTORY_SEPARATOR.'config.php';
require_once SYSTEM_ROOT.'function.php';
require SYSTEM_ROOT.'image.php';

header('Content-Type: application/json; charset=utf-8');

//API令牌门(配置了全局令牌或启用了DB令牌时,上传必须携带其中之一)
if (api_token_required()) {
	$given=isset($_POST['api_token'])?$_POST['api_token']:(isset($_GET['api_token'])?$_GET['api_token']:(isset($_SERVER['HTTP_X_API_TOKEN'])?$_SERVER['HTTP_X_API_TOKEN']:''));
	if (!is_string($given) || api_token_check($given)===false) {
		json_exit(['code'=>401,'error'=>'API token 无效']);
	}
}

//没有选择文件(原写法 empty($_FILES == false) 逻辑相反,靠巧合工作)
if (empty($_FILES['file']) || !is_array($_FILES['file']) || empty($_FILES['file']['tmp_name'])) {
	json_exit(['code'=>110,'error'=>'没有选择文件']);
}

//IP黑名单拦截
if (ip_banned(get_client_ip())) {
	json_exit(['code'=>403,'error'=>'你的 IP 已被封禁,无法上传']);
}

//上传频率限制(按IP按小时,含失败尝试)
if (!rate_limit_check()) {
	json_exit(['code'=>429,'error'=>'上传太频繁,请稍后再试']);
}

$file=$_FILES['file'];

//上传过程出错
if (!empty($file['error'])) {
	$errors=[
		UPLOAD_ERR_INI_SIZE=>'文件超过了php.ini限制的大小',
		UPLOAD_ERR_FORM_SIZE=>'文件超过了表单限制的大小',
		UPLOAD_ERR_PARTIAL=>'文件只有部分被上传',
		UPLOAD_ERR_NO_FILE=>'没有选择文件',
	];
	json_exit(['code'=>110,'error'=>isset($errors[$file['error']])?$errors[$file['error']]:'文件上传失败(error '.$file['error'].')']);
}

//大小限制(后台可配,默认5M)
$max_bytes=((int)$config['web']['max_size_mb'])*1048576;
if ($file['size'] > $max_bytes) {
	json_exit(['code'=>110,'error'=>'对不起,上传的文件大于'.$config['web']['max_size_mb'].'M']);
}

//扩展名白名单(统一转小写再比对,原来的写法 .JPG 会被拒)
$file_ext=strtolower(ltrim((string)strrchr($file['name'],'.'),'.'));
if ($file_ext==='jpeg') {
	$file_ext='jpg';
}
if (!in_array($file_ext,['jpg','png','bmp','gif','webp'])) {
	json_exit(['code'=>110,'error'=>'扩展名不允许']);
}

//校验文件真实内容是图片(getimagesize只认文件头,故配合内容扫描)
$size_info=getimagesize($file['tmp_name']);
if ($size_info===false || !in_array($size_info['mime'],['image/jpeg','image/png','image/gif','image/bmp','image/x-ms-bmp','image/x-bmp','image/webp'])) {
	json_exit(['code'=>110,'error'=>'只允许上传图片文件']);
}

//图片内不应包含 "<?php" 等标签(gif不做重编码,必须靠这里拦截图片马)
if (!scan_image_safe($file['tmp_name'])) {
	json_exit(['code'=>110,'error'=>'图片内容不合法']);
}

//计算md5
$file_md5=md5_file($file['tmp_name']);

//i/2610/09 目录一律用 / 分隔,保证图片URL在各平台下都正确
$file_path='i/'.date('ym').'/'.date('d');

//用户配额检查(登录用户;秒传不消耗配额)
$uid=empty($_SESSION['user_id'])?0:(int)$_SESSION['user_id'];
if ($uid) {
	$quota_err=user_quota_check($uid, (int)$file['size']);
	if ($quota_err!=='') {
		json_exit(['code'=>110,'error'=>$quota_err]);
	}
}

//秒传:同一文件已存在且未被删除,直接返回已有地址
$db_md5=$db->get('imginfo',['id','path','see','delete_token'],['md5'=>$file_md5]);
if (!empty($db_md5)) {
	if ($db_md5['see']) {
		$db_path=url_path($db_md5['path']);
		if (is_file(ROOT.$db_path)) {
			//文件已在服务器上,丢弃本次上传
			@unlink($file['tmp_name']);
			json_exit(['code'=>'success','data'=>['url'=>$config['web']['cdn'].$db_path,'md5'=>$file_md5,'delete'=>$config['web']['cdn'].'delete.php?token='.urlencode($db_md5['delete_token']),'reused'=>true]]);
		}else{
			//记录存在但文件丢失,用本次上传补回文件
			$dir=ROOT.dirname($db_path);
			if (!is_dir($dir) && !@mkdir($dir,0755,true)) {
				json_exit(['code'=>110,'error'=>'目录创建失败']);
			}
			if (!@move_uploaded_file($file['tmp_name'],ROOT.$db_path) && !@rename($file['tmp_name'],ROOT.$db_path)) {
				json_exit(['code'=>110,'error'=>'文件保存失败']);
			}
			json_exit(['code'=>'success','data'=>['url'=>$config['web']['cdn'].$db_path,'md5'=>$file_md5,'delete'=>$config['web']['cdn'].'delete.php?token='.urlencode($db_md5['delete_token']),'reused'=>true]]);
		}
	}else{
		//已被删除/判违规的文件,不允许再次上传
		json_exit(['code'=>110,'error'=>'该图片已被删除或判定违规,不允许重复上传']);
	}
}

//创建文件夹(递归)
if (!is_dir(ROOT.$file_path) && !@mkdir(ROOT.$file_path,0755,true)) {
	json_exit(['code'=>110,'error'=>'目录创建失败']);
}

//压缩保存(WebP转换按后台设置,最终扩展名以压缩结果为准;落盘路径=基础名+最终扩展名)
$name_base=date('His').mt_rand(100,999);
$result=compress_image($file['tmp_name'],ROOT.$file_path.'/'.$name_base,$file_ext,!empty($config['web']['webp_enabled']));
if (!$result['ok']) {
	json_exit(['code'=>110,'error'=>'文件保存失败']);
}
$new_name=$name_base.'.'.$result['ext'];
$db_path=$file_path.'/'.$new_name;

//入库(并发下md5撞车则以已有记录为准,同样走秒传)
$insert_ok=false;
$delete_token=random_token();
try {
	$insert_ok=(bool)$db->insert('imginfo',[
		'ua'=>isset($_SERVER['HTTP_USER_AGENT'])?substr((string)$_SERVER['HTTP_USER_AGENT'],0,150):'',
		'ip'=>get_client_ip(),
		'date'=>date('Y-m-d H:i:s'),
		'md5'=>$file_md5,
		'path'=>$db_path,
		'name'=>$new_name,
		'size'=>$result['size'],
		'compress'=>$result['compress'],
		'level'=>0,
		'see'=>1,
		'delete_token'=>$delete_token,
		'user_id'=>$uid,
	]);
} catch (Exception $e) {
	$insert_ok=false;
}
if (!$insert_ok || $db->error()[1]) {
	$err_code=$db->error()[1];
	if ($err_code==1062 || $err_code==19) {
		//唯一索引冲突:另一个请求刚插入了同一文件,返回那条记录(秒传)
		$again=$db->get('imginfo',['path','delete_token'],['md5'=>$file_md5]);
		if (!empty($again)) {
			@unlink(ROOT.$db_path);
			json_exit(['code'=>'success','data'=>['url'=>$config['web']['cdn'].url_path($again['path']),'md5'=>$file_md5,'delete'=>$config['web']['cdn'].'delete.php?token='.urlencode($again['delete_token']),'reused'=>true]]);
		}
	}elseif ($err_code) {
		json_exit(['code'=>110,'error'=>'数据入库失败']);
	}
}

//每日上传统计+1(活跃日历数据源,独立于保留策略长期保存;失败不影响上传)
try {
	$st=$db->pdo->prepare("INSERT INTO stats_daily (date, count) VALUES (?, 1)
		ON CONFLICT(date) DO UPDATE SET count = count + 1");
	$st->execute([date('Y-m-d')]);
} catch (Exception $e) {
	//统计失败可忽略
}

json_exit(['code'=>'success','data'=>[
	'url'=>$config['web']['cdn'].$db_path,
	'md5'=>$file_md5,
	'delete'=>$config['web']['cdn'].'delete.php?token='.urlencode($delete_token),
	'reused'=>false,
	'size'=>$result['size'],
	'compress'=>$result['compress'],
]]);
