<?php
/**
 * 图片鉴黄(moderatecontent接口)
 * 接口地址: https://www.moderatecontent.com/api/v2?key=KEY&url=图片URL
 * 支持多Key轮询:mod_keys表管理,当月用量统计,超额/停用/无效Key自动跳过
 * moderate_run() 供后台"内容审核"页与 system/imgLevel.php cron脚本共用
 */

//moderatecontent 免费Key配额(官方未明示,按社区反馈与实际观察设定为日500/月2500,可按实际调整)
define('MOD_DAILY_LIMIT', 500);
define('MOD_MONTHLY_LIMIT', 2500);

/**
 * 读取全部Key(空池时用配置里的img_level_key播种;日/月翻转自动清零计数)
 * @return array
 */
function mod_keys_all(){
	global $db, $config;
	$month=date('Y-m');
	$day=date('Y-m-d');
	//月份翻转:月计数清零
	$db->update('mod_keys',['used_month'=>$month,'used_count'=>0],['used_month[!]'=>$month]);
	//日期翻转:日计数清零
	$db->update('mod_keys',['used_day'=>$day,'used_day_count'=>0],['used_day[!]'=>$day]);
	//空池播种:把设置里的默认Key收进来
	$count=$db->count('mod_keys');
	if (!$count && !empty($config['web']['img_level_key'])) {
		$db->insert('mod_keys',['key'=>$config['web']['img_level_key'],'enabled'=>1,'status'=>'ok','used_month'=>$month,'used_count'=>0,'used_day'=>$day,'used_day_count'=>0]);
	}
	return $db->select('mod_keys',['id','key','enabled','status','used_month','used_count','used_day','used_day_count'],['ORDER'=>['id'=>'ASC']]);
}

/**
 * 从Key池取下一个可用Key(轮询:启用 且 日/月配额均有余量 且 非无效Key)
 * @return string|null
 */
function mod_keys_pick(){
	global $db;
	$month=date('Y-m');
	$day=date('Y-m-d');
	foreach (mod_keys_all() as $k) {
		if (empty($k['enabled']) || $k['status']==='invalid') {
			continue;
		}
		$usedMonth=($k['used_month']===$month)?(int)$k['used_count']:0;
		$usedDay=($k['used_day']===$day)?(int)$k['used_day_count']:0;
		if ($usedMonth>=MOD_MONTHLY_LIMIT || $usedDay>=MOD_DAILY_LIMIT) {
			continue;
		}
		return $k['key'];
	}
	return null;
}

/**
 * 记录一次Key使用(当日/当月计数各+1)
 * @param string $key
 */
function mod_key_use($key){
	global $db;
	$month=date('Y-m');
	$day=date('Y-m-d');
	$db->pdo->prepare("INSERT INTO mod_keys (key, used_month, used_count, used_day, used_day_count)
		VALUES (?, ?, 1, ?, 1)
		ON CONFLICT(key) DO UPDATE SET
			used_count = CASE WHEN used_month = ? THEN used_count + 1 ELSE 1 END,
			used_day = ?,
			used_day_count = CASE WHEN used_day = ? THEN used_day_count + 1 ELSE 1 END")
		->execute([$key, $month, $day, $month, $day, $day]);
}

/**
 * 标记Key状态(ok/invalid)
 * @param string $key
 * @param string $status
 */
function mod_key_mark($key, $status){
	global $db;
	$db->update('mod_keys',['status'=>$status],['key'=>$key]);
}

/**
 * 批量检测未鉴定图片(每次最多12张),自动在可用Key间轮询
 * @param  int $limit 本轮处理数量
 * @return array ['ok'=>正常,'adult'=>违规已删,'skip'=>跳过,'nokey'=>无可用Key]
 */
function moderate_run($limit=12){
	global $db, $config;
	$img=$db->select('imginfo',['id','path'],['level'=>0,'see'=>1,'ORDER'=>['id'=>'ASC'],'LIMIT'=>(int)$limit]);
	$stat=['ok'=>0,'adult'=>0,'skip'=>0,'nokey'=>0];
	if (empty($img)) {
		return $stat;
	}
	foreach ($img as $value) {
		$key=mod_keys_pick();
		if ($key===null) {
			$stat['nokey']+=count($img)-array_sum($stat);
			break;
		}
		$apiurl = 'https://www.moderatecontent.com/api/v2?key='.urlencode($key).'&url='.urlencode($config['web']['cdn'].url_path($value['path']));
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
		$rc=isset($html['error_code'])?(int)$html['error_code']:-1;
		if ($rc==1001) {
			//1001 图片不存在(本地测试或文件缺失),不消耗Key用量
			$stat['skip']++;
		}elseif ($rc==2 || $rc==3) {
			//Key缺失/无效:标记并换下一个Key重试本图
			mod_key_mark($key,'invalid');
			$retry=mod_keys_pick();
			if ($retry!==null && $retry!==$key) {
				$stat['skip']++;//下轮再用新Key处理,避免本轮阻塞
			}else{
				$stat['nokey']++;
			}
		}elseif ($rc==0) {
			mod_key_use($key);
			if (isset($html['rating_index']) && $html['rating_index']==3) {
				//成人内容:删文件+标记see=0(同后台删除逻辑,可恢复)
				trash_put($value['path']);
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
