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
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}