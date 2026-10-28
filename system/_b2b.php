<?php
//B2 前端集成: 设置卡片/仪表盘/手动同步/cron (执行后删除)
function patch($file, $pairs) {
    $raw = file_get_contents($file);
    $crlf = (strpos($raw, "\r\n") !== false);
    $c = str_replace("\r\n", "\n", $raw);
    $miss = [];
    foreach ($pairs as $i => $p) {
        if (strpos($c, $p[0]) === false) { $miss[] = $i; continue; }
        $c = str_replace($p[0], $p[1], $c);
    }
    if ($crlf) { $c = str_replace("\n", "\r\n", $c); }
    file_put_contents($file, $c);
    echo basename($file), ': patched=', count($pairs) - count($miss), ' miss=', json_encode($miss), PHP_EOL;
}

//1. tpl_seting.php: B2 卡片(插在"接口令牌"卡片之前) + 测试按钮JS
patch(__DIR__.'/../admin/tpl_seting.php', [
    [
        "    <div class=\"acard\">\n        <div class=\"acard-head\">接口令牌",
        "    <div class=\"acard\">\n"
        . "        <div class=\"acard-head\">B2 云存储同步 <span class=\"sub\">Backblaze B2 · 定时把图片上传到 B2 备份,支持删除同步与用量统计</span></div>\n"
        . "        <div class=\"acard-body\">\n"
        . "            {if \$b2['last_error']}<div class=\"alert alert-danger\">最近一次同步出错: {\$b2['last_error']|escape}</div>{/if}\n"
        . "            <p class=\"text-muted\" style=\"margin-top:0\">当前: {if \$b2['enabled']}<span class=\"label label-success\">已启用</span>{else}<span class=\"label label-danger\">未启用</span>{/if}\n"
        . "                已同步 <b>{\$$b2['synced']}</b> 张({format_size(\$b2['synced_size'])}) · 待同步 <b>{\$b2['pending']}</b> 张 · 最近同步: {\$b2['last_run']|default:'从未'} · 累计上传 {\$b2['uploaded_total']} / 删除 {\$b2['deleted_total']}</p>\n"
        . "            <form action=\"seting.php\" method=\"post\">\n"
        . "                <input type=\"hidden\" name=\"_csrf\" value=\"{\$csrf}\">\n"
        . "                <input type=\"hidden\" name=\"action\" value=\"b2\">\n"
        . "                <div class=\"form-row\">\n"
        . "                    <label>启用同步</label>\n"
        . "                    <label style=\"width:auto;display:flex;align-items:center;gap:8px;font-weight:400;font-size:14px;color:var(--text)\">\n"
        . "                        <input type=\"checkbox\" name=\"b2_enabled\" value=\"1\"{if \$config['web']['b2_enabled']} checked{/if} style=\"width:17px;height:17px\">\n"
        . "                        定时上传图片到 B2(随归档任务每6小时执行,也可手动)\n"
        . "                    </label></div>\n"
        . "                <div class=\"form-row\"><label>Key ID</label>\n"
        . "                    <input type=\"text\" class=\"form-control\" id=\"b2_key_id\" name=\"b2_key_id\" value=\"{\$config['web']['b2_key_id']|escape}\" style=\"max-width:340px;font-family:var(--mono)\">\n"
        . "                    <button type=\"button\" class=\"btn btn-sm\" id=\"b2-test-btn\">测试连接</button>\n"
        . "                    <span class=\"help-block\" id=\"b2-test-result\" style=\"margin:0 0 0 10px;align-self:center\"></span></div>\n"
        . "                <div class=\"form-row\"><label>App Key</label>\n"
        . "                    <input type=\"password\" class=\"form-control\" id=\"b2_app_key\" name=\"b2_app_key\" value=\"{\$config['web']['b2_app_key']|escape}\" style=\"max-width:340px;font-family:var(--mono)\">\n"
        . "                    <span class=\"text-muted\" style=\"font-size:12.5px;align-self:center\">保存在服务端配置文件,不随git提交</span></div>\n"
        . "                <div class=\"form-row\"><label>存储桶 ID</label>\n"
        . "                    <input type=\"text\" class=\"form-control\" name=\"b2_bucket_id\" value=\"{\$config['web']['b2_bucket_id']|escape}\" style=\"max-width:340px;font-family:var(--mono)\">\n"
        . "                    <span class=\"text-muted\" style=\"font-size:12.5px;align-self:center\">点「测试连接」会列出账号下所有桶及其ID</span></div>\n"
        . "                <div class=\"form-row\"><label>存储桶名</label>\n"
        . "                    <input type=\"text\" class=\"form-control\" name=\"b2_bucket_name\" value=\"{\$config['web']['b2_bucket_name']|escape}\" style=\"max-width:220px\" placeholder=\"仅备注\"></div>\n"
        . "                <div class=\"form-row\"><label>自定义域名</label>\n"
        . "                    <input type=\"text\" class=\"form-control\" name=\"b2_domain\" value=\"{\$config['web']['b2_domain']|escape}\" style=\"max-width:320px\" placeholder=\"https://cdn.example.com\">\n"
        . "                    <span class=\"text-muted\" style=\"font-size:12.5px;align-self:center\">B2 支持绑定域名,此处仅作展示;留空用 B2 默认地址</span></div>\n"
        . "                <div class=\"form-row\"><label>每轮数量</label>\n"
        . "                    <input type=\"number\" class=\"form-control\" name=\"b2_sync_batch\" min=\"1\" max=\"100\" value=\"{\$config['web']['b2_sync_batch']}\" style=\"max-width:100px\">\n"
        . "                    <label style=\"width:auto;display:flex;align-items:center;gap:8px;font-weight:400;font-size:14px;color:var(--text);margin-left:16px\">\n"
        . "                        <input type=\"checkbox\" name=\"b2_delete_remote\" value=\"1\"{if \$config['web']['b2_delete_remote']} checked{/if} style=\"width:17px;height:17px\">\n"
        . "                        本站删除时同步删除 B2 副本\n"
        . "                    </label></div>\n"
        . "                <div class=\"form-actions\"><button class=\"btn btn-primary\" type=\"submit\">保存</button></div>\n"
        . "            </form>\n"
        . "        </div>\n"
        . "    </div>\n\n"
        . "    <div class=\"acard\">\n        <div class=\"acard-head\">接口令牌",
    ],
]);
//修正上面故意写的 {$$b2} 笔误
$raw = file_get_contents(__DIR__.'/../admin/tpl_seting.php');
$raw = str_replace('{\\$$b2[\'synced\']}', '{$b2[\'synced\']}', $raw);
$raw = str_replace('{$$b2[', '{$b2[', $raw);
file_put_contents(__DIR__.'/../admin/tpl_seting.php', $raw);

