<?php
/**
 * 图片压缩与安全重编码(基于GD)
 * JPEG/PNG: 重编码压缩,超大图等比缩放,JPEG自动按EXIF转正
 * BMP:      转存为PNG(体积更小)
 * GIF:      不处理(会破坏动图),原样保存
 * 重编码同时会剥离图片内夹杂的PHP等非图像数据
 */

//长边超过该值则等比缩小
define('IMG_MAX_SIDE', 2560);
//JPEG压缩质量
define('IMG_JPEG_QUALITY', 80);

/**
 * 压缩并保存图片
 * @param  string $src    上传的临时文件
 * @param  string $dest   目标完整路径(含文件名)
 * @param  string $ext    小写扩展名(不含点) jpg/jpeg/png/gif/bmp
 * @param  bool   $webp   是否优先转存WebP(GIF除外,体积更小)
 * @return array  ['ok'=>bool,'ext'=>最终扩展名,'size'=>最终大小,'compress'=>是否被压缩/转码]
 */
function compress_image($src, $dest_base, $ext, $webp=false){
	//参数从后台配置读取(带常量兜底)
	$cfg=isset($GLOBALS['config']['web'])?$GLOBALS['config']['web']:[];
	$max_side=(int)(isset($cfg['img_max_side'])?$cfg['img_max_side']:IMG_MAX_SIDE);
	$jpeg_q=(int)(isset($cfg['img_jpeg_quality'])?$cfg['img_jpeg_quality']:IMG_JPEG_QUALITY);
	$webp_q=(int)(isset($cfg['img_webp_quality'])?$cfg['img_webp_quality']:82);
	$wm_text=trim((string)(isset($cfg['watermark_text'])?$cfg['watermark_text']:''));
	$wm_font=trim((string)(isset($cfg['watermark_font'])?$cfg['watermark_font']:''));
	$info=getimagesize($src);
	if ($info===false) {
		return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
	}
	$mime=$info['mime'];
	$compress=0;
	//最终落盘路径 = 基础名 + 最终扩展名(扩展名可能因WebP转换而变化)
	$dest=$dest_base.'.'.$ext;

	//GIF(含动图)不重编码,原样保存
	if ($ext==='gif') {
		if (!@move_uploaded_file($src,$dest) && !@rename($src,$dest)) {
			return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
		}
		clearstatcache(true,$dest);
		return ['ok'=>true,'ext'=>'gif','size'=>filesize($dest),'compress'=>0];
	}

	//BMP转PNG;开启WebP且GD支持时,jpg/png/bmp一律转WebP(GIF保持动图原样)
	if ($ext==='bmp') {
		$ext=function_exists('imagewebp') ? 'webp' : 'png';
		$compress=1;
	}elseif ($webp && in_array($ext,['jpg','png']) && function_exists('imagewebp')) {
		$ext='webp';
		$compress=1;
	}
	$dest=$dest_base.'.'.$ext;

	$img=null;
	if ($mime==='image/jpeg' || $ext==='jpg') {
		$img=@imagecreatefromjpeg($src);
		if ($img && function_exists('exif_read_data')) {
			$img=exif_rotate_image($img, @exif_read_data($src));
		}
	}elseif ($mime==='image/png' || $ext==='png') {
		$img=@imagecreatefrompng($src);
	}elseif ($mime==='image/gif') {
		$img=@imagecreatefromgif($src);
	}elseif ($mime==='image/webp' || $ext==='webp') {
		$img=@imagecreatefromwebp($src);
		//WebP输入统一重编码输出(剥离元数据),关闭WebP时也保持webp
		$ext='webp';
		$compress=1;
	}elseif (in_array($mime,['image/bmp','image/x-ms-bmp','image/x-bmp'])) {
		$img=@imagecreatefrombmp($src);
	}
	if (!$img) {
		//GD解码失败时,前面getimagesize已通过,保守起见原样保存
		if (!@move_uploaded_file($src,$dest) && !@rename($src,$dest)) {
			return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
		}
		clearstatcache(true,$dest);
		return ['ok'=>true,'ext'=>$ext,'size'=>filesize($dest),'compress'=>0];
	}

	//真彩色+保留透明通道
	if (!imageistruecolor($img)) {
		$tmp=imagecreatetruecolor(imagesx($img),imagesy($img));
		imagefill($tmp,0,0,imagecolorallocatealpha($tmp,0,0,0,127));
		imagecopy($tmp,$img,0,0,0,0,imagesx($img),imagesy($img));
		imagedestroy($img);
		$img=$tmp;
	}
	imagealphablending($img,true);
	imagesavealpha($img,true);

	//超大图等比缩放
	$w=imagesx($img);
	$h=imagesy($img);
	$side=max($w,$h);
	if ($side>$max_side && $max_side>0) {
		$scale=$max_side/$side;
		$nw=max(1,(int)round($w*$scale));
		$nh=max(1,(int)round($h*$scale));
		$scaled=imagecreatetruecolor($nw,$nh);
		imagealphablending($scaled,false);
		imagesavealpha($scaled,true);
		imagefill($scaled,0,0,imagecolorallocatealpha($scaled,0,0,0,127));
		imagecopyresampled($scaled,$img,0,0,0,0,$nw,$nh,$w,$h);
		imagedestroy($img);
		$img=$scaled;
		$compress=1;
	}

	//叠加水印(配置了水印文字时)
	if ($wm_text!=='') {
		watermark_apply($img, $wm_text, $wm_font);
	}

	//重编码到目标路径
	$done=false;
	if ($ext==='webp') {
		$done=@imagewebp($img,$dest,$webp_q);
	}elseif ($ext==='png') {
		$done=@imagepng($img,$dest,6);
	}else{
		$done=@imagejpeg($img,$dest,$jpeg_q);
	}
	imagedestroy($img);
	if (!$done) {
		return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
	}

	//压缩后反而变大且没缩放时,退回原始文件
	$final_size=filesize($dest);
	$orig_size=filesize($src);
	if ($compress===0 && $orig_size>0 && $final_size>=$orig_size) {
		@unlink($dest);
		if (!@move_uploaded_file($src,$dest) && !@rename($src,$dest)) {
			return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
		}
		clearstatcache(true,$dest);
		return ['ok'=>true,'ext'=>$ext,'size'=>filesize($dest),'compress'=>0];
	}
	$compress=1;
	return ['ok'=>true,'ext'=>$ext,'size'=>$final_size,'compress'=>$compress];
}

