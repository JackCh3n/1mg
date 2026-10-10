{include file="tpl_header.php"}

    {if $save_error}<div class="alert alert-danger">{$save_error|escape}</div>{/if}
    {if $save_ok}<div class="alert alert-success">{$save_ok|escape}</div>{/if}
    {if $otp_error}<div class="alert alert-danger">{$otp_error|escape}</div>{/if}

    <div class="acard">
        <div class="acard-head">基本信息</div>
        <div class="acard-body">
            <form action="seting.php" method="post">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="action" value="site">
                <div class="form-row"><label>网站标题</label><input type="text" class="form-control" name="title" value="{$config['web']['title']|escape}" required></div>
                <div class="form-row"><label>网址</label><input type="text" class="form-control" name="server" value="{$config['web']['server']|escape}" placeholder="https://example.com"></div>
                <div class="form-row"><label>CDN 域名</label><input type="text" class="form-control" name="cdn" value="{$config['web']['cdn']|escape}" placeholder="https://cdn.example.com/"></div>
                <div class="form-row"><label>鉴黄 Key</label><input type="text" class="form-control" id="img_level_key" name="key" value="{$config['web']['img_level_key']|escape}" style="max-width:320px"><button type="button" class="btn btn-sm" id="test-key-btn">测试可用性</button><span class="help-block" id="test-key-result" style="margin:0 0 0 10px;align-self:center"></span></div>
                <div class="form-row"><label>鉴黄口令</label><input type="text" class="form-control" name="pass" value="{$config['web']['img_level_pass']|escape}"><span class="help-block" style="margin:0 0 0 10px;align-self:center">同时用作归档 cron 接口的访问口令</span></div>
                <div class="form-row">
                    <label>上传限速</label>
                    <input type="number" class="form-control" name="rate_hour" min="0" max="10000" value="{$config['web']['rate_hour']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">次/小时/IP(0=不限制,含失败尝试)</span>
                </div>
                <div class="form-row">
                    <label>单张上限</label>
                    <input type="number" class="form-control" name="max_size_mb" min="1" max="50" value="{$config['web']['max_size_mb']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">MB(1-50,注意不超过服务器 upload_max_filesize)</span>
                </div>
                <div class="form-row">
                    <label>单次张数</label>
                    <input type="number" class="form-control" name="max_files" min="1" max="100" value="{$config['web']['max_files']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">一次最多上传的张数(1-100)</span>
                </div>
                <div class="form-row">
                    <label>WebP 转存</label>
                    <label style="width:auto;display:flex;align-items:center;gap:8px;font-weight:400;font-size:14px;color:var(--text)">
                        <input type="checkbox" name="webp_enabled" value="1"{if $config['web']['webp_enabled']} checked{/if} style="width:17px;height:17px">
                        上传时自动转为 WebP(体积更小)
                    </label>
                </div>
                <div class="form-row">
                    <label>API 令牌</label>
                    <input type="text" class="form-control" name="api_token" id="api_token" value="{$config['web']['api_token']|escape}" placeholder="留空=开放上传接口" style="max-width:320px">
                    <button type="button" class="btn btn-sm" onclick="var t='';var c='0123456789abcdef';for(var i=0;i<32;i++)t+=c[Math.floor(Math.random()*16)];document.getElementById('api_token').value=t;">生成</button>
                </div>
                <div class="form-row"><label></label><span class="help-block" style="align-self:center">非空时上传/预检接口需携带令牌(表单字段 api_token 或请求头 X-API-Token),详见 <a href="../api.php" target="_blank">API 文档</a></span></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">保存</button></div>
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">数据保留策略 <span class="sub">在线 → 归档 → 清理,三级保留</span></div>
        <div class="acard-body">
            <form action="seting.php" method="post">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="action" value="policy">
                <div class="form-row">
                    <label>在线保留</label>
                    <input type="number" class="form-control" name="retention_online" min="1" max="365" value="{$config['web']['retention_online']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">天 · 超期记录自动归档到压缩备份</span>
                </div>
                <div class="form-row">
                    <label>归档保留</label>
                    <input type="number" class="form-control" name="retention_archive" min="7" max="3650" value="{$config['web']['retention_archive']}" style="max-width:120px">
                    <span class="text-muted" style="font-size:13px;align-self:center">天 · data/archive/yyyymmdd.csv.gz,到期自动删除</span>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">保存</button></div>
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">界面皮肤 <span class="sub">访客可在页面右上角自行切换(本地记忆),此处为站点默认值</span></div>
        <div class="acard-body">
            <form action="seting.php" method="post">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="action" value="skin">
                <div class="form-row">
                    <label>默认皮肤</label>
                    <select class="form-control" name="default_skin" style="max-width:180px">
                        <option value="light"{if $config['web']['default_skin']=='light'} selected{/if}>明亮</option>
                        <option value="dark"{if $config['web']['default_skin']=='dark'} selected{/if}>暗黑</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>强调色</label>
                    <input type="color" class="form-control" name="accent" value="{if $config['web']['accent']}{$config['web']['accent']}{else}#10b981{/if}" style="max-width:80px;height:40px;padding:4px">
                    <span class="text-muted" style="font-size:13px;align-self:center">按钮/链接/日历等主色</span>
                </div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">保存</button> <button class="btn" type="submit" name="reset" value="1" onclick="localStorage.removeItem('mg_theme');return true;">恢复默认</button><span class="help-block" style="align-self:center;margin-left:10px">恢复为明亮皮肤与默认强调色(同时清除本机记忆)</span></div>
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">两步验证 <span class="sub">账号 + 密码 + 动态验证码</span></div>
        <div class="acard-body">
            {if $otp_enabled}
                <p style="margin-top:0;color:var(--accent-strong)">已开启两步验证,登录时需要输入验证器上的 6 位动态码。</p>
                <form class="inline-form" action="seting.php" method="post">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input type="hidden" name="action" value="otp">
                    <input type="hidden" name="otp_action" value="disable">
                    <input type="text" class="form-control" name="code" placeholder="输入 6 位验证码解除绑定" maxlength="6" required style="max-width:230px">
                    <button type="submit" class="btn btn-danger">解除绑定</button>
                </form>
            {elseif $otp_setup}
                <p style="margin-top:0">1. 用 Google Authenticator 等验证器扫码,或手动输入密钥:</p>
                <p><span class="otp-secret">{$otp_setup}</span></p>
                <p>2. 或添加 URI: <span class="otp-uri">{$otp_uri|escape}</span></p>
                <div id="qrcode" data-uri="{$otp_uri|escape}" style="display:none"></div>
                <p>3. 输入验证器显示的 6 位验证码完成绑定:</p>
                <form class="inline-form" action="seting.php" method="post">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input type="hidden" name="action" value="otp">
                    <input type="hidden" name="otp_action" value="enable">
                    <input type="text" class="form-control" name="code" placeholder="6 位验证码" maxlength="6" required style="max-width:160px">
                    <button type="submit" class="btn btn-primary">确认绑定</button>
                </form>
                <form action="seting.php" method="post" style="margin-top:12px">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input type="hidden" name="action" value="otp">
                    <input type="hidden" name="otp_action" value="cancel">
                    <button type="submit" class="btn btn-sm">取消</button>
                </form>
            {else}
                <p style="margin-top:0;color:var(--muted)">开启后,登录需要 账号 + 密码 + 验证器动态码 三重验证,大幅提升后台安全性。</p>
                <form action="seting.php" method="post">
                    <input type="hidden" name="_csrf" value="{$csrf}">
                    <input type="hidden" name="action" value="otp">
                    <input type="hidden" name="otp_action" value="begin">
                    <button type="submit" class="btn btn-primary">生成密钥并绑定</button>
                </form>
            {/if}
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">存储信息</div>
        <div class="acard-body">
            <table class="info-table">
                <tr><th>SQLite 数据库</th><td>{$storage['db_file']|escape} · {$storage['db_size']|escape}</td></tr>
                <tr><th>归档备份</th><td>{$storage['archive_count']} 个压缩包 · 共 {$storage['archive_size_txt']|escape} · <code>{$storage['archive_dir']|escape}</code></td></tr>
                <tr><th>cron 归档接口</th><td><code>admin/archive.php?type=cron&amp;who=鉴黄口令</code></td></tr>
            </table>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">修改管理员密码</div>
        <div class="acard-body">
            <form action="seting.php" method="post">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="hidden" name="action" value="password">
                <div class="form-row"><label>原密码</label><input type="password" class="form-control" name="oldpass" required style="max-width:280px"></div>
                <div class="form-row"><label>新密码</label><input type="password" class="form-control" name="newpass" placeholder="至少 6 位" required style="max-width:280px"></div>
                <div class="form-row"><label>确认新密码</label><input type="password" class="form-control" name="newpass2" required style="max-width:280px"></div>
                <div class="form-actions"><button class="btn btn-primary" type="submit">修改密码</button></div>
            </form>
        </div>
    </div>

    {if $otp_setup}
    <script src="../view/vendor/qrcode.min.js"></script>
    <script>
    {literal}
    (function(){
        var box=document.getElementById('qrcode');
        if (window.QRCode && box) {
            box.style.display='block';
            new QRCode(box, box.getAttribute('data-uri'));
        }
    })();
    {/literal}
    </script>
    {/if}

{include file="tpl_footer.php"}

<script>
{literal}
(function(){
    var btn=document.getElementById("test-key-btn");
    if(!btn) return;
    btn.addEventListener("click", function(){
        var out=document.getElementById("test-key-result");
        var key=document.getElementById("img_level_key").value;
        var csrf=document.querySelector("input[name=_csrf]").value;
        btn.disabled=true; btn.textContent="测试中…";
        out.textContent=""; out.className="help-block";
        fetch("moderation.php?type=testkey",{
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:"_csrf="+encodeURIComponent(csrf)+"&key="+encodeURIComponent(key)
        }).then(function(r){return r.json();}).then(function(d){
            out.textContent=d.msg;
            out.style.color=d.ok?"var(--ok)":"var(--danger)";
        }).catch(function(){ out.textContent="请求失败"; out.style.color="var(--danger)"; })
        .finally(function(){ btn.disabled=false; btn.textContent="测试可用性"; });
    });
})();
{/literal}
</script>
