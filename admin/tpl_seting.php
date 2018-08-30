{include file="tpl_header.php"}
{include file="tpl_navbar.php"}

<!-- Page Heading -->


                <div class="main-wrap">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            网站设置
                        </div>
                        <div class="panel-body">  
                            <div class="col-sm-8">
                                
                                <form class="form-horizontal mt15" id="user_form" action="seting.php" method="post">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网站标题</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="1mg" id="title" name="title" value="{$config['web']['title']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">网址</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="https://abc.cn/" id="server" name="server" value="{$config['web']['server']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">cdn域名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="https://abc.cn/" id="cdn" name="cdn" value="{$config['web']['cdn']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄Key</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="https://abc.cn/" id="key" name="key" value="{$config['web']['img_level_key']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">图片鉴黄口令</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="详情请查看博客" id="pass" name="pass" value="{$config['web']['img_level_pass']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库地址</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="127.0.0.1" id="db_server" name="db_server" value="{$config['db']['server']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库端口</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="3306" id="db_port" name="db_port" value="{$config['db']['port']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——用户名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="root" id="db_user" name="db_user" value="{$config['db']['username']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——密码</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="pass" id="db_pass" name="db_pass" value="{$config['db']['password']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库——库名</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="name" id="db_pass" name="db_pass" value="{$config['db']['database_name']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库—编码</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="utf8" id="db_charset" name="db_charset" value="{$config['db']['charset']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">数据库—类型</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="mysql" id="db_type" name="db_type" value="{$config['db']['database_type']}">
                                            <span class="king-required-tip text-danger ml5">*</span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="king-btn king-info mr10" title="保存" type="submit">
                                                <i class="fa fa-save btn-icon"></i>保存
                                            </button>
                                            <a class="king-btn king-default" title="返回">
                                                <i class="fa fa-mail-reply-all btn-icon"></i>返回
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    
                </div>
{include file="tpl_footer.php"}