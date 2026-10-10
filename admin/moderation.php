<?php
/**
 * 内容审核: 待鉴定图片人工审核 + 一键自动检测
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();
require_once SYSTEM_ROOT.'moderate.php';

//鉴黄Key可用性测试:用公开测试图调用一次接口,看Key是否被接受
if (isset($_GET['type']) && $_GET['type']==='testkey' && $_SERVER['REQUEST_METHOD']==='POST') {
	header('Content-Type: application/json; charset=utf-8');
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		json_exit(['ok'=>false,'msg'=>'页面已过期,请刷新后重试']);
	}
	$key=preg_replace('/[^A-Za-z0-9_-]/','', (string)$_POST['key']);
	if ($key==='') {
		json_exit(['ok'=>false,'msg'=>'请先填写鉴黄 Key']);
	}
	//官方文档自带的示例图,moderatecontent自家的服务器一定能拉取到
	$test_img='http://www.moderatecontent.com/img/logo.png';
	$apiurl='https://www.moderatecontent.com/api/v2?key='.urlencode($key).'&url='.urlencode($test_img);
	$curl=curl_init($apiurl);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 12);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	$resp=curl_exec($curl);
	$err=curl_error($curl);
	curl_close($curl);
	$data=json_decode((string)$resp,true);
	if ($resp===false || $err) {
		json_exit(['ok'=>false,'msg'=>'接口连接失败: '.mb_substr($err,0,80)]);
	}
	if (!is_array($data)) {
		json_exit(['ok'=>false,'msg'=>'接口返回异常,请稍后再试']);
	}
	if (isset($data['error_code']) && $data['error_code']==0) {
		json_exit(['ok'=>true,'msg'=>'Key 有效,接口调用正常(识别等级: '.(isset($data['rating_letter'])?$data['rating_letter']:$data['rating_index']).')']);
	}
	if (isset($data['error_code']) && $data['error_code']==1001) {
		//Key被接受但测试图拉取失败,同样说明Key可用
		json_exit(['ok'=>true,'msg'=>'Key 有效(测试图片获取失败,不影响实际使用)']);
	}
	json_exit(['ok'=>false,'msg'=>'Key 无效或接口异常'.(isset($data['error_message'])?': '.$data['error_message']:'')]);
}

$msg='';
//IP黑名单管理(封禁/解封)
if (isset($_GET['type']) && in_array($_GET['type'],['ban_ip','unban_ip']) && $_SERVER['REQUEST_METHOD']==='POST') {
	if (csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$ip=trim((string)$_POST['ip']);
		if (filter_var($ip, FILTER_VALIDATE_IP)) {
			if ($_GET['type']==='ban_ip') {
				$db->pdo->prepare("INSERT OR REPLACE INTO ban_ip (ip,reason,date) VALUES (?,?,?)")->execute([$ip, mb_substr((string)($_POST['reason'] ?? ''),0,100), date('Y-m-d H:i:s')]);
				admin_log('ip_ban',$ip);
			} else {
				$db->delete('ban_ip',['ip'=>$ip]);
				admin_log('ip_unban',$ip);
			}
		}
	}
	header('Location: '.($_SERVER['HTTP_REFERER'] ?? 'moderation.php'));
	exit();
}
//API令牌管理(增/启停/删),从设置页提交后回跳
if (isset($_GET['type']) && strpos($_GET['type'],'token_')===0 && isset($_POST['_csrf']) && $_SERVER['REQUEST_METHOD']==='POST') {
	if (csrf_verify($_POST['_csrf'])) {
		$t=$_GET['type'];
		if ($t==='token_add') {
			$name=mb_substr(trim((string)$_POST['name']),0,32);
			$tok=bin2hex(random_bytes(16));
			$db->insert('api_tokens',['name'=>$name?:'未命名','token'=>$tok,'enabled'=>1,'uses'=>0,'created'=>date('Y-m-d H:i:s')]);
			admin_log('token_add',$name);
		}elseif ($t==='token_toggle') {
			$id=(int)$_POST['id'];
			$row=$db->get('api_tokens',['enabled'],['id'=>$id]);
			if ($row) { $db->update('api_tokens',['enabled'=>$row['enabled']?0:1],['id'=>$id]); admin_log('token_toggle','id='.$id); }
		}elseif ($t==='token_del') {
			$id=(int)$_POST['id'];
			$db->delete('api_tokens',['id'=>$id]);
			admin_log('token_del','id='.$id);
		}
	}
	header('Location: seting.php');
	exit();
}

//鉴黄Key池管理(增/启停/删),从设置页提交后回跳
if (isset($_GET['type']) && isset($_POST['_csrf']) && $_SERVER['REQUEST_METHOD']==='POST') {
	$t=$_GET['type'];
	if (!csrf_verify($_POST['_csrf'])) {
		header('Location: ../admin/seting.php');
		exit();
	}
	require_once SYSTEM_ROOT.'moderate.php';
	if ($t==='key_add') {
		$k=preg_replace('/[^A-Za-z0-9_-]/','', (string)$_POST['key']);
		if (strlen($k)>=16) {
			try {
				$db->insert('mod_keys',['key'=>$k,'enabled'=>1,'status'=>'ok','used_month'=>date('Y-m'),'used_count'=>0]);
				admin_log('modkey_add',substr($k,0,8).'…');
			} catch (Exception $e) { /*重复Key忽略*/ }
		}
	}elseif ($t==='key_toggle') {
		$id=(int)$_POST['id'];
		$row=$db->get('mod_keys',['id','enabled'],['id'=>$id]);
		if ($row) {
			$db->update('mod_keys',['enabled'=>$row['enabled']?0:1],['id'=>$id]);
			admin_log('modkey_toggle','id='.$id.',enabled='.($row['enabled']?0:1));
		}
	}elseif ($t==='key_del') {
		$id=(int)$_POST['id'];
		$row=$db->get('mod_keys',['key'],['id'=>$id]);
		$db->delete('mod_keys',['id'=>$id]);
		admin_log('modkey_del',$row?substr($row['key'],0,8).'…':'id='.$id);
	}
	header('Location: seting.php');
	exit();
}

