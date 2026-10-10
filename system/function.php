<?php
/**
 * 全国随机ip
 * @return stting
 */
function rand_ip(){
          $ip_long = array(
           array('607649792', '608174079'), //36.56.0.0-36.63.255.255
           array('975044608', '977272831'), //58.30.0.0-58.63.255.255
           array('999751680', '999784447'), //59.151.0.0-59.151.127.255
           array('1019346944', '1019478015'), //60.194.0.0-60.195.255.255
           array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
           array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
           array('1947009024', '1947074559'), //116.13.0.0-116.13.255.255
           array('1987051520', '1988034559'), //118.112.0.0-118.126.255.255
           array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
           array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
           array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
           array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
           array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
           array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
           array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
          );
	   $rand_key = mt_rand(0, 9);
       $ip= long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
       // $headers['CLIENT-IP'] = $ip; 
       // $headers['X-FORWARDED-FOR'] = $ip; 

       // $headerArr = array(); 
       // foreach( $headers as $n => $v ) { 
       //     $headerArr[] = $n .':' . $v;  
       // }
       // return $headerArr;
       return $ip;
}
/**
 * 随机ua
 * @return sting
 */
function rand_ua(){
$ua=[//PC端的UserAgent  
			"safari 5.1 – MAC"=>"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",  
			"safari 5.1 – Windows"=>"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",  
			"Firefox 38esr"=>"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",  
			"IE 11"=>"Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",  
			"IE 9.0"=>"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",  
			"IE 8.0"=>"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",  
			"IE 7.0"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",  
			"IE 6.0"=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",  
			"Firefox 4.0.1 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",  
			"Firefox 4.0.1 – Windows"=>"Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",  
			"Opera 11.11 – MAC"=>"Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",  
			"Opera 11.11 – Windows"=>"Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",  
			"Chrome 17.0 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",  
			"傲游（Maxthon）"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",  
			"腾讯TT"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",  
			"世界之窗（The World） 2.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",  
			"世界之窗（The World） 3.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",  
			"360浏览器"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",  
			"搜狗浏览器 1.x"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",  
			"Avant"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",  
			"Green Browser"=>"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",  
			//移动端口  
			"safari iOS 4.33 – iPhone"=>"Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",  
			"safari iOS 4.33 – iPod Touch"=>"Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",  
			"safari iOS 4.33 – iPad"=>"Mozilla/5.0 (iPad; U; CPU OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",  
			"Android N1"=>"Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",  
			"Android QQ浏览器 For android"=>"MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",  
			"Android Opera Mobile"=>"Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10",  
			"Android Pad Moto Xoom"=>"Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13",  
			"BlackBerry"=>"Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+",  
			"WebOS HP Touchpad"=>"Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.0; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/233.70 Safari/534.6 TouchPad/1.0",  
			"UC标准"=>"NOKIA5700/ UCWEB7.0.2.37/28/999",  
			"UCOpenwave"=>"Openwave/ UCWEB7.0.2.37/28/999",  
			"UC Opera"=>"Mozilla/4.0 (compatible; MSIE 6.0; ) Opera/UCWEB7.0.2.37/28/999",  
			"微信内置浏览器"=>"Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN",  
		  
		]; 
	return $ua[array_rand($ua)];
}

function file_put_config($dir,$file_name,$array){
	echo "string";
}
function bbr(){
	echo(PHP_EOL);
}
/**
 * openssl加密与解密
 * @param  string $str 要加密的字符串
 * @param  string $n   默认加密,非 en解密
 * @return string      返回加密或解密的字符串
 */
function encdec_openssl($str,$n='en'){

	if ($n=='en') {
		return base64_encode(openssl_encrypt ($str,'AES-128-CBC','http://nobige.cn',OPENSSL_RAW_DATA,'MyQNum1272016935'));
	}else{
		return openssl_decrypt(base64_decode($str),'AES-128-CBC','http://nobige.cn',OPENSSL_RAW_DATA,'MyQNum1272016935');
	}
}

/**
 * 设置缓存
 * @param string $key
 * @param string $data
 */
