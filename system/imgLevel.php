<?php
/**
 * 图片鉴黄(moderatecontent接口),批量处理12张未鉴定的图片
 * 触发方式: 管理员已登录, 或带上口令 ?who=<img_level_pass>(供cron调用)
 */
require 'config.php';
require_once SYSTEM_ROOT.'function.php';

$allowed=false;
if (isset($_SESSION['admin_id'])) {
	$allowed=true;
}elseif (isset($_GET['who']) && hash_equals((string)$config['web']['img_level_pass'],(string)$_GET['who'])) {
	$allowed=true;
}
if (!$allowed) {
	exit('404');
}

$img=$db->select('imginfo',['id','path'],['level'=>0,'see'=>1,'ORDER'=>['id'=>'ASC'],'LIMIT'=>12]);
if (empty($img)) {
	exit('0');
}
foreach ($img as $key => $value) {
	//对图片进行鉴黄
	$apiurl = 'https://www.moderatecontent.com/api/v2?key='.urlencode($config['web']['img_level_key']).'&url='.urlencode($config['web']['cdn'].url_path($value['path']));

	$curl = curl_init($apiurl);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36');
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_TIMEOUT, 15);
	$html = curl_exec($curl);
	curl_close($curl);

	//更新数据库
	$html = json_decode((string)$html,true);
	if (!is_array($html)) {
		echo('0');
		continue;
	}
	if ($html['error_code']==1001) {
		//1001 图片不存在 一般为本地测试 或者真的不存在
		echo('0');
	}elseif ($html['error_code']==0) {
		//error_code 0 为正常识别
		if (isset($html['rating_index']) && $html['rating_index']==3) {
			//rating_index 1 大众 2青少年 3成人
			$img_path=url_path($value['path']);
			if (strpos($img_path,'i/')===0 && is_file(ROOT.$img_path)) {
				unlink(ROOT.$img_path);
			}
			$db->update('imginfo',['see'=>0],['id'=>$value['id']]);
			echo('3');
		}else{
			$db->update('imginfo',['level'=>isset($html['rating_index'])?(int)$html['rating_index']:1],['id'=>$value['id']]);
			echo('1');
		}
	}else{
		//其他状态
		echo('0');
	}
}
