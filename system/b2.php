<?php
/**
 * Backblaze B2 云存储同步(原生 B2 API,纯curl)
 * - 同步: 把主库未同步的图片上传到 B2,文件名=站点相对路径(i/2610/10/xxx.webp)
 * - 删除: 本站删除(see=0)的图片在下次同步时删除 B2 副本(墓碑机制,可在后台关闭)
 * - 统计: 已同步数/大小/待同步/失败数/最近同步时间
 * 凭据缓存: data/b2_auth.json(23小时);统计: data/b2_meta.json
 */

/**
 * B2是否已启用且配置完整
 * @return bool
 */
function b2_ready(){
	global $config;
	$w=$config['web'];
	return !empty($w['b2_enabled']) && $w['b2_key_id']!=='' && $w['b2_app_key']!=='' && $w['b2_bucket_id']!=='';
}

/**
 * B2配置数组
 * @return array
 */
function b2_conf(){
	global $config;
	$w=$config['web'];
	return [
		'key_id'=>(string)$w['b2_key_id'],
		'app_key'=>(string)$w['b2_app_key'],
		'bucket_id'=>(string)$w['b2_bucket_id'],
		'bucket_name'=>(string)$w['b2_bucket_name'],
		'domain'=>rtrim((string)$w['b2_domain'],'/'),
		'delete_remote'=>!isset($w['b2_delete_remote']) || (int)$w['b2_delete_remote']===1,
		'batch'=>max(1,(int)($w['b2_sync_batch'] ?? 5)),
	];
}

/**
 * 读取/写入B2元数据(统计)
 * @param  array|null $set 写入新数据(null=读取)
 * @return array
 */
function b2_meta($set=null){
	$f=DATA_DIR.'b2_meta.json';
	if ($set!==null) {
		@file_put_contents($f, json_encode($set, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
		return $set;
	}
	if (is_file($f)) {
		$m=json_decode((string)file_get_contents($f),true);
		if (is_array($m)) {
			return $m;
		}
	}
	return ['last_run'=>'','last_error'=>'','uploaded_total'=>0,'deleted_total'=>0,'failed_total'=>0];
}

/**
 * 授权(带23小时缓存)
 * @param  bool $force 忽略缓存
 * @return array ['ok'=>bool,'api'=>..,'token'=>..,'account'=>..,'error'=>..]
 */
function b2_authorize($force=false){
	$cache=DATA_DIR.'b2_auth.json';
	if (!$force && is_file($cache)) {
		$a=json_decode((string)file_get_contents($cache),true);
		if (is_array($a) && !empty($a['token']) && (time()-(int)$a['time'])<23*3600) {
			return ['ok'=>true,'api'=>$a['api'],'token'=>$a['token'],'account'=>$a['account']];
		}
	}
	$c=b2_conf();
	$ch=curl_init('https://api.backblazeb2.com/b2api/v2/b2_authorize_account');
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_USERPWD,$c['key_id'].':'.$c['app_key']);
	curl_setopt($ch,CURLOPT_TIMEOUT,20);
	curl_setopt($ch,CURLOPT_HTTPHEADER,['Accept: application/json']);
	$ca=http_ca_file();
	if ($ca) { curl_setopt($ch,CURLOPT_CAINFO,$ca); }
	$r=curl_exec($ch);
	$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$err=curl_error($ch);
	curl_close($ch);
	if ($r===false) {
		return ['ok'=>false,'error'=>'连接 Backblaze 失败: '.mb_substr($err,0,100)];
	}
	$d=json_decode((string)$r,true);
	if ($code!==200 || empty($d['apiUrl'])) {
		//B2 的 message 字段常为空,依次用 code / HTTP 状态兜底
		$msg='';
		foreach (['message','code'] as $k) {
			if (isset($d[$k]) && trim((string)$d[$k])!=='') { $msg=trim((string)$d[$k]); break; }
		}
		if ($msg==='') { $msg='HTTP '.$code; }
		return ['ok'=>false,'error'=>'授权失败: '.mb_substr($msg,0,120)];
	}
	$save=['api'=>$d['apiUrl'],'token'=>$d['authorizationToken'],'account'=>$d['accountId'],'time'=>time()];
	@file_put_contents($cache, json_encode($save), LOCK_EX);
	return ['ok'=>true,'api'=>$d['apiUrl'],'token'=>$d['authorizationToken'],'account'=>$d['accountId']];
}

/**
 * B2 API POST 调用
 */
function b2_call($api, $token, $function, $body){
	$ch=curl_init($api.'/b2api/v2/'.$function);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body));
	curl_setopt($ch,CURLOPT_TIMEOUT,30);
	curl_setopt($ch,CURLOPT_HTTPHEADER,['Authorization: '.$token,'Content-Type: application/json','Accept: application/json']);
	$r=curl_exec($ch);
	$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);
	$d=json_decode((string)$r,true);
	return ['code'=>$code,'data'=>is_array($d)?$d:null,'raw'=>$r];
}

