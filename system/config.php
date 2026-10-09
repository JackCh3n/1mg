<?php
/**
 * 全局配置
 * 数据库使用 SQLite(单文件,免安装MySQL)
 * 后台"网站设置"保存的内容会写入 system/config.user.json 并在此处覆盖默认值
 */
//生产环境关闭错误回显,只记录日志,避免泄露路径/配置等敏感信息
ini_set('display_errors', 'off');
ini_set('log_errors', 'on');
error_reporting(E_ALL);
// ini_set ('memory_limit', '512M');//执行内存
define('SYSTEM_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);//当前文件目录
define('ROOT', dirname(SYSTEM_ROOT).DIRECTORY_SEPARATOR);//上级目录
define('DATA_DIR', ROOT.'data'.DIRECTORY_SEPARATOR);//数据目录(数据库/归档)
date_default_timezone_set('Asia/Shanghai');
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
/*配置(SQLite单文件模式)*/
$config=[
	'db' =>[
		'database_type'=>'sqlite',
		//数据库文件位置(相对站点根)
		'database_file'=>'data/1mg.sqlite',
	],
	'web'=>[
		'title'=>'1mg',
		'server'=>'http://tu.x0ox.xyz',
		'cdn'=>'http://tu.x0ox.xyz/',
		'img_level_key'=>'b297a39d17b1ad0fc10d0f8696e07ca5',
		'img_level_pass'=>'nobige.cn',
		//界面皮肤
		'default_skin'=>'light', //light / dark
		'accent'=>'',            //自定义强调色(#10b981为默认,空=默认)
		//数据保留策略
		'retention_online'=>7,   //在线保留天数(主库)
		'retention_archive'=>180,//归档压缩包保留天数
		//上传与接口
		'rate_hour'=>60,         //每IP每小时上传上限(0=不限制)
		'webp_enabled'=>1,       //上传时转存WebP(更小,兼容性2026年已普及)
		'api_token'=>'',         //非空时上传/预检接口要求携带此令牌(空=开放)
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
//sqlite数据库文件统一解析为绝对路径(不受运行目录影响)
if ($config['db']['database_type']==='sqlite') {
	$__dbf=(string)$config['db']['database_file'];
	if ($__dbf!=='' && $__dbf[0]!=='/' && !preg_match('#^[a-zA-Z]:#',$__dbf)) {
		$config['db']['database_file']=ROOT.$__dbf;
	}
	unset($__dbf);
}
require ROOT.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
//公共函数库全局加载一次(其余文件一律require_once)
require_once SYSTEM_ROOT.'function.php';
//实例化数据库
use Medoo\Medoo;//数据库
$db = new Medoo($config['db']);

/*SQLite自动初始化:首次运行自动建表/建索引/播种默认管理员*/
db_init_sqlite($db);
