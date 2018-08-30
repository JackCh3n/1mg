<?php
require 'config.php';
require SYSTEM_ROOT.'function.php';
if (empty($_GET)) {
	exit('404');
}
if (isset($_GET['who'])) {
	if ($_GET['who']!=$config['web']['img_level_pass']) {
		exit('404');
	}
	$img=$db->select('img',['path'],['level'=>0,'LIMIT'=>12]);
	if (empty($img)) {
		exit('0');
	}
    foreach ($info as $key => $value) {
        //对图片进行鉴黄

        $apiurl = 'https://www.moderatecontent.com/api/v2?key='.$config['web']['img_level_key'].'&url='.$config['web']['cdn'].$value['path'];

        $curl = curl_init($apiurl);

        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36');
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $html = curl_exec($curl);
        curl_close($curl);

        //更新数据库
        $html = json_decode($html,true);
        if ($html['error_code']==1001) {
        	//1001 图片不存在 一般为本地测试 或者真的不存在
            echo('0');
        }elseif ($html['error_code']==0) {
        	//error_code 0 为正常识别
            if ($html['rating_index']==3) {
            	//rating_index 1 大众 2青少年 3成人
                unlink(ROOT.str_replace('/',DIRECTORY_SEPARATOR,$value['path']));

                $database->update('imginfo',['level' =>  $level['error_code'],'see'=>0],['id'   => $value['id']]);
                echo('3');
            }else{
                $database->update('imginfo',['level' =>  $level['error_code']],['id'   => $value['id']]);
                echo('1');
            }
        }else{
        	//其他状态
            echo('0');
        }
        
    }
	
	
}