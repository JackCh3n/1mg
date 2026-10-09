{include file="tpl_header.php"}
{include file="tpl_navbar.php"}

                <div class="main-wrap">
                {if $save_error}<div class="alert alert-danger">{$save_error|escape}</div>{/if}
                {if $save_ok}<div class="alert alert-success">{$save_ok|escape}</div>{/if}
                {if $otp_error}<div class="alert alert-danger">{$otp_error|escape}</div>{/if}

                    <div class="panel panel-default">
                        <div class="panel-heading">网站设置</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="site">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网站标题</label>
                                        <div class="col-sm-10"><input type="text" class="form-control" name="title" value="{$config['web']['title']|escape}"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网址</label>
                                        <div class="col-sm-10"><input type="text" class="form-control" name="server" value="{$config['web']['server']|escape}"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">cdn域名</label>
                                        <div class="col-sm-10"><input type="text" class="form-control" name="cdn" value="{$config['web']['cdn']|escape}"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄Key</label>
                                        <div class="col-sm-10"><input type="text" class="form-control" name="key" value="{$config['web']['img_level_key']|escape}"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄口令</label>
                                        <div class="col-sm-10"><input type="text" class="form-control" name="pass" value="{$config['web']['img_level_pass']|escape}"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="king-btn king-info mr10" type="submit"><i class="fa fa-save btn-icon"></i>保存</button>
                                            <a class="king-btn king-default" href="index.php"><i class="fa fa-mail-reply-all btn-icon"></i>返回</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">数据保留策略(在线 → 归档 → 清理)</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="policy">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">在线保留</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="retention_online" min="1" max="365" value="{$config['web']['retention_online']}">
                                                <span class="input-group-addon">天(主库,超期自动归档)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">归档保留</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="retention_archive" min="7" max="3650" value="{$config['web']['retention_archive']}">
                                                <span class="input-group-addon">天(压缩备份 data/archive/yyyymmdd.csv.gz,到期删除)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10"><button class="king-btn king-info" type="submit"><i class="fa fa-save btn-icon"></i>保存</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">界面皮肤</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="skin">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">默认皮肤</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="default_skin">
                                                <option value="light"{if $config['web']['default_skin']=='light'} selected{/if}>明亮</option>
                                                <option value="dark"{if $config['web']['default_skin']=='dark'} selected{/if}>暗黑</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">强调色</label>
                                        <div class="col-sm-10">
                                            <input type="color" class="form-control" name="accent" value="{$config['web']['accent']|default:'#10b981'}" style="height:38px;padding:4px">
                                            <p class="help-block">访客可在页面右上角自行切换明暗皮肤(本地记忆),此处设置站点默认值与强调色</p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10"><button class="king-btn king-info" type="submit"><i class="fa fa-save btn-icon"></i>保存</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">两步验证(账号 + 密码 + 动态验证码)</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                            {if $otp_enabled}
                                <p class="text-success"><i class="fa fa-shield"></i> 已开启两步验证。登录时需要输入验证器上的6位动态码。</p>
                                <form class="form-inline mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="otp">
                                    <input type="hidden" name="otp_action" value="disable">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="code" placeholder="输入6位验证码" maxlength="6" required>
                                    </div>
                                    <button type="submit" class="king-btn king-danger">解除绑定</button>
                                </form>
                            {elseif $otp_setup}
                                <p>1. 用 Google Authenticator 等验证器扫描二维码,或手动输入密钥:</p>
                                <p><code style="font-size:16px;letter-spacing:2px">{$otp_setup}</code></p>
                                <p style="word-break:break-all">2. 或添加URI: <code>{$otp_uri|escape}</code></p>
                                <div id="qrcode"></div>
                                <p>3. 输入验证器显示的6位验证码完成绑定:</p>
                                <form class="form-inline mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="otp">
                                    <input type="hidden" name="otp_action" value="enable">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="code" placeholder="6位验证码" maxlength="6" required>
                                    </div>
                                    <button type="submit" class="king-btn king-info">确认绑定</button>
                                </form>
                                <form class="mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="otp">
                                    <input type="hidden" name="otp_action" value="cancel">
                                    <button type="submit" class="king-btn king-default">取消</button>
                                </form>
                            {else}
                                <p>开启后,登录需要 账号+密码+验证器动态码 三重验证。</p>
                                <form action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="otp">
                                    <input type="hidden" name="otp_action" value="begin">
                                    <button type="submit" class="king-btn king-info">生成密钥并绑定</button>
                                </form>
                            {/if}
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">存储信息</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                                <table class="table table-bordered">
                                    <tr><th width="180">SQLite 数据库</th><td>{$storage['db_file']|escape} ({$storage['db_size']|escape})</td></tr>
                                    <tr><th>归档备份</th><td>{$storage['archive_count']} 个压缩包,共 {$storage['archive_size_txt']|escape} ({$storage['archive_dir']|escape})</td></tr>
                                    <tr><th>cron 归档接口</th><td>admin/archive.php?type=cron&amp;who=图片鉴黄口令</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">修改管理员密码</div>
                        <div class="panel-body">
                            <div class="col-sm-8">
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="password">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">原密码</label>
                                        <div class="col-sm-10"><input type="password" class="form-control" name="oldpass" required></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">新密码</label>
                                        <div class="col-sm-10"><input type="password" class="form-control" name="newpass" placeholder="至少6位" required></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">确认新密码</label>
                                        <div class="col-sm-10"><input type="password" class="form-control" name="newpass2" required></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10"><button class="king-btn king-info" type="submit"><i class="fa fa-key btn-icon"></i>修改密码</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
{if $otp_setup}
<div id="qrcode" data-uri="{$otp_uri|escape}" style="display:none"></div>
{/if}
{include file="tpl_footer.php"}
{if $otp_setup}
<script src="https://cdnjs.loli.net/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
