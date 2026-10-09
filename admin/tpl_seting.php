{include file="tpl_header.php"}
{include file="tpl_navbar.php"}

<!-- Page Heading -->


                <div class="main-wrap">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            网站设置
                        </div>
                        <div class="panel-body">
                        {if $save_error}
                            <div class="alert alert-danger">{$save_error|escape}</div>
                        {/if}
                        {if $save_ok}
                            <div class="alert alert-success">{$save_ok|escape}</div>
                        {/if}
                            <div class="col-sm-8">

                                <form class="form-horizontal mt15" id="user_form" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="site">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网站标题</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="1mg" id="title" name="title" value="{$config['web']['title']|escape}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网址</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="https://abc.cn" id="server" name="server" value="{$config['web']['server']|escape}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">cdn域名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="https://abc.cn/" id="cdn" name="cdn" value="{$config['web']['cdn']|escape}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄Key</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="图片鉴黄Key" id="key" name="key" value="{$config['web']['img_level_key']|escape}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄口令</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="详情请查看博客" id="pass" name="pass" value="{$config['web']['img_level_pass']|escape}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="king-btn king-info mr10" title="保存" type="submit">
                                                <i class="fa fa-save btn-icon"></i>保存
                                            </button>
                                            <a class="king-btn king-default" title="返回" href="index.php">
                                                <i class="fa fa-mail-reply-all btn-icon"></i>返回
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <hr>
                                <h4>数据库设置</h4>
                                <p class="text-muted">修改后会先测试连接,连接成功才会保存。</p>
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="db">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库地址</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="127.0.0.1" id="db_server" name="db_server" value="{$config['db']['server']|escape}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库端口</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="3306" id="db_port" name="db_port" value="{$config['db']['port']|escape}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——用户名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="root" id="db_user" name="db_user" value="{$config['db']['username']|escape}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——密码</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" placeholder="pass" id="db_pass" name="db_pass" value="{$config['db']['password']|escape}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——库名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="tu" id="db_name" name="db_name" value="{$config['db']['database_name']|escape}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库—编码</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="db_charset" name="db_charset">
                                                {foreach ['utf8','utf8mb4','gbk','latin1'] as $cs}
                                                <option value="{$cs}"{if $config['db']['charset']==$cs} selected{/if}>{$cs}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="king-btn king-info mr10" title="保存" type="submit">
                                                <i class="fa fa-save btn-icon"></i>保存
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <hr>
                                <h4>修改管理员密码</h4>
                                <form class="form-horizontal mt15" action="seting.php" method="post">
                                    <input type="hidden" name="_csrf" value="{$csrf}">
                                    <input type="hidden" name="action" value="password">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">原密码</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" name="oldpass" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">新密码</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" name="newpass" placeholder="至少6位" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">确认新密码</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" name="newpass2" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="king-btn king-info mr10" title="修改密码" type="submit">
                                                <i class="fa fa-key btn-icon"></i>修改密码
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>
{include file="tpl_footer.php"}
