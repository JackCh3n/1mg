<?php
/**
 * 图片鉴黄(moderatecontent接口)
 * moderate_run() 供后台"内容审核"页与 system/imgLevel.php cron脚本共用
 */

/**
 * 批量检测未鉴定图片(每次最多12张)
 * @param  int $limit 本轮处理数量
 * @return array ['ok'=>正常,'adult'=>违规已删,'skip'=>跳过]
 */
function moderate_run($limit=12){
	global $db, $config;
	$img=$db->select('imginfo',['id','path'],['level'=>0,'see'=>1,'ORDER'=>['id'=>'ASC'],'LIMIT'=>(int)$limit]);
	$stat=['ok'=>0,'adult'=>0,'skip'=>0];
	if (empty($img)) {
		return $stat;
	}
	foreach ($img as $value) {
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

		$html = json_decode((string)$html,true);
		if (!is_array($html)) {
			$stat['skip']++;
			continue;
		}
		if ($html['error_code']==1001) {
			//1001 图片不存在(本地测试或文件缺失)
			$stat['skip']++;
		}elseif ($html['error_code']==0) {
			if (isset($html['rating_index']) && $html['rating_index']==3) {
				//成人内容:删文件+标记see=0(同后台删除逻辑,可恢复)
				$img_path=url_path($value['path']);
				if (strpos($img_path,'i/')===0 && is_file(ROOT.$img_path)) {
					unlink(ROOT.$img_path);
				}
				$db->update('imginfo',['see'=>0],['id'=>$value['id']]);
				$stat['adult']++;
			}else{
				$db->update('imginfo',['level'=>isset($html['rating_index'])?(int)$html['rating_index']:1],['id'=>$value['id']]);
				$stat['ok']++;
			}
		}else{
			$stat['skip']++;
		}
	}
	return $stat;
}
