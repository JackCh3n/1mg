<?php
/**
 * WebDAV 云存储同步(适配日本免费空间等标准 WebDAV 服务)
 * - 上传: 按本站路径结构 PUT 到 {wd_url}/i/2610/10/xxx.webp,自动 MKCOL 建目录
 * - 删除: 本站删除(see=0)的图片同步 DELETE 远端副本(可关)
 * - 测试: PROPFIND 探测目录是否可用
 * - 统计: data/wd_meta.json
 */

/**
 * WebDAV 是否已启用且配置完整
 * @return bool
 */
function wd_ready(){
	global $config;
	$w=$config['web'];
	return !empty($w['wd_enabled']) && trim((string)$w['wd_url'])!=='';
}

/**
 * WebDAV 配置
 * @return array
 */
function wd_conf(){
	global $config;
	$w=$config['web'];
	return [
		'url'=>rtrim(trim((string)$w['wd_url']),'/'),
		'user'=>(string)$w['wd_user'],
		'pass'=>(string)$w['wd_pass'],
		'domain'=>rtrim((string)$w['wd_domain'],'/'),
		'delete_remote'=>!isset($w['wd_delete_remote']) || (int)$w['wd_delete_remote']===1,
		'batch'=>max(1,(int)(isset($w['wd_sync_batch'])?$w['wd_sync_batch']:5)),
	];
}

/**
 * 读写 WebDAV 同步元数据
 * @param  array|null $set
 * @return array
 */