//人工审核操作
if (!empty($_POST['do']) && !empty($_POST['key'])) {
	if (csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$key=(int)$_POST['key'];
		$row=$db->get('imginfo',['id','path'],['id'=>$key,'see'=>1]);
		if (!empty($row)) {
			if ($_POST['do']==='adult') {
				trash_put($row['path']);
				$db->update('imginfo',['see'=>0],['id'=>$key]);
				admin_log('moderation_remove', $row['path']);
				$msg='已删除违规图片';
			}elseif ($_POST['do']==='ok') {
				$db->update('imginfo',['level'=>1],['id'=>$key]);
				admin_log('moderation_ok', $row['path']);
				$msg='已标记为正常';
			}
		}
	}else{
		$msg='页面已过期,请重试';
	}
}

//一键自动检测
if (!empty($_GET['action']) && $_GET['action']=='auto' && isset($_GET['token'])) {
	if (hash_equals(csrf_token(),(string)$_GET['token'])) {
		$stat=moderate_run(12);
		admin_log('moderation_auto', 'ok='.$stat['ok'].',adult='.$stat['adult'].',skip='.$stat['skip']);
		$msg='自动检测完成: 正常 '.$stat['ok'].' 张,违规删除 '.$stat['adult'].' 张,跳过 '.$stat['skip'].' 张';
	}else{
		$msg='非法请求';
	}
}

$pending=$db->select('imginfo',['id','path','date'],['level'=>0,'see'=>1,'ORDER'=>['id'=>'DESC'],'LIMIT'=>60]);
$pending_count=$db->count('imginfo',['level'=>0,'see'=>1]);
$checked_count=$db->count('imginfo',['level[>]'=>0]);

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','moderation');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('csrf',csrf_token());
$smarty->assign('msg',$msg);
$smarty->assign('ban_list',$db->select('ban_ip',['ip','reason','date'],['ORDER'=>['date'=>'DESC'],'LIMIT'=>100]));
$smarty->assign('pending',$pending);
$smarty->assign('pending_count',$pending_count);
$smarty->assign('checked_count',$checked_count);
$smarty->display('tpl_moderation.php');
