<?php
//删除 site 动作保存数组中的 6 个图片处理键 v2 (执行后删除)
$f = __DIR__ . '/../admin/seting.php';
$lines = file($f);
$delIdx = [];
foreach ($lines as $i => $l) {
    if ($i >= 90 && $i <= 108 && preg_match("/'(img_max_side|img_jpeg_quality|img_webp_quality|watermark_text|watermark_font|ttl_enabled)=>/", $l)) {
        $delIdx[] = $i;
    }
}
if (count($delIdx) !== 6) { echo "expect 6, got ", count($delIdx), "\n"; exit(1); }
foreach ($delIdx as $i) { unset($lines[$i]); }
file_put_contents($f, implode('', $lines));
echo "removed 6 keys\n";
