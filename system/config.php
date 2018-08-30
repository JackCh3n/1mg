<?php
ini_set('display_errors', 'on'); 
error_reporting(E_ALL | E_STRICT);
// ini_set ('memory_limit', '512M');//执行内存
define('SYSTEM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);//当前文件目录
define('ROOT', dirname(SYSTEM_ROOT).DIRECTORY_SEPARATOR);//上级目录
date_default_timezone_set('Asia/Shanghai');
session_start();
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
require ROOT.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
//实例化数据库
use Medoo\Medoo;//数据库
$db = new medoo([
	'database_type' => $config['db']['database_type'],
	'database_name' => $config['db']['database_name'],
	'server' => $config['db']['server'],
	'username' =>$config['db']['username'],
	'password' => $config['db']['password'],
	'charset' => $config['db']['charset'],
	'port' => $config['db']['port'],
]);