function set_cache($key,$data){
	static $dir;
	$dir=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
	if (!is_dir($dir)) {
		mkdir($dir,0666);
	}
	if (!file_exists($dir.$key)) {
		file_put_contents($dir.$key,encdec_openssl(json_encode($data)),LOCK_EX);
		unset($dir,$key,$data);
	}
	return true;
}

/**
 * 得到缓存
 * @param  string $key key
 * @return string
 */
function get_cache($key){	
	static $dir;
	$dir=dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
	if (file_exists($dir.$key)) {
		$dec_cache=encdec_openssl(file_get_contents($dir.$key),1);
		if ($dec_cache!=false) {
			unset($key,$dir);
			return json_decode($dec_cache,true);
		}else{
			unset($key,$dir);
			return false;
		}
	}else{
		unset($key,$dir);
		return false;
	}
	
}
/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    //只信任 REMOTE_ADDR,X-Forwarded-For 等请求头可被任意伪造,不能作为记录依据
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = $ip ? sprintf("%u",ip2long($ip)) : 0;
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 字节数转可读大小
 * @param  int $size
 * @return string
 */
function format_size($size){
	if ($size>=1048576) {
		return round($size/1048576,2).' MB';
	}
	if ($size>=1024) {
		return round($size/1024,2).' KB';
	}
	return (int)$size.' B';
}

/**
 * SQLite自动初始化:建表/索引/默认管理员(无历史数据,全新库起步)
 * @param Medoo $db
 */
function db_init_sqlite($db){
	static $done=false;
	if ($done) {
		return;
	}
	$done=true;
	//数据目录
	if (!is_dir(DATA_DIR)) {
		@mkdir(DATA_DIR,0755,true);
	}
	$pdo=$db->pdo;
	$pdo->exec("CREATE TABLE IF NOT EXISTS imginfo (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		path TEXT DEFAULT '',
		ip TEXT DEFAULT '',
		ua TEXT DEFAULT '',
		date TEXT DEFAULT '',
		dir TEXT DEFAULT '',
		compress INTEGER DEFAULT 0,
		level INTEGER DEFAULT 0,
		see INTEGER DEFAULT 1,
		md5 TEXT DEFAULT '',
		name TEXT DEFAULT '',
		size TEXT DEFAULT '',
		delete_token TEXT DEFAULT '',
		user_id INTEGER DEFAULT 0
	)");
	$pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS uniq_md5 ON imginfo(md5)");
	$pdo->exec("CREATE INDEX IF NOT EXISTS idx_date ON imginfo(date)");
	$pdo->exec("CREATE INDEX IF NOT EXISTS idx_token ON imginfo(delete_token)");
	$pdo->exec("CREATE INDEX IF NOT EXISTS idx_user ON imginfo(user_id)");
	//注册用户
	$pdo->exec("CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT UNIQUE,
		password_hash TEXT,
		created TEXT DEFAULT '',
		last_login TEXT DEFAULT ''
	)");
	$pdo->exec("CREATE TABLE IF NOT EXISTS admin (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT UNIQUE,
		password_hash TEXT,
		otp_secret TEXT DEFAULT '',
		otp_enabled INTEGER DEFAULT 0,
		last_login TEXT DEFAULT ''
	)");
	$pdo->exec("CREATE TABLE IF NOT EXISTS stats_daily (
		date TEXT PRIMARY KEY,
		count INTEGER DEFAULT 0
	)");
	//上传频率限制(按IP按小时)
	$pdo->exec("CREATE TABLE IF NOT EXISTS rate_limit (
		ip TEXT,
		slot TEXT,
		count INTEGER DEFAULT 0,
		PRIMARY KEY (ip, slot)
	)");
	//管理员操作审计
	$pdo->exec("CREATE TABLE IF NOT EXISTS admin_log (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		username TEXT DEFAULT '',
		action TEXT DEFAULT '',
		target TEXT DEFAULT '',
		ip TEXT DEFAULT '',
		date TEXT DEFAULT ''
	)");
	//鉴黄Key池(moderatecontent,多Key轮询+日/月用量统计)
	$pdo->exec("CREATE TABLE IF NOT EXISTS mod_keys (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		key TEXT UNIQUE,
		enabled INTEGER DEFAULT 1,
		status TEXT DEFAULT 'ok',
		used_month TEXT DEFAULT '',
		used_count INTEGER DEFAULT 0,
		used_day TEXT DEFAULT '',
		used_day_count INTEGER DEFAULT 0
	)");
	//老库补列
	try { $pdo->exec("ALTER TABLE mod_keys ADD COLUMN used_day TEXT DEFAULT ''"); } catch (Exception $e) {}
	try { $pdo->exec("ALTER TABLE mod_keys ADD COLUMN used_day_count INTEGER DEFAULT 0"); } catch (Exception $e) {}
	//首次运行播种默认管理员 admin / admin123456
	$has_admin=$pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
	if (!$has_admin) {
		$st=$pdo->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
		$st->execute(['admin', password_hash('admin123456', PASSWORD_DEFAULT)]);
	}
}