/**
 * 测试连接:授权 + 列出存储桶(返回桶ID方便复制)
 * @return array
 */
function b2_test(){
	$a=b2_authorize(true);
	if (!$a['ok']) {
		return ['ok'=>false,'msg'=>$a['error']];
	}
	$r=b2_call($a['api'],$a['token'],'b2_list_buckets',['accountId'=>$a['account']]);
	if ((int)$r['code']!==200 || empty($r['data']['buckets'])) {
		$msg=isset($r['data']['message'])?$r['data']['message']:('HTTP '.$r['code']);
		return ['ok'=>false,'msg'=>'授权成功,但读取存储桶失败: '.mb_substr($msg,0,120)];
	}
	$names=[];
	foreach ($r['data']['buckets'] as $b) {
		$names[]=$b['bucketName'].' ('.$b['bucketId'].')'.($b['bucketId']===b2_conf()['bucket_id']?' ←当前':'');
	}
	return ['ok'=>true,'msg'=>'连接成功,账号下有 '.count($names).' 个存储桶: '.implode(' | ',$names)];
}

/**
 * 上传单个文件到B2
 * @return array ['ok'=>bool,'file_id'=>..,'error'=>..]
 */
function b2_upload_file($auth, $local_path, $b2_name){
	if (!is_file($local_path)) {
		return ['ok'=>false,'error'=>'本地文件不存在'];
	}
	$c=b2_conf();
	//获取上传地址(每次批量共用逻辑在外层缓存简单起见逐文件获取,B2限速足够)
	$u=b2_call($auth['api'],$auth['token'],'b2_get_upload_url',['bucketId'=>$c['bucket_id']]);
	if ((int)$u['code']!==200 || empty($u['data']['uploadUrl'])) {
		$msg=isset($u['data']['message'])?$u['data']['message']:('HTTP '.$u['code']);
		return ['ok'=>false,'error'=>'获取上传地址失败: '.mb_substr($msg,0,120)];
	}
	$mime_map=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp'];
	$ext=strtolower(pathinfo($b2_name,PATHINFO_EXTENSION));
	$mime=isset($mime_map[$ext])?$mime_map[$ext]:'application/octet-stream';
	$ch=curl_init($u['data']['uploadUrl']);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_POST,true);
	curl_setopt($ch,CURLOPT_POSTFIELDS,file_get_contents($local_path));
	curl_setopt($ch,CURLOPT_TIMEOUT,120);
	curl_setopt($ch,CURLOPT_HTTPHEADER,[
		'Authorization: '.$u['data']['authorizationToken'],
		'X-Bz-File-Name: '.rawurlencode($b2_name),
		'Content-Type: '.$mime,
		'X-Bz-Content-Sha1: '.sha1_file($local_path),
		'X-Bz-Info-src: 1mg',
	]);
	$ca=http_ca_file();
	if ($ca) { curl_setopt($ch,CURLOPT_CAINFO,$ca); }
	$r=curl_exec($ch);
	$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$err=curl_error($ch);
	curl_close($ch);
	if ($r===false) {
		return ['ok'=>false,'error'=>'上传失败: '.mb_substr($err,0,100)];
	}
	$d=json_decode((string)$r,true);
	if ($code!==200 || empty($d['fileId'])) {
		$msg=isset($d['message'])?$d['message']:('HTTP '.$code);
		return ['ok'=>false,'error'=>'上传失败: '.mb_substr($msg,0,120)];
	}
	return ['ok'=>true,'file_id'=>$d['fileId']];
}

/**
 * 删除B2上的文件版本
 */