function wd_meta($set=null){
	$f=DATA_DIR.'wd_meta.json';
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
 * WebDAV 请求(Basic认证 + HTTPS证书验证)
 * @param  string      $method PROPFIND/PUT/DELETE/MKCOL
 * @param  string      $url    绝对URL
 * @param  string|null $body
 * @param  array       $extra  额外请求头
 * @return array ['code'=>int,'body'=>string,'err'=>string]
 */
function wd_request($method, $url, $body=null, $extra=[]){
	$c=wd_conf();
	$headers=array_merge(['Expect:'], $extra);
	$ch=curl_init($url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
	curl_setopt($ch,CURLOPT_TIMEOUT,60);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
	if ($c['user']!=='' || $c['pass']!=='') {
		curl_setopt($ch,CURLOPT_USERPWD,$c['user'].':'.$c['pass']);
		curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
	}
	if ($body!==null) {
		curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
	}
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
	//标准WebDAV服务器一般不跟随重定向,避免PUT丢失方法
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
	$ca=http_ca_file();
	if ($ca) {
		curl_setopt($ch,CURLOPT_CAINFO,$ca);
	}
	$resp=curl_exec($ch);
	$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$err=curl_error($ch);
	curl_close($ch);
	return ['code'=>$code,'body'=>$resp===false?'':(string)$resp,'err'=>$err];
}

/**
 * 测试连接(PROPFIND Depth:0)
 * @return array ['ok'=>bool,'msg'=>string]
 */
function wd_test(){
	$c=wd_conf();
	if ($c['url']==='') {
		return ['ok'=>false,'msg'=>'请先填写 WebDAV 地址'];
	}
	$r=wd_request('PROPFIND', $c['url'].'/', null, ['Depth: 0','Content-Type: application/xml']);
	if ($r['err']!=='') {
		return ['ok'=>false,'msg'=>'连接失败: '.mb_substr($r['err'],0,100)];
	}
	if ($r['code']===207 || $r['code']===200) {
		$is_dav=(stripos($r['body'],'multistatus')!==false);
		return ['ok'=>true,'msg'=>'连接成功'.($is_dav?'(标准 WebDAV 响应)':'') .',目录可访问'];
	}
	if ($r['code']===401 || $r['code']===403) {
		return ['ok'=>false,'msg'=>'认证失败(HTTP '.$r['code'].'),请检查用户名/密码'];
	}
	if ($r['code']===404) {
		return ['ok'=>false,'msg'=>'目录不存在(404),请先在空间中创建该目录'];
	}
	if ($r['code']===405) {
		//部分空间不支持PROPFIND,降级用PUT测试文件探测
		return ['ok'=>false,'msg'=>'目录存在但不支持 PROPFIND(405),可能不是标准 WebDAV 服务'];
	}
	return ['ok'=>false,'msg'=>'连接异常,服务器返回 HTTP '.$r['code']];
}

/**
 * 递归创建远端目录(MKCOL),已存在(405)视为成功
 * @param  string $remote_path 例: i/2610/10
 * @return bool
 */
function wd_ensure_dir($remote_path){
	$c=wd_conf();
	$parts=explode('/',trim($remote_path,'/'));
	$cur='';
	foreach ($parts as $p) {
		if ($p==='') {
			continue;
		}
		$cur.=($cur===''?'':'/').$p;
		$r=wd_request('MKCOL', $c['url'].'/'.$cur.'/');
		//201/200=创建成功,405=已存在,301=服务器重定向(尾斜杠),其余视为失败
		if (!in_array($r['code'],[201,200,405,301],true)) {
			return false;
		}
	}
	return true;
}

/**
 * 上传单个文件
 * @param  string $local_file 本地绝对路径
 * @param  string $remote_rel 远端相对路径(例 i/2610/10/x.webp)
 * @return array ['ok'=>bool,'error'=>string]
 */
function wd_upload_file($local_file, $remote_rel){
	if (!is_file($local_file)) {
		return ['ok'=>false,'error'=>'本地文件不存在'];
	}
	$c=wd_conf();
	$remote_rel=str_replace('\\','/',$remote_rel);
	$dir=dirname($remote_rel);
	if (!wd_ensure_dir($dir)) {
		return ['ok'=>false,'error'=>'创建远端目录失败'];
	}
	$ext=strtolower(pathinfo($remote_rel,PATHINFO_EXTENSION));
	$mime_map=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','bmp'=>'image/bmp'];
	$mime=isset($mime_map[$ext])?$mime_map[$ext]:'application/octet-stream';
	$r=wd_request('PUT', $c['url'].'/'.ltrim($remote_rel,'/'), file_get_contents($local_file), ['Content-Type: '.$mime]);
	if ($r['err']!=='') {
		return ['ok'=>false,'error'=>'上传失败: '.mb_substr($r['err'],0,100)];
	}
	if (!in_array($r['code'],[200,201,204],true)) {
		return ['ok'=>false,'error'=>'上传失败: HTTP '.$r['code']];
	}
	return ['ok'=>true,'error'=>''];
}

/**
 * 删除远端文件
 */
function wd_delete_remote($remote_rel){
	$c=wd_conf();
	$r=wd_request('DELETE', $c['url'].'/'.ltrim(str_replace('\\','/',$remote_rel),'/'));
	return in_array($r['code'],[200,204,404],true);
}

/**
 * 执行一轮同步(先删墓碑,再按配额上传)
 * @return array ['ok'=>bool,'uploaded'=>int,'deleted'=>int,'failed'=>int,'error'=>string]
 */
function wd_sync_run(){
	global $db;
	if (!wd_ready()) {
		return ['ok'=>false,'uploaded'=>0,'deleted'=>0,'failed'=>0,'error'=>'WebDAV 未启用或地址为空'];
	}
	$c=wd_conf();
	$meta=wd_meta();
	$meta['last_run']=date('Y-m-d H:i:s');
	$meta['last_error']='';
	$uploaded=0;
	$deleted=0;
	$failed=0;

	//1.删除墓碑
	if ($c['delete_remote']) {
		$rows=$db->select('imginfo',['id','path'],['see'=>0,'wd_path[!]'=>'','LIMIT'=>$c['batch']]);
		foreach ($rows as $t) {
			if (wd_delete_remote($t['path'])) {
				$db->update('imginfo',['wd_path'=>''],['id'=>$t['id']]);
				$deleted++;
			}else{
				$failed++;
			}
		}
	}else{
		$db->update('imginfo',['wd_path'=>''],['see'=>0,'wd_path[!]'=>'']);
	}

	//2.上传未同步
	if ($uploaded<$c['batch']) {
		$pend=$db->select('imginfo',['id','path'],['see'=>1,'wd_path'=>'','LIMIT'=>($c['batch']-$uploaded)]);
		foreach ($pend as $p) {
			$rel=str_replace('\\','/',url_path($p['path']));
			if (strpos($rel,'i/')!==0) {
				continue;
			}
			$up=wd_upload_file(ROOT.$rel,$rel);
			if ($up['ok']) {
				$db->update('imginfo',['wd_path'=>$rel],['id'=>$p['id']]);
				$uploaded++;
			}else{
				$failed++;
				if ($meta['last_error']==='') {
					$meta['last_error']=$up['error'];
				}
			}
		}
	}

	$meta['uploaded_total']=(int)$meta['uploaded_total']+$uploaded;
	$meta['deleted_total']=(int)$meta['deleted_total']+$deleted;
	$meta['failed_total']=(int)$meta['failed_total']+$failed;
	wd_meta($meta);
	return ['ok'=>true,'uploaded'=>$uploaded,'deleted'=>$deleted,'failed'=>$failed,'error'=>$meta['last_error']];
}

/**
 * WebDAV 统计
 * @return array
 */
function wd_stats(){
	global $db;
	$synced=0;
	$synced_size=0;
	$pending=0;
	try {
		$synced=(int)$db->count('imginfo',['wd_path[!]'=>'','see'=>1]);
		$synced_size=(int)$db->sum('imginfo','size',['wd_path[!]'=>'','see'=>1]);
		$pending=(int)$db->count('imginfo',['wd_path'=>'','see'=>1]);
	} catch (Exception $e) {}
	$meta=wd_meta();
	return [
		'enabled'=>wd_ready(),
		'synced'=>$synced,
		'synced_size'=>$synced_size,
		'pending'=>$pending,
		'last_run'=>$meta['last_run'],
		'last_error'=>$meta['last_error'],
		'uploaded_total'=>(int)$meta['uploaded_total'],
		'deleted_total'=>(int)$meta['deleted_total'],
	];
}
