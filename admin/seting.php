<?php
/**
 * 网站设置
 * 保存到 system/config.user.json(不写PHP文件,避免代码注入)
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

$save_error='';
$save_ok='';
if (!empty($_POST)) {
	//所有落盘的内容必须是合法UTF-8
	foreach ($_POST as $pv) {
		if (is_string($pv) && !mb_check_encoding($pv,'UTF-8')) {
			$save_error='输入内容存在非法字符';
			break;
		}
	}
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$save_error='页面已过期,请重新提交';
	}else{
		$action=isset($_POST['action'])?$_POST['action']:'';

		//网站基本设置
		if ($action==='site') {
			$title=trim(strip_tags((string)$_POST['title']));
			$server=trim((string)$_POST['server']);
			$cdn=trim((string)$_POST['cdn']);
			$key=trim(strip_tags((string)$_POST['key']));
			$pass=trim(strip_tags((string)$_POST['pass']));
			$rate_hour=(int)$_POST['rate_hour'];
			$max_size_mb=(int)$_POST['max_size_mb'];
			$max_files=(int)$_POST['max_files'];
			$github_url=trim((string)$_POST['github_url']);
			$show_github=isset($_POST['show_github'])?1:0;
			$since_year=(int)$_POST['since_year'];
			$user_quota_mb=(int)$_POST['user_quota_mb'];
			$user_daily_files=(int)$_POST['user_daily_files'];
			$webp=isset($_POST['webp_enabled'])?1:0;
			$api_token=preg_replace('/[^A-Za-z0-9_-]/','', (string)$_POST['api_token']);
			foreach ([[$title,'网站标题',1,50],[$key,'图片鉴黄Key',0,64],[$pass,'图片鉴黄口令',0,64]] as $v) {
				if (mb_strlen($v[0])<$v[2] || mb_strlen($v[0])>$v[3]) {
					$save_error=$v[1].'长度不合法';
					break;
				}
			}
			if ($save_error==='' && ($rate_hour<0 || $rate_hour>10000)) {
				$save_error='每小时上传上限需在0-10000之间(0=不限制)';
			}
			if ($save_error==='' && ($max_size_mb<1 || $max_size_mb>50)) {
				$save_error='单张大小上限需在1-50MB之间';
			}
			if ($save_error==='' && ($max_files<1 || $max_files>100)) {
				$save_error='单次上传张数需在1-100之间';
			}
			if ($save_error==='' && $github_url!=='' && (!filter_var($github_url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i',$github_url))) {
				$save_error='开源地址格式不正确';
			}
			if ($save_error==='' && ($since_year<1970 || $since_year>(int)date('Y'))) {
				$save_error='起始年份需在1970到当前年份之间';
			}
			if ($save_error==='' && ($user_quota_mb<0 || $user_quota_mb>1048576)) {
				$save_error='用户存储配额需在0-1048576MB之间(0=不限)';
			}
			if ($save_error==='' && ($user_daily_files<0 || $user_daily_files>10000)) {
				$save_error='用户每日张数需在0-10000之间(0=不限)';
			}
			if ($save_error==='' && mb_strlen($api_token)>64) {
				$save_error='接口令牌过长';
			}
			if ($save_error==='') {
				foreach ([['server',$server],['cdn',$cdn]] as $v) {
					if (!filter_var($v[1], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i',$v[1])) {
						$save_error=$v[0]==='server'?'网址格式不正确':'cdn域名格式不正确';
						break;
					}
				}
			}
			if ($save_error==='') {
				//cdn统一以/结尾,server统一不带尾/
				$server=rtrim($server,'/');
				$cdn=rtrim($cdn,'/').'/';
				if (save_user_config(['web'=>[
					'title'=>$title,
					'server'=>$server,
					'cdn'=>$cdn,
					'img_level_key'=>$key,
					'img_level_pass'=>$pass,
					'rate_hour'=>$rate_hour,
					'max_size_mb'=>$max_size_mb,
					'max_files'=>$max_files,
					'github_url'=>$github_url,
					'show_github'=>$show_github,
					'since_year'=>$since_year,
					'user_quota_mb'=>$user_quota_mb,
					'user_daily_files'=>$user_daily_files,
					'webp_enabled'=>$webp,
					'api_token'=>$api_token,
				]])) {
					admin_log('settings_site','rate_hour='.$rate_hour.',webp='.$webp.',api_auth='.($api_token!==''?'on':'off'));
					header('Location: seting.php?msg=site');
					exit();
				}
				$save_error='配置文件写入失败,请检查 system 目录权限';
			}
		}

		//数据保留策略
		if ($action==='policy') {
			$online=(int)$_POST['retention_online'];
			$archive=(int)$_POST['retention_archive'];
			$trash_days=(int)$_POST['trash_days'];
			if ($online<1 || $online>365) {
				$save_error='在线保留天数需在1-365之间';
			}elseif ($archive<7 || $archive>3650) {
				$save_error='归档保留天数需在7-3650之间';
			}elseif ($trash_days<1 || $trash_days>3650) {
				$save_error='回收站保留天数需在1-3650之间';
			}elseif ($archive<=$online) {
				$save_error='归档保留天数必须大于在线保留天数';
			}else{
				if (save_user_config(['web'=>['retention_online'=>$online,'retention_archive'=>$archive,'trash_days'=>$trash_days]])) {
					admin_log('settings_policy','online='.$online.'d,archive='.$archive.'d');
					header('Location: seting.php?msg=policy');
					exit();
				}
				$save_error='配置文件写入失败,请检查 system 目录权限';
			}
		}

		//界面皮肤
		if ($action==='skin') {
			if (!empty($_POST['reset'])) {
				//恢复默认外观
				if (save_user_config(['web'=>['default_skin'=>'light','accent'=>'']])) {
					admin_log('settings_skin_reset','');
					header('Location: seting.php?msg=skin');
					exit();
				}
				$save_error='配置文件写入失败,请检查 system 目录权限';
			}else{
				$skin=in_array($_POST['default_skin'],['light','dark'])?$_POST['default_skin']:'light';
				$accent=strtolower(trim((string)$_POST['accent']));
				if ($accent!=='' && !preg_match('/^#[0-9a-f]{6}$/',$accent)) {
					$save_error='强调色格式不正确(#RRGGBB)';
				}else{
					if (save_user_config(['web'=>['default_skin'=>$skin,'accent'=>$accent]])) {
						admin_log('settings_skin','skin='.$skin.',accent='.$accent);
						header('Location: seting.php?msg=skin');
						exit();
					}
					$save_error='配置文件写入失败,请检查 system 目录权限';
				}
			}
		}

		//修改管理员密码
		if ($action==='password') {
			$oldpass=(string)$_POST['oldpass'];
			$newpass=(string)$_POST['newpass'];
			$newpass2=(string)$_POST['newpass2'];
			if (strlen($newpass)<6 || strlen($newpass)>64) {
				$save_error='新密码长度需在6到64位之间';
			}elseif ($newpass!==$newpass2) {
				$save_error='两次输入的新密码不一致';
			}elseif (!admin_change_password($oldpass,$newpass)) {
				$save_error='原密码错误或修改失败';
			}else{
				admin_log('password_change','');
				header('Location: seting.php?msg=password');
				exit();
			}
		}
	}
}

//两步验证操作
$otp_msg='';
$otp_error='';
$otp_setup=isset($_SESSION['otp_setup_secret'])?$_SESSION['otp_setup_secret']:'';
if (!empty($_POST['action']) && $_POST['action']==='otp') {
	if (!csrf_verify(isset($_POST['_csrf'])?$_POST['_csrf']:'')) {
		$otp_error='页面已过期,请重新提交';
	}else{
		$otp_action=isset($_POST['otp_action'])?$_POST['otp_action']:'';
		$code=preg_replace('/\D/','', (string)$_POST['code']);
		if ($otp_action==='begin') {
			//生成新密钥待确认
			require_once SYSTEM_ROOT.'totp.php';
			$_SESSION['otp_setup_secret']=totp_generate_secret();
			header('Location: seting.php');
			exit();
		}elseif ($otp_action==='backup') {
			if (empty($_SESSION['admin_id'])) {
				$otp_error='请先登录';
			}else{
				$_SESSION['otp_backup_show']=otp_backup_generate();
				admin_log('otp_backup_regen','');
				header('Location: seting.php?msg=otp_backup');
				exit();
			}
		}elseif ($otp_action==='cancel') {
			unset($_SESSION['otp_setup_secret']);
			header('Location: seting.php');
			exit();
		}elseif ($otp_action==='enable') {
			require_once SYSTEM_ROOT.'totp.php';
			if ($otp_setup==='' ) {
				$otp_error='请先生成密钥';
			}elseif (!totp_verify($otp_setup, $code)) {
				$otp_error='验证码不正确,请重试';
			}else{
				$row=$db->get('admin',['id'],['id'=>$_SESSION['admin_id']]);
				$db->update('admin',[
					'otp_secret'=>encdec_openssl($otp_setup),
					'otp_enabled'=>1,
				],['id'=>$_SESSION['admin_id']]);
				unset($_SESSION['otp_setup_secret']);
				//绑定成功即生成一份备份码,在页面上显示一次
				$_SESSION['otp_backup_show']=otp_backup_generate();
				admin_log('otp_enable','');
				header('Location: seting.php?msg=otp_on');
				exit();
			}
		}elseif ($otp_action==='disable') {
			require_once SYSTEM_ROOT.'totp.php';
			$secret=admin_get_otp_secret();
			if ($secret==='' || !totp_verify($secret,$code)) {
				$otp_error='验证码不正确,无法解除绑定';
			}else{
				$db->update('admin',['otp_secret'=>'','otp_enabled'=>0],['id'=>$_SESSION['admin_id']]);
				admin_log('otp_disable','');
				header('Location: seting.php?msg=otp_off');
				exit();
			}
		}
	}
}

//存储信息
require_once SYSTEM_ROOT.'moderate.php';
$mod_keys=mod_keys_all();
$db_file=ROOT.ltrim($config['db']['database_file'],'/');
$storage=[
	'db_file'=>(is_file($db_file)?$db_file:'尚未创建'),
	'db_size'=>is_file($db_file)?format_size(filesize($db_file)):'0',
	'archive_dir'=>DATA_DIR.'archive',
	'archive_count'=>0,
	'archive_size'=>0,
];
if (is_dir($storage['archive_dir'])) {
	foreach (glob($storage['archive_dir'].'/*.csv.gz') as $af) {
		$storage['archive_count']++;
		$storage['archive_size']+=filesize($af);
	}
}
$storage['archive_size_txt']=format_size($storage['archive_size']);

$msg=isset($_GET['msg'])?$_GET['msg']:'';
$messages=[
	'site'=>'网站设置已保存',
	'policy'=>'保留策略已保存',
	'skin'=>'外观设置已保存',
	'password'=>'密码修改成功',
	'otp_on'=>'两步验证已开启',
	'otp_off'=>'两步验证已关闭',
	'otp_backup'=>'备份码已重新生成(旧码已失效),请立即抄写保存',
];

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','seting');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('config',$config);
$smarty->assign('csrf',csrf_token());
$smarty->assign('save_error',$save_error);
$smarty->assign('save_ok',isset($messages[$msg])?$messages[$msg]:'');
$smarty->assign('otp_enabled',(int)admin_get_otp_enabled());
$smarty->assign('otp_setup',$otp_setup);
$smarty->assign('otp_error',$otp_error);
if ($otp_setup!=='') {
	require_once SYSTEM_ROOT.'totp.php';
	$smarty->assign('otp_uri',totp_uri($otp_setup,$_SESSION['admin_user'],$config['web']['title']));
}
$smarty->assign('storage',$storage);
$smarty->assign('trash',trash_stat());
$smarty->assign('trash_days',(int)($config['web']['trash_days'] ?? 30));
$smarty->assign('otp_backup_left',otp_backup_left());
$smarty->assign('otp_backup_show',isset($_SESSION['otp_backup_show'])?$_SESSION['otp_backup_show']:null);
unset($_SESSION['otp_backup_show']);
$smarty->assign('mod_keys',$mod_keys);
$smarty->assign('api_tokens',$db->select('api_tokens',['id','name','token','enabled','uses','last_used','created'],['ORDER'=>['id'=>'ASC']]));
$smarty->assign('mod_limit',MOD_MONTHLY_LIMIT);
$smarty->assign('day_limit',MOD_DAILY_LIMIT);
$smarty->display('tpl_seting.php');
