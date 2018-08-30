<?php
/**
 * 图片上传
 * md5查询是否重复
 * 前端压缩和前端md5传值不会
 */
// var_dump(getimagesize('test.html'));
// Array
// (
//     [file] => Array
//         (
//             [name] => 0077qhzWly1frtg0wegstj30go0goabv.jpg
//             [type] => image/jpeg
//             [tmp_name] => /private/var/tmp/phpet8x41
//             [error] => 0
//             [size] => 77152
//         )
// )

require 'system'.DIRECTORY_SEPARATOR.'config.php';
require SYSTEM_ROOT.'function.php';
// print_r($_FILES);
// echo(filesize($_FILES['file']['tmp_name']));
// print_r($_POST);exit;
// var_dump(file_exists('i/1808/29/1535543553781.jpg'));
//文件为空
if (empty($_FILES == false)) {
  //  203b2ca0f891467d8e73b4c1951790c6
  $file_md5=md5_file($_FILES['file']['tmp_name']);

  //0829
  $file_path='i'.DIRECTORY_SEPARATOR.date('ym').DIRECTORY_SEPARATOR.date('d');

  //创建文件夹
  if (!is_dir($file_path)) {
    if (!is_dir('i')) {
      mkdir('i',0666);
    }
    if (!is_dir('i'.DIRECTORY_SEPARATOR.date('ym'))) {
      mkdir('i'.DIRECTORY_SEPARATOR.date('ym'),0666);
    }
    mkdir($file_path,0666);
  }
/*查看数据是否相同文件
Array
(
    [path] => i/1808/29/1535543553781.jpg
    [see] => 1
)
 */
  $db_md5=$db->get('imginfo',['path','see'],['md5'=>$file_md5]);
  //是否重复
  if (!empty($db_md5)) {
    //是否违规
    if ($db_md5['see']) {
      if(file_exists($db_md5['path'])){
        //存在就删了上传文件
        unlink($_FILES['file']['tmp_name']);
      }else{
        move_uploaded_file($_FILES['file']['tmp_name'],$db_md5['path']);
      }
      exit(json_encode(['code'=>'success','data'=>['url'=>$config['web']['cdn'].$db_md5['path']]]));
    }else{
      exit(json_encode(['code'=>110,'error'=>'系统检测,你上传的极有可能是违规图片,请不要上传违规图片.']));
    }
  }
  unset($db_md5);
  if ($_FILES['file']['size'] > 5242880) {
  	exit(json_encode(['code'=>110,'error'=>'对不起，上传的文件大于5M']));
  }

  if ($_FILES['file']['error'] > 0) {
  	exit(json_encode(['code'=>110,'error'=>$_FILES['file']['error']]));
  }
  //获取文件后缀 .jpg
  $file_ext=strrchr($_FILES["file"]["name"], '.');

  //扩展名判断 .jpg
  if (in_array($file_ext,['.jpg','.png','.bmp','.gif']) == false) {
  	exit(json_encode(['code'=>110,'error'=>'扩展名不允许']));
  }

  //文件名 1535477902570.jpg
  $new_name = date('His').mt_rand(100,999).strtolower($file_ext);

  //mime
  if (getimagesize($_FILES['file']['tmp_name']) == false) {
  	exit(json_encode(['code'=>110,'error'=>'只允许上传图片文件']));
  }

  // 获取文件大小
  $file_size=filesize($_FILES['file']['tmp_name']);

  //移动 
  move_uploaded_file($_FILES['file']['tmp_name'],$file_path.DIRECTORY_SEPARATOR.$new_name);

  //入库
  $db->insert('imginfo',[
    'ua'=>$_SERVER['HTTP_USER_AGENT'],
    'ip'=>get_client_ip(),
    'date'=>date('Y-m-d H:i:s'),
    'md5'=>$file_md5,
    'path'=>$file_path.DIRECTORY_SEPARATOR.$new_name,
    'name'=>$new_name,
    'size'=>$file_size
  ]);
  exit(json_encode(['code'=>'success','data'=>['url'=>$config['web']['cdn'].$file_path.DIRECTORY_SEPARATOR.$new_name]]));
}else{
  exit(json_encode(['code'=>110,'error'=>'没有选择文件']));
}