<?php
/**
 * 网站设置
 * 保存到 system/config.user.json(不写PHP文件,避免代码注入),改数据库配置前会先测试连接
 */
require '../system/config.php';
require SYSTEM_ROOT.'auth.php';
admin_require_login();

$save_error='';
$save_ok='';
if (!empty($_POST)) {
	//所有入库/落盘的内容必须是合法UTF-8
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
			foreach ([$title,$server,$cdn,$key,$pass] as $v) {
				//配置要以JSON落盘,非法UTF-8一律拒绝
				if (!mb_check_encoding($v,'UTF-8')) {
					$save_error='输入内容存在非法字符';
					break;
				}
			}
			foreach ([[$title,'网站标题',1,50],[$key,'图片鉴黄Key',0,64],[$pass,'图片鉴黄口令',0,64]] as $v) {
				if (mb_strlen($v[0])<$v[2] || mb_strlen($v[0])>$v[3]) {
					$save_error=$v[1].'长度不合法';
					break;
				}
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
				]])) {
					header('Location: seting.php?msg=site');
					exit();
				}
				$save_error='配置文件写入失败,请检查 system 目录权限';
			}
		}

		//数据库设置(先测试连接,成功才保存)
		if ($action==='db') {
			$db_server=trim((string)$_POST['db_server']);
			$db_port=(int)$_POST['db_port'];
			$db_user=trim((string)$_POST['db_user']);
			$db_pass=(string)$_POST['db_pass'];
			$db_name=trim((string)$_POST['db_name']);
			$db_charset=in_array($_POST['db_charset'],['utf8','utf8mb4','gbk','latin1'])?$_POST['db_charset']:'utf8';
			if (!preg_match('/^[A-Za-z0-9_.-]{1,100}$/',$db_server)) {
				$save_error='数据库地址格式不正确';
			}elseif ($db_port<1 || $db_port>65535) {
				$save_error='数据库端口不正确';
			}elseif (!preg_match('/^[A-Za-z0-9_.-]{1,32}$/',$db_user)) {
				$save_error='数据库用户名格式不正确';
			}elseif (mb_strlen($db_pass)>64) {
				$save_error='数据库密码过长';
			}elseif (!preg_match('/^[A-Za-z0-9_]{1,64}$/',$db_name)) {
				$save_error='数据库名格式不正确';
			}
			if ($save_error==='') {
				try {
					$test=new PDO(
						"mysql:host={$db_server};port={$db_port};dbname={$db_name};charset={$db_charset}",
						$db_user,
						$db_pass,
						[PDO::ATTR_TIMEOUT=>5]
					);
					$test=null;
					if (save_user_config(['db'=>[
						'server'=>$db_server,
						'port'=>$db_port,
						'username'=>$db_user,
						'password'=>$db_pass,
						'database_name'=>$db_name,
						'charset'=>$db_charset,
						'database_type'=>'mysql',
					]])) {
						header('Location: seting.php?msg=db');
						exit();
					}
					$save_error='配置文件写入失败,请检查 system 目录权限';
				} catch (PDOException $e) {
					$save_error='数据库连接失败,设置未保存,请检查填写内容';
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
				header('Location: seting.php?msg=password');
				exit();
			}
		}
	}
}

$msg=isset($_GET['msg'])?$_GET['msg']:'';
$messages=['site'=>'网站设置已保存','db'=>'数据库设置已保存','password'=>'密码修改成功'];

$smarty = admin_smarty();
$smarty->assign('title',$config['web']['title']);
$smarty->assign('nav_active','seting');
$smarty->assign('admin_user',$_SESSION['admin_user']);
$smarty->assign('config',$config);
$smarty->assign('csrf',csrf_token());
$smarty->assign('save_error',$save_error);
$smarty->assign('save_ok',isset($messages[$msg])?$messages[$msg]:'');
$smarty->display('tpl_seting.php');
