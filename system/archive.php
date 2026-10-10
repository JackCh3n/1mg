<?php
/**
 * 数据三级保留:
 *   1.在线(主库)   默认保留 7  天,超期记录归档
 *   2.归档备份     data/archive/yyyymmdd.csv.gz,默认保留 180 天
 *   3.到期清理     超过保留期的归档压缩包直接删除
 * 归档只搬数据库记录,图片文件本身不受影响(不破坏外链)
 */

/**
 * 归档目录与元数据文件路径
 * @return array
 */
function archive_paths(){
	$dir=DATA_DIR.'archive';
	if (!is_dir($dir)) {
		@mkdir($dir,0755,true);
	}
	return [$dir, $dir.'/meta.json'];
}

/**
 * 读取归档元数据
 * @return array
 */
function archive_meta(){
	list($dir,$meta_file)=archive_paths();
	if (is_file($meta_file)) {
		$meta=json_decode((string)file_get_contents($meta_file),true);
		if (is_array($meta)) {
			return $meta;
		}
	}
	return ['last_run'=>'','archived_total'=>0,'files'=>[]];
}

/**
 * 保存归档元数据
 * @param array $meta
 */
function archive_meta_save($meta){
	list($dir,$meta_file)=archive_paths();
	file_put_contents($meta_file, json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * 执行一轮归档+清理
 * @param  int $online_days  在线保留天数
 * @param  int $archive_days 归档保留天数
 * @return array ['archived'=>本次归档行数,'purged_files'=>清理文件数,'purged_rows'=>清理行数]
 */
function archive_run($online_days, $archive_days){
	global $db;
	list($dir,$meta_file)=archive_paths();
	$meta=archive_meta();

	$cutoff=date('Y-m-d 00:00:00', strtotime('-'.(int)$online_days.' days'));
	$rows=$db->select('imginfo',
		['id','path','ip','ua','date','dir','compress','level','see','md5','name','size'],
		['date[<]'=>$cutoff,'ORDER'=>['date'=>'ASC']]);

	$archived=0;
	if (!empty($rows)) {
		//按天分组
		$by_day=[];
		foreach ($rows as $r) {
			$day=substr((string)$r['date'],0,10);
			$stamp=strtotime($day);
			if ($stamp===false) {
				continue;
			}
			$by_day[date('Ymd',$stamp)][]=$r;
		}
		foreach ($by_day as $yyyymmdd=>$day_rows) {
			$file=$dir.'/'.$yyyymmdd.'.csv.gz';
			$existing='';
			if (is_file($file)) {
				$existing=(string)file_get_contents('compress.zlib://'.$file);
			}
			//生成csv片段
			$tmp=fopen('php://temp','r+');
			foreach ($day_rows as $r) {
				fputcsv($tmp, [$r['id'],$r['path'],$r['ip'],$r['ua'],$r['date'],$r['dir'],$r['compress'],$r['level'],$r['see'],$r['md5'],$r['name'],$r['size']]);
			}
			rewind($tmp);
			$csv=stream_get_contents($tmp);
			fclose($tmp);
			//原子写入
			$gz=gzencode($existing.$csv, 9);
			$tmp_file=$file.'.tmp';
			file_put_contents($tmp_file,$gz,LOCK_EX);
			rename($tmp_file,$file);
			$archived+=count($day_rows);
			$meta['files'][$yyyymmdd]=(isset($meta['files'][$yyyymmdd])?(int)$meta['files'][$yyyymmdd]:0)+count($day_rows);
		}
		//删除已归档记录
		$db->delete('imginfo',['date[<]'=>$cutoff]);
	}

	//清理超过保留期的归档文件
	$purge_before=date('Ymd', strtotime('-'.(int)$archive_days.' days'));
	$purged_files=0;
	$purged_rows=0;
	foreach (glob($dir.'/*.csv.gz') as $file) {
		$base=basename($file,'.csv.gz');
		if (!preg_match('/^\d{8}$/',$base) || $base>=$purge_before) {
			continue;
		}
		//统计将被清理的行数
		$lines=(string)file_get_contents('compress.zlib://'.$file);
		$purged_rows+=max(0, substr_count($lines,"\n")-0);
		@unlink($file);
		if (isset($meta['files'][$base])) {
			unset($meta['files'][$base]);
		}
		$purged_files++;
	}

	//处理设置了有效期的图片:到期移入回收站(可在回收站窗口内恢复)
	$expired=$db->select('imginfo',['id','path'],['see'=>1,'expire_at[!]'=>'','expire_at[<]'=>date('Y-m-d H:i:s'),'LIMIT'=>200]);
	foreach ($expired as $ex) {
		trash_put($ex['path']);
		$db->update('imginfo',['see'=>0],['id'=>$ex['id']]);
	}

	//顺带清理回收站中超过保留期的文件
	$trash_days=(int)($GLOBALS['config']['web']['trash_days'] ?? 30);
	trash_purge($trash_days);

	$meta['last_run']=date('Y-m-d H:i:s');
	$meta['archived_total']=(int)$meta['archived_total']+$archived-$purged_rows;
	if ($meta['archived_total']<0) {
		$meta['archived_total']=0;
	}
	archive_meta_save($meta);

	return ['archived'=>$archived,'purged_files'=>$purged_files,'purged_rows'=>$purged_rows];
}

/**
 * 归档文件列表(新到旧)
 * @param  int $limit
 * @return array [['yyyymmdd'=>..,'rows'=>..,'size'=>..,'date'=>..],..]
 */
function archive_list($limit=60){
	list($dir,)=archive_paths();
	$meta=archive_meta();
	$out=[];
	$files=glob($dir.'/*.csv.gz');
	if ($files) {
		rsort($files);
		foreach (array_slice($files,0,$limit) as $file) {
			$base=basename($file,'.csv.gz');
			if (!preg_match('/^\d{8}$/',$base)) {
				continue;
			}
			$out[]=[
				'yyyymmdd'=>$base,
				'date'=>$base,
				'rows'=>isset($meta['files'][$base])?(int)$meta['files'][$base]:0,
				'size'=>filesize($file),
			];
		}
	}
	return $out;
}

/**
 * 读取归档文件内容(下载用),非法文件名返回null
 * @param  string $yyyymmdd
 * @return string|null
 */
function archive_read($yyyymmdd){
	if (!preg_match('/^\d{8}$/',$yyyymmdd)) {
		return null;
	}
	list($dir,)=archive_paths();
	$file=$dir.'/'.$yyyymmdd.'.csv.gz';
	if (!is_file($file)) {
		return null;
	}
	return (string)file_get_contents('compress.zlib://'.$file);
}

/**
 * 到点自动归档(超过6小时未跑就执行一次),供后台仪表盘调用
 * @param int $online_days
 * @param int $archive_days
 * @return array|false false=本次未执行
 */
function archive_auto_run($online_days, $archive_days){
	$meta=archive_meta();
	$last=strtotime((string)$meta['last_run']);
	if ($last!==false && (time()-$last)<6*3600) {
		return false;
	}
	return archive_run($online_days,$archive_days);
}