function b2_delete_remote($auth, $file_id, $b2_name){
	$r=b2_call($auth['api'],$auth['token'],'b2_delete_file_version',['fileName'=>$b2_name,'fileId'=>$file_id]);
	return (int)$r['code']===200;
}

/**
 * 执行一轮同步(上传未同步 + 处理删除墓碑),返回统计
 * @return array ['ok'=>bool,'uploaded'=>n,'deleted'=>n,'failed'=>n,'error'=>..]
 */
function b2_sync_run(){
	global $db;
	if (!b2_ready()) {
		return ['ok'=>false,'uploaded'=>0,'deleted'=>0,'failed'=>0,'error'=>'B2 未启用或配置不完整'];
	}
	$c=b2_conf();
	$meta=b2_meta();
	$meta['last_run']=date('Y-m-d H:i:s');
	$meta['last_error']='';

	$auth=b2_authorize();
	if (!$auth['ok']) {
		$meta['last_error']=$auth['error'];
		b2_meta($meta);
		return ['ok'=>false,'uploaded'=>0,'deleted'=>0,'failed'=>0,'error'=>$auth['error']];
	}

	$uploaded=0;
	$deleted=0;
	$failed=0;

	//1.处理删除墓碑: 本站已删除(see=0)但B2还有副本的
	if ($c['delete_remote']) {
		$tombstones=$db->select('imginfo',['id','path','b2_file_id'],['see'=>0,'b2_file_id[!]'=>'','LIMIT'=>$c['batch']]);
		foreach ($tombstones as $t) {
			$name=str_replace('\\','/',url_path($t['path']));
			if (b2_delete_remote($auth,$t['b2_file_id'],$name)) {
				$db->update('imginfo',['b2_file_id'=>''],['id'=>$t['id']]);
				$deleted++;
			}else{
				$failed++;
			}
		}
	}else{
		//不同步删除:直接清标记,远端保留
		$db->update('imginfo',['b2_file_id'=>''],['see'=>0,'b2_file_id[!]'=>'']);
	}

	//2.上传未同步的在线图片
	if ($uploaded<$c['batch']) {
		$pend=$db->select('imginfo',['id','path'],['see'=>1,'b2_file_id'=>'','LIMIT'=>($c['batch']-$uploaded)]);
		foreach ($pend as $p) {
			$rel=str_replace('\\','/',url_path($p['path']));
			if (strpos($rel,'i/')!==0) {
				continue;
			}
			$up=b2_upload_file($auth,ROOT.$rel,$rel);
			if ($up['ok']) {
				$db->update('imginfo',['b2_file_id'=>$up['file_id']],['id'=>$p['id']]);
				$uploaded++;
			}else{
				$failed++;
				//授权过期则重授权一次
				if (strpos($up['error'],'bad_auth_token')!==false || strpos($up['error'],'expired')!==false) {
					$auth=b2_authorize(true);
					if (!$auth['ok']) {
						$meta['last_error']=$auth['error'];
						break;
					}
				}
			}
		}
	}

	$meta['uploaded_total']=(int)$meta['uploaded_total']+$uploaded;
	$meta['deleted_total']=(int)$meta['deleted_total']+$deleted;
	$meta['failed_total']=(int)$meta['failed_total']+$failed;
	b2_meta($meta);
	return ['ok'=>true,'uploaded'=>$uploaded,'deleted'=>$deleted,'failed'=>$failed,'error'=>''];
}

/**
 * B2统计(设置页/仪表盘展示)
 * @return array
 */
function b2_stats(){
	global $db;
	$synced=0;
	$synced_size=0;
	try {
		$synced=(int)$db->count('imginfo',['b2_file_id[!]'=>'','see'=>1]);
		$synced_size=(int)$db->sum('imginfo','size',['b2_file_id[!]'=>'','see'=>1]);
		$pending=(int)$db->count('imginfo',['b2_file_id'=>'','see'=>1]);
	}catch (Exception $e) {
		$pending=0;
	}
	$meta=b2_meta();
	return [
		'enabled'=>b2_ready(),
		'synced'=>$synced,
		'synced_size'=>$synced_size,
		'pending'=>$pending,
		'last_run'=>$meta['last_run'],
		'last_error'=>$meta['last_error'],
		'uploaded_total'=>(int)$meta['uploaded_total'],
		'deleted_total'=>(int)$meta['deleted_total'],
	];
}