/**
 * 在图片右下角叠加水印(支持TTF字体,未配置字体时用内置位图字体)
 * @param resource $img
 * @param string $text
 * @param string $font TTF字体路径(可空)
 */
function watermark_apply($img, $text, $font=''){
	$w=imagesx($img);
	$h=imagesy($img);
	$color=imagecolorallocatealpha($img, 255, 255, 255, 78);
	$shadow=imagecolorallocatealpha($img, 0, 0, 0, 78);
	if ($font!=='' && is_file($font) && function_exists('imagettftext')) {
		$size=max(12, (int)round($w/45));
		$box=imagettfbbox($size, 0, $font, $text);
		$tw=abs($box[2]-$box[0]);
		$th=abs($box[5]-$box[1]);
		$x=max(4, $w-$tw-14);
		$y=max($th+8, $h-12);
		imagettftext($img, $size, 0, $x+1, $y+1, $shadow, $font, $text);
		imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
	} else {
		//内置位图字体:仅ASCII,中文会显示为乱码,故先过滤
		$ascii=preg_replace('/[^\x20-\x7E]/', '', $text);
		if ($ascii!=='') {
			$fw=imagefontwidth(5);
			$fh=imagefontheight(5);
			$x=max(2, $w-strlen($ascii)*$fw-8);
			$y=max($fh, $h-$fh-6);
			imagestring($img, 5, $x+1, $y+1, $ascii, $shadow);
			imagestring($img, 5, $x, $y, $ascii, $color);
		}
	}
}

/**
 * 按EXIF Orientation把JPEG转正(手机拍摄的横竖向问题)
 * @param  resource $img
 * @param  array|false $exif
 * @return resource
 */
function exif_rotate_image($img, $exif){
	if (empty($exif) || empty($exif['Orientation'])) {
		return $img;
	}
	$o=(int)$exif['Orientation'];
	if ($o<2 || $o>8) {
		return $img;
	}
	switch ($o) {
		case 2: //水平翻转
			imageflip($img, IMG_FLIP_HORIZONTAL);
			break;
		case 3:
			$img=imagerotate($img,180,0);
			break;
		case 4:
			imageflip($img, IMG_FLIP_VERTICAL);
			break;
		case 5:
			$img=imagerotate($img,-90,0);
			imageflip($img, IMG_FLIP_HORIZONTAL);
			break;
		case 6:
			$img=imagerotate($img,-90,0);
			break;
		case 7:
			$img=imagerotate($img,90,0);
			imageflip($img, IMG_FLIP_HORIZONTAL);
			break;
		case 8:
			$img=imagerotate($img,90,0);
			break;
	}
	return $img;
}