/**
 * 上传频率限制:按IP按小时计数,超限返回false
 * 在上传入口调用,失败尝试也计数;配置 rate_hour<=0 表示不限制
 * @return bool
 */
function rate_limit_check(){
	global $db, $config;
	$max=isset($config['web']['rate_hour'])?(int)$config['web']['rate_hour']:60;
	if ($max<=0) {
		return true;
	}
	try {
		$ip=(string)get_client_ip();
		$slot=date('YmdH');
		$pdo=$db->pdo;
		//顺带清理2小时前的旧槽位
		$pdo->prepare("DELETE FROM rate_limit WHERE slot < ?")->execute([date('YmdH', strtotime('-2 hours'))]);
		$pdo->prepare("INSERT INTO rate_limit (ip, slot, count) VALUES (?, ?, 1)
			ON CONFLICT(ip, slot) DO UPDATE SET count = count + 1")->execute([$ip, $slot]);
		$st=$pdo->prepare("SELECT count FROM rate_limit WHERE ip = ? AND slot = ?");
		$st->execute([$ip, $slot]);
		return ((int)$st->fetchColumn()) <= $max;
	} catch (Exception $e) {
		return true;//限速组件故障不阻塞上传
	}
}

/**
 * 生成随机令牌(32位十六进制)
 * @return string
 */
function random_token(){
	return bin2hex(random_bytes(16));
}

/**
 * 递归统计目录大小与文件数
 * @param  string $dir
 * @return array ['size'=>字节,'files'=>数量]
 */
function dir_usage($dir){
	$size=0;
	$files=0;
	foreach (glob($dir.'/*', GLOB_NOSORT) as $item) {
		if (is_dir($item)) {
			$sub=dir_usage($item);
			$size+=$sub['size'];
			$files+=$sub['files'];
		}else{
			$size+=filesize($item);
			$files++;
		}
	}
	return ['size'=>$size,'files'=>$files];
}

/**
 * 图片目录磁盘用量(30分钟缓存,避免大目录频繁遍历)
 * @return array ['size'=>字节,'files'=>数量,'cached'=>bool]
 */
function img_disk_usage(){
	$cache_file=DATA_DIR.'usage_cache.json';
	if (is_file($cache_file)) {
		$cache=json_decode((string)file_get_contents($cache_file),true);
		if (is_array($cache) && (time()-(int)$cache['time'])<1800) {
			$cache['cached']=true;
			return $cache;
		}
	}
	$usage=dir_usage(ROOT.'i');
	$usage['time']=time();
	$usage['cached']=false;
	@file_put_contents($cache_file, json_encode($usage), LOCK_EX);
	return $usage;
}

/**
 * 扫描并清理孤儿图片文件(磁盘上有、数据库无记录,且修改时间超过1小时的非上传中文件)
 * @return array ['removed'=>数量,'freed'=>字节]
 */
function orphan_clean(){
	global $db;
	$paths=[];
	foreach ($db->select('imginfo', ['path']) as $row) {
		$paths[url_path($row['path'])]=true;
	}
	$removed=0;
	$freed=0;
	$cutoff=time()-3600;
	$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT.'i', FilesystemIterator::SKIP_DOTS));
	foreach ($it as $file) {
		if (!$file->isFile()) {
			continue;
		}
		$rel=str_replace('\\','/', substr($file->getPathname(), strlen(ROOT)));
		if (isset($paths[$rel])) {
			continue;
		}
		//只清理符合图床命名规则的文件,且跳过1小时内的新文件(可能正在上传)
		if (!preg_match('#^i/\d{4}/\d{2}/\d{2}/\d{9}\.(jpg|png|gif|webp|bmp)$#', $rel) || $file->getMTime()>$cutoff) {
			continue;
		}
		$freed+=$file->getSize();
		@unlink($file->getPathname());
		$removed++;
	}
	return ['removed'=>$removed,'freed'=>$freed];
}

