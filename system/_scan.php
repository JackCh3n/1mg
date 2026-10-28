<?php
/**
 * 全新视角静态扫描 (执行后删除)
 * 1) 语法 2) Smarty模板结构 3) 未转义输出 4) 调试残留 5) 硬编码路径/域名 6) 危险模式
 */
$issues = [];

// 1. 语法
exec('git ls-files "*.php" 2>&1', $files);
foreach ($files as $f) {
    if (strpos($f, 'vendor/') === 0) continue;
    exec('php -l ' . escapeshellarg($f) . ' 2>&1', $out, $code);
    $line = implode('', $out);
    if ($code !== 0 || stripos($line, 'No syntax errors') === false) {
        $issues[] = "语法: $f => " . substr($line, 0, 120);
    }
    $out = [];
}

// 2. Smarty 模板: {$xxx} 未转义的输出点(排除明确允许的) + 标签配平
foreach (glob('admin/tpl_*.php') as $f) {
    $c = file_get_contents($f);
    preg_match_all('/\{(\$[a-zA-Z_][a-zA-Z0-9_\'\]\[]*)\}/', $c, $m);
    foreach (array_unique($m[1]) as $v) {
        //输出型标签无 |escape 修饰 —— 列出来人工判断
        $issues[] = "模板未转义候选: $f => {$v}";
    }
    //foreach/if 配平
    foreach ([['{foreach', '{/foreach}'], ['{if ', '{/if}'], ['{section', '{/section}']] as $p) {
        $o = substr_count($c, $p[0]);
        $cl = substr_count($c, $p[1]);
        if ($o !== $cl) $issues[] = "模板配平: $f => {$p[0]} $o vs {$p[1]} $cl";
    }
}

// 3. 调试残留
foreach (array_merge($files, glob('admin/tpl_*.php')) as $f) {
    if (strpos($f, 'vendor/') === 0) continue;
    $c = file_get_contents($f);
    foreach (['var_dump(', 'print_r(', 'console.log(', 'dd(', 'die(', ';exit;\n//debug'] as $pat) {
        if (strpos($pat, 'console.log') !== false && strpos($f, '.js') === false && strpos($f, 'tpl_') === false) continue;
        if (strpos($c, $pat) !== false && strpos($f, 'function.php') === false) {
            // 排除注释行
            preg_match_all('/^\s*' . preg_quote($pat, '/') . '/m', $c, $mm);
            if ($mm[0]) $issues[] = "调试残留: $f => " . $pat . " x" . count($mm[0]);
        }
    }
}

// 4. 硬编码
foreach ($files as $f) {
    if (strpos($f, 'vendor/') === 0) continue;
    $c = file_get_contents($f);
    foreach (['127.0.0.1:189', 'localhost:189', 'tu.x0ox.xyz', 'nobige.cn'] as $pat) {
        if (strpos($f, 'README.md') !== false) continue;
        if (strpos($c, $pat) !== false) $issues[] = "硬编码: $f => $pat";
    }
}

// 5. 危险模式: 未初始化变量/直接include用户输入
foreach ($files as $f) {
    if (strpos($f, 'vendor/') === 0) continue;
    $c = file_get_contents($f);
    if (preg_match('/(include|require)[^\n]*\$_(GET|POST|REQUEST)/', $c)) {
        $issues[] = "动态包含用户输入: $f";
    }
    if (preg_match('/\$_(GET|POST|REQUEST)\[[^\]]+\]\s*\(\s*\)/', $c)) {
        $issues[] = "可变函数调用用户输入: $f";
    }
}

echo count($issues), " 个发现:\n";
foreach ($issues as $i) echo "  - ", $i, "\n";
