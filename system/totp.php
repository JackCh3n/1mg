<?php
/**
 * TOTP 两步验证(RFC 6238,兼容 Google Authenticator 等验证器)
 * 纯PHP实现,无外部依赖
 */

/**
 * 生成Base32密钥(160位)
 * @return string
 */
function totp_generate_secret(){
	$alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$raw=random_bytes(20);
	$out='';
	for ($i=0; $i<strlen($raw); $i++) {
		$out.=$alphabet[ord($raw[$i])>>3];
	}
	return $out;
}

/**
 * Base32解码(RFC 4648,忽略空白与填充)
 * @param  string $b32
 * @return string 二进制
 */
function totp_base32_decode($b32){
	$alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$b32=strtoupper(preg_replace('/[^A-Za-z2-7]/','', (string)$b32));
	$bits='';
	foreach (str_split($b32) as $c) {
		$pos=strpos($alphabet,$c);
		if ($pos===false) {
			continue;
		}
		$bits.=str_pad(decbin($pos),5,'0',STR_PAD_LEFT);
	}
	$out='';
	foreach (str_split($bits,8) as $byte) {
		if (strlen($byte)===8) {
			$out.=chr(bindec($byte));
		}
	}
	return $out;
}

/**
 * 计算指定时间片的TOTP验证码
 * @param  string $secret Base32密钥
 * @param  int    $slice  时间片(time()/30)
 * @return string 6位数字
 */
function totp_code($secret, $slice=null){
	if ($slice===null) {
		$slice=(int)floor(time()/30);
	}
	$key=totp_base32_decode($secret);
	$bin=pack('N',0).pack('N',$slice);
	$hash=hash_hmac('sha1',$bin,$key,true);
	$offset=ord(substr($hash,-1)) & 0x0F;
	$value=unpack('N',substr($hash,$offset,4))[1] & 0x7FFFFFFF;
	return str_pad((string)($value % 1000000),6,'0',STR_PAD_LEFT);
}

/**
 * 校验验证码,允许±1个时间片时钟偏差
 * @param  string $secret Base32密钥
 * @param  string $code   用户输入的6位验证码
 * @return bool
 */
function totp_verify($secret, $code){
	$code=preg_replace('/\D/','', (string)$code);
	if (strlen($code)!==6 || strlen($secret)<16) {
		return false;
	}
	$now=(int)floor(time()/30);
	for ($i=$now-1; $i<=$now+1; $i++) {
		if (hash_equals(totp_code($secret,$i),$code)) {
			return true;
		}
	}
	return false;
}

/**
 * 生成otpauth URI(验证器扫码/手动录入用)
 * @param  string $secret
 * @param  string $account 显示的账号名
 * @param  string $issuer  站点名
 * @return string
 */
function totp_uri($secret, $account, $issuer='1mg'){
	return 'otpauth://totp/'.rawurlencode($issuer).':'.rawurlencode($account)
		.'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&algorithm=SHA1&digits=6&period=30';
}
