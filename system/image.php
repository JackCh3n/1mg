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
 * @return array  ['ok'=>bool,'ext'=>最终扩展名,'size'=>最终大小,'compress'=>是否被压缩/转码]
 */
function compress_image($src, $dest, $ext){
	$info=getimagesize($src);
	if ($info===false) {
		return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
	}
	$mime=$info['mime'];
	$compress=0;

	//GIF(含动图)不重编码,原样保存
	if ($ext==='gif') {
		if (!@move_uploaded_file($src,$dest) && !@rename($src,$dest)) {
			return ['ok'=>false,'ext'=>$ext,'size'=>0,'compress'=>0];
		}
		clearstatcache(true,$dest);
		return ['ok'=>true,'ext'=>'gif','size'=>filesize($dest),'compress'=>0];
	}

	//BMP一律转成PNG
	if ($ext==='bmp') {
		$ext='png';
		$compress=1;
	}

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
	if ($side>IMG_MAX_SIDE) {
		$scale=IMG_MAX_SIDE/$side;
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

	//重编码到目标路径
	$done=false;
	if ($ext==='png') {
		$done=@imagepng($img,$dest,6);
	}else{
		$done=@imagejpeg($img,$dest,IMG_JPEG_QUALITY);
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