//2. tpl_seting.php: 测试按钮JS(复用 testkey 模式,追加在文件尾)
$c = file_get_contents(__DIR__.'/../admin/tpl_seting.php');
$js = '
<script>
{literal}
(function(){
    var btn=document.getElementById("b2-test-btn");
    if(!btn) return;
    btn.addEventListener("click", function(){
        var out=document.getElementById("b2-test-result");
        var csrf=document.querySelector("input[name=_csrf]").value;
        var body="_csrf="+encodeURIComponent(csrf)
            +"&action=b2_test"
            +"&b2_key_id="+encodeURIComponent(document.getElementById("b2_key_id").value)
            +"&b2_app_key="+encodeURIComponent(document.getElementById("b2_app_key").value);
        btn.disabled=true; btn.textContent="测试中…";
        out.textContent="";
        fetch("seting.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:body})
        .then(function(r){return r.json();})
        .then(function(d){ out.textContent=d.msg; out.style.color=d.ok?"var(--ok)":"var(--danger)"; })
        .catch(function(){ out.textContent="请求失败"; out.style.color="var(--danger)"; })
        .finally(function(){ btn.disabled=false; btn.textContent="测试连接"; });
    });
})();
{/literal}
</script>
';
$c = rtrim($c) . "\n" . $js;
file_put_contents(__DIR__.'/../admin/tpl_seting.php', $c);
echo "tpl_seting b2 card+js done\n";

//3. admin/index.php: 手动同步动作 + 统计注入
patch(__DIR__.'/../admin/index.php', [
    [
        "require_once SYSTEM_ROOT.'archive.php';",
        "require_once SYSTEM_ROOT.'archive.php';\nrequire_once SYSTEM_ROOT.'b2.php';"
    ],
    [
        "//孤儿文件清理",
        "//手动 B2 同步\n\$b2_msg='';\nif (isset(\$_GET['action']) && \$_GET['action']=='b2_sync' && isset(\$_GET['token'])) {\n\tif (hash_equals(csrf_token(),(string)\$_GET['token'])) {\n\t\t\$br=b2_sync_run();\n\t\tadmin_log('b2_sync','up='.\$br['uploaded'].',del='.\$br['deleted'].',fail='.\$br['failed']);\n\t\theader('Location: index.php?b2='.(\$br['ok']?'1':'0'));\n\t\texit();\n\t}\n\t\$b2_msg='非法请求';\n}\n\n//孤儿文件清理"
    ],
    [
        "\$smarty->assign('archive_msg',\$archive_msg);",
        "\$smarty->assign('archive_msg',\$archive_msg);\n\$smarty->assign('b2_stat',b2_stats());\n\$smarty->assign('b2_msg',\$b2_msg.(isset(\$_GET['b2'])?((\$_GET['b2']==='1')?'B2 同步完成一轮':'B2 同步失败,请到设置页查看错误'):''));\n\$smarty->assign('b2_domain',rtrim((string)(\$config['web']['b2_domain'] ?? ''),'/'));\n\$smarty->assign('b2_conf_on',!empty(\$config['web']['b2_enabled']));"
    ],
]);

//4. tpl_index.php: B2 统计卡(插在归档卡片之后,即最后一个 acard 后)
patch(__DIR__.'/../admin/tpl_index.php', [
    [
        "{include file=\"tpl_footer.php\"}",
        "{if \$b2_conf_on}\n"
        . "    <div class=\"acard\">\n"
        . "        <div class=\"acard-head\">B2 云存储同步\n"
        . "            <span class=\"head-right\"><a class=\"btn btn-sm\" href=\"index.php?action=b2_sync&amp;token={\$csrf}\" onclick=\"return confirm('立即执行一轮B2同步?')\">立即同步</a></span>\n"
        . "        </div>\n"
        . "        <div class=\"acard-body\">\n"
        . "            <p class=\"text-muted\" style=\"margin-top:0\">已同步 <b>{\$b2_stat['synced']}</b> 张 · {format_size(\$b2_stat['synced_size'])} · 待同步 <b>{\$b2_stat['pending']}</b> 张 · 累计上传 {\$b2_stat['uploaded_total']} / 删除 {\$b2_stat['deleted_total']} · 最近同步: {\$b2_stat['last_run']|default:'从未'}</p>\n"
        . "            {if \$b2_stat['last_error']}<div class=\"alert alert-danger\">{\$b2_stat['last_error']|escape}</div>{/if}\n"
        . "            {if \$b2_domain}<p class=\"text-muted\" style=\"font-size:12.5px\">镜像域名示例: <a href=\"{\$b2_domain}/i/\" target=\"_blank\" rel=\"noopener\">{\$b2_domain}</a>(文件路径与本地一致)</p>{/if}\n"
        . "        </div>\n"
        . "    </div>\n"
        . "{/if}\n\n{include file=\"tpl_footer.php\"}"
    ],
]);

//5. cron挂接: admin/archive.php 的 cron 分支同步后顺带B2
patch(__DIR__.'/../admin/archive.php', [
    [
        "\t\$result=archive_run((int)\$config['web']['retention_online'],(int)\$config['web']['retention_archive']);\n\texit(json_encode(['code'=>'success','data'=>\$result], JSON_UNESCAPED_UNICODE));",
        "\t\$result=archive_run((int)\$config['web']['retention_online'],(int)\$config['web']['retention_archive']);\n"
        . "\t//顺带执行一轮B2同步(未启用则跳过)\n"
        . "\t\$b2_result=null;\n"
        . "\ttry {\n"
        . "\t\trequire_once SYSTEM_ROOT.'b2.php';\n"
        . "\t\tif (b2_ready()) {\n"
        . "\t\t\t\$b2_result=b2_sync_run();\n"
        . "\t\t}\n"
        . "\t} catch (Exception \$e) {\n"
        . "\t\t\$b2_result=['ok'=>false,'error'=>\$e->getMessage()];\n"
        . "\t}\n"
        . "\texit(json_encode(['code'=>'success','data'=>['archive'=>\$result,'b2'=>\$b2_result]], JSON_UNESCAPED_UNICODE));"
    ],
]);
