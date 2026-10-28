<?php
//v3: strpos 匹配删除 (执行后删除)
$f = __DIR__ . '/../admin/seting.php';
$lines = file($f);
$keys = ["'img_max_side'=>", "'img_jpeg_quality'=>", "'img_webp_quality'=>", "'watermark_text'=>", "'watermark_font'=>", "'ttl_enabled'=>"];
$del = [];
foreach ($lines as $i => $l) {
    foreach ($keys as $k) {
        if (strpos($l, $k) !== false) {
            //只删 site 动作里的(缩进为5个tab),imageproc 里的是4个tab
            if (substr($l, 0, 6) === "\t\t\t\t\t") {
                $del[$i] = true;
            }
        }
    }
}
if (count($del) !== 6) {
    echo "expect 6 got ", count($del), "\n";
    foreach ($del as $i => $_) echo "  line ", $i + 1, ": ", json_encode($lines[$i]), "\n";
    exit(1);
}
foreach ($del as $i => $_) unset($lines[$i]);
file_put_contents($f, implode('', $lines));
echo "removed ", count($del), " lines\n";