/**
 * 后台统一的Smarty实例(显式指定模板/编译目录,不依赖当前工作目录)
 * @return Smarty
 */
function admin_smarty(){
	$smarty = new Smarty;
	$smarty->debugging = false;//debug
	$smarty->caching = false;//缓存
	$smarty->cache_lifetime = 120;//缓存有效时间 秒
	$smarty->setTemplateDir(dirname(SYSTEM_ROOT).DIRECTORY_SEPARATOR.'admin');
	$smarty->setCompileDir($smarty->getTemplateDir()[0].'templates_c');
	if (!is_dir($smarty->getCompileDir())) {
		@mkdir($smarty->getCompileDir(),0755,true);
	}
	//皮肤默认值(后台可配置,访客可在页面上自行切换)
	if (isset($GLOBALS['config']['web'])) {
		$smarty->assign('default_skin',isset($GLOBALS['config']['web']['default_skin'])?$GLOBALS['config']['web']['default_skin']:'light');
		$smarty->assign('default_accent',isset($GLOBALS['config']['web']['accent'])?$GLOBALS['config']['web']['accent']:'');
		$smarty->assign('site_title',isset($GLOBALS['config']['web']['title'])?$GLOBALS['config']['web']['title']:'1mg');
	}
	return $smarty;
}

/**
 * 统一输出json并结束
 * @param mixed $data
 */
function json_exit($data){
	header('Content-Type: application/json; charset=utf-8');
	exit(json_encode($data, JSON_UNESCAPED_SLASHES));
}

/**
 * 数据库中存放的相对路径统一为 / 分隔,保证生成的图片URL在Windows下也正确
 * @param  string $path
 * @return string
 */
function url_path($path){
	return str_replace(DIRECTORY_SEPARATOR, '/', (string)$path);
}

/**
 * 保存后台设置到 json 文件(不拼接PHP代码,避免写入代码注入)
 * @param  array $new_config 要覆盖的 db/web 配置
 * @return bool
 */
function save_user_config($new_config){
	$dir=SYSTEM_ROOT;
	$file=$dir.'config.user.json';
	$old=[];
	if (is_file($file)) {
		$old=json_decode((string)file_get_contents($file),true);
		if (!is_array($old)) {
			$old=[];
		}
	}
	foreach (['db','web'] as $sec) {
		if (isset($new_config[$sec]) && is_array($new_config[$sec])) {
			$old[$sec]=array_merge(isset($old[$sec]) && is_array($old[$sec])?$old[$sec]:[], $new_config[$sec]);
		}
	}
	$json=json_encode($old, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	//输入含非法UTF-8时json_encode返回false,绝不能把配置文件写空
	if ($json===false) {
		return false;
	}
	return (bool)file_put_contents($file, $json, LOCK_EX);
}

/**
 * 上传文件内容的简单webshell特征扫描
 * 图片里不应出现 "<?php" "<?=" 等标签,存在即拒绝(gif等不做重编码的格式尤其需要)
 * @param  string $tmp_file
 * @return bool  true=安全
 */
function scan_image_safe($tmp_file){
	$fp=fopen($tmp_file,'rb');
	if ($fp===false) {
		return false;
	}
	$head='';
	while (!feof($fp) && strlen($head)<1048576) {
		$head.=fread($fp,65536);
	}
	fclose($fp);
	foreach (['<?php','<?=', '<?script', '<?xml'.chr(0), '__HALT_COMPILER'] as $bad) {
		if (stripos($head,$bad)!==false) {
			return false;
		}
	}
	return true;
}