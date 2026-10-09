<?php
/**
 * 图片鉴黄 cron 入口: ?who=<img_level_pass>(口令与后台"图片鉴黄口令"一致)
 * 管理员也可在后台「内容审核」页直接操作
 */
require 'config.php';
require_once SYSTEM_ROOT.'function.php';
require_once SYSTEM_ROOT.'moderate.php';

$allowed=false;
if (isset($_SESSION['admin_id'])) {
	$allowed=true;
}elseif (isset($_GET['who']) && hash_equals((string)$config['web']['img_level_pass'],(string)$_GET['who'])) {
	$allowed=true;
}
if (!$allowed) {
	exit('404');
}

$stat=moderate_run(12);
echo implode('', [$stat['adult']?'3':'', $stat['ok']?'1':'', (!$stat['adult']&&!$stat['ok'])?'0':'']);
