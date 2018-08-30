<?php
/**
 *  今日上传榜
 */
require '../system'.DIRECTORY_SEPARATOR.'config.php';
require SYSTEM_ROOT.'function.php';
   $key = 'qwe';
    $url = 'qweqwe';
    // echo json_encode([
    //     'initialPreview' => [
    //         "i/1808/29/195645686.jpg",
    //         "http://iph.href.lu/320x220",
    //         "http://iph.href.lu/320x220",
    //         "http://iph.href.lu/320x220",
    //     ],
    //     'initialPreviewConfig' => [
    //         ['type'=>'image','caption' => "Sports-1.jpg", 'size' => 627392, 'width' => '120px','url' => $url, 'key' => $key],
    //         ['caption' => "Sports-2.jpg", 'size' => 123123, 'width' => '120px', 'url' => $url, 'key' => $key],
    //         ['caption' => "Sports-3.jpg", 'size' => 321233, 'width' => '120px', 'url' => $url, 'key' => $key],
    //         ['caption' => "Sports-4.jpg", 'size' => 523131, 'width' => '120px', 'url' => $url, 'key' => $key],
    //     ],
    //     'append' => true // 是否把这些配置加入`initialPreview`。
    //                      // 如果设置为`false`，它会重载初始预览。
    //                      // 如果设置为`true`，它会加入初始预览之中。
    //                      // 如果这个属性没有被设置或者没有传出，它会默认为`true`。
    //                      
    // ]);
$today_data=$db->select('imginfo',['path','name','size'],['date[~]'=>date('Y-m-d'),'LIMIT'=>20,'see'=>1]);
// if (count($today_out)<12) {
// 	exit(json_encode(['code'=>110,'error'=>'没找到，过会再看看？']));
// }
shuffle($today_data);
$for_n=count($today_data)>12?12:count($today_data);
for ($i=0; $i <$for_n ; $i++) { 
	$today_out['url'][$i]=$config['web']['cdn'].$today_data[$i]['path'];
	$today_out['iPC'][$i]=[
		'caption'=>$today_data[$i]['name'],
		'size'=>$today_data[$i]['size'],
		'key'=>$i
	];
}
echo(json_encode([
	'initialPreview'=>$today_out['url'],
	'initialPreviewConfig'=>$today_out['iPC'],
	'initialPreviewAsData'=>true,'uploadUrl'=>'upload.php',
]));
unset($today_data,$today_out);
// exit(json_encode(['initialPreview'=>$today_]));