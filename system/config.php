<?php
/**
 * 全局配置
 * 后台"网站设置"保存的内容会写入 system/config.user.json 并在此处覆盖默认值
 */
//生产环境关闭错误回显,只记录日志,避免泄露路径/配置等敏感信息
ini_set('display_errors', 'off');
ini_set('log_errors', 'on');
error_reporting(E_ALL);
// ini_set ('memory_limit', '512M');//执行内存
define('SYSTEM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);//当前文件目录
define('ROOT', dirname(SYSTEM_ROOT).DIRECTORY_SEPARATOR);//上级目录
date_default_timezone_set('Asia/Shanghai');
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
/*数据库配置*/
$config=[
	'db' =>[
		'server' => '127.0.0.1', //数据库服务器
		'port' => 3306, //数据库端口
		'username' => 'root', //数据库用户名
		'password' => 'root', //数据库密码
		'database_name' => 'tu', //数据库名
		'charset'=>'utf8',
		'database_type'=>'mysql'

	],
	'web'=>[
		'title'=>'1mg',
		'server'=>'http://tu.x0ox.xyz',
		'cdn'=>'http://tu.x0ox.xyz/',
		'img_level_key'=>'b297a39d17b1ad0fc10d0f8696e07ca5',
		'img_level_pass'=>'nobige.cn',
	]
];
/*后台保存过的设置(存于json,不用拼接php代码,避免写入代码注入)*/
$user_config_file=SYSTEM_ROOT.'config.user.json';
if (is_file($user_config_file)) {
	$user_config=json_decode((string)file_get_contents($user_config_file),true);
	if (is_array($user_config)) {
		foreach (['db','web'] as $_sec) {
			if (isset($user_config[$_sec]) && is_array($user_config[$_sec])) {
				$config[$_sec]=array_merge($config[$_sec],$user_config[$_sec]);
			}
		}
	}
	unset($user_config);
}
unset($user_config_file);
require ROOT.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
//公共函数库全局加载一次(其余文件一律require_once)
require_once SYSTEM_ROOT.'function.php';
//实例化数据库(直接传入db配置段,后台自定义的键如database_file也能透传)
use Medoo\Medoo;//数据库
$db = new Medoo($config['db']);
