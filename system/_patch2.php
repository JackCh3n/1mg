<?php
//一次性补丁:设置表单新行/前台动态限制/JS读取/首页链接/按钮排序/piexif误报 (执行后删除)
$ok = 0;

//1. tpl_seting.php 新增两行
$f = __DIR__.'/../admin/tpl_seting.php';
$c = file_get_contents($f);
if (strpos($c, 'max_size_mb') === false) {
    $row = '
                <div class="form-row">
                    <label>单张上限</label>
                    <input type="number" class="form-control" name="max_size_mb" min="1" max="50" value="{$config[\'web\'][\'max_size_mb\']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">MB(1-50,注意不超过服务器 upload_max_filesize)</span>
                </div>
                <div class="form-row">
                    <label>单次张数</label>
                    <input type="number" class="form-control" name="max_files" min="1" max="100" value="{$config[\'web\'][\'max_files\']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">一次最多上传的张数(1-100)</span>
                </div>';
    $anchor = '含失败尝试)</span>
                </div>';
    $c = str_replace($anchor, $anchor.$row, $c);
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), 'max_size_mb') !== false);

//2. index.php: data属性 + hero/帮助文案动态化
$f = __DIR__.'/../index.php';
$c = file_get_contents($f);
if (strpos($c, 'data-max-size') === false) {
    $c = str_replace(
    $old = 'data-api-token="<?php echo htmlspecialchars($config[\'web\'][\'api_token\'] ?? \'\', ENT_QUOTES, \'UTF-8\'); ?>">';
    $new = "data-api-token=\"<?php echo htmlspecialchars(\['web']['api_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>\"
                    data-max-size=\"<?php echo (int)(\['web']['max_size_mb'] ?? 5); ?>\"
                    data-max-count=\"<?php echo (int)(\['web']['max_files'] ?? 10); ?>\">";
    $c = str_replace($old, $new, $c);    $c = str_replace(
        '单张 5MB,一次最多 10 张</p>',
        '单张 <?php echo (int)($config[\'web\'][\'max_size_mb\'] ?? 5); ?>MB,一次最多 <?php echo (int)($config[\'web\'][\'max_files\'] ?? 10); ?> 张</p>',
        $c
    );
    $c = str_replace(
        '<li>单张不超过 <b>5MB</b>,一次最多 <b>10 张</b></li>',
        '<li>单张不超过 <b><?php echo (int)($config[\'web\'][\'max_size_mb\'] ?? 5); ?>MB</b>,一次最多 <b><?php echo (int)($config[\'web\'][\'max_files\'] ?? 10); ?> 张</b></li>',
        $c
    );
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), 'data-max-size') !== false);

//3. index.js: 读取动态限制
$f = __DIR__.'/../view/index.js';
$c = file_get_contents($f);
if (strpos($c, 'data-max-size') === false) {
    $c = str_replace(
        "    //API令牌(后台开启接口鉴权时随上传/预检携带)\n    var API_TOKEN = \$('#file').attr('data-api-token') || '';",
        "    //API令牌(后台开启接口鉴权时随上传/预检携带)\n    var API_TOKEN = \$('#file').attr('data-api-token') || '';\n    //后台可配的上传限制\n    var MAX_SIZE = parseInt(\$('#file').attr('data-max-size'), 10) || 5;\n    var MAX_COUNT = parseInt(\$('#file').attr('data-max-count'), 10) || 10;",
        $c
    );
    $c = str_replace(
        "        maxFileSize: 5120,\n        maxFilesNum: 10,\n        maxFileCount: 10,",
        "        maxFileSize: MAX_SIZE * 1024,\n        maxFilesNum: MAX_COUNT,\n        maxFileCount: MAX_COUNT,",
        $c
    );
    $c = str_replace(
        "                maxFileSize: 5120,",
        "                maxFileSize: MAX_SIZE * 1024,",
        $c
    );
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), 'MAX_SIZE') !== false);

//4. 后台首页链接(tpl_header topbar)
$f = __DIR__.'/../admin/tpl_header.php';
$c = file_get_contents($f);
if (strpos($c, '返回首页') === false) {
    $c = str_replace(
        '            <div class="topbar-right">
                <span class="who">',
        '            <div class="topbar-right">
                <a href="../" target="_blank" rel="noopener" title="打开前台首页">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;vertical-align:-3px"><path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
                    返回首页
                </a>
                <span class="who">',
        $c
    );
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), '返回首页') !== false);

//5. 按钮排序: Browse最左,Upload最右
$f = __DIR__.'/../view/site.css';
$c = file_get_contents($f);
if (strpos($c, 'fileinput-btn-order') === false) {
    $c .= "\n/* fileinput 按钮排序: Browse按钮最左,Upload按钮最右(fileinput-btn-order) */\n.file-input .input-group .input-group-btn { display: flex; width: 100%; gap: 10px; }\n.file-input .input-group .btn-file { order: -1; }\n.file-input .input-group .fileinput-upload { margin-left: auto; }\n";
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), 'fileinput-btn-order') !== false);

//6. piexif误报: 非JPEG不做EXIF解析(移除"Error loading the piexif.js library."误报)
$f = __DIR__.'/../vendor/smarty/../view/bootstrap-fileinput-4.4.9/js/fileinput.js';
$f = __DIR__.'/../view/bootstrap-fileinput-4.4.9/js/fileinput.js';
$c = file_get_contents($f);
$old = "                exifObj = window.piexif ? window.piexif.load(iData) : null;";
if (strpos($c, "iData.slice(0, 2)") === false && strpos($c, $old) !== false) {
    $new = "                exifObj = (iData && iData.slice(0, 2) === '/9j/' && window.piexif) ? window.piexif.load(iData) : null; //仅JPEG有EXIF,其他格式跳过避免误报";
    $c = str_replace($old, $new, $c);
    file_put_contents($f, $c);
}
$ok += (int)(strpos(file_get_contents($f), '/9j/') !== false);

//7. minified版同步修复(前台/后台实际加载的是min)
$fmin = __DIR__.'/../view/bootstrap-fileinput-4.4.9/js/fileinput.min.js';
$cmin = file_get_contents($fmin);
if (strpos($cmin, "'/9j/'") === false && strpos($cmin, 'window.piexif?window.piexif.load(') !== false) {
    $cmin = str_replace('window.piexif?window.piexif.load(', "iData&&'/9j/'===iData.slice(0,2)&&window.piexif?window.piexif.load(", $cmin);
    file_put_contents($fmin, $cmin);
}
$ok += (int)(strpos(file_get_contents($fmin), '/9j/') !== false);

echo 'patch score(7 expected): ', $ok, PHP_EOL;
