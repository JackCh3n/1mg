{include file="tpl_header.php"}
{include file="tpl_navbar.php"}
<div class="container-fluid">
                <div class="row page-header-box">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            上传日志
                        </h1>
                    </div>
                </div>
                <!-- Page Heading -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        查询条件
                    </div>
                    <div class='panel-body'>
                        <div class="row">
                        <form role="form" method="get" action="logs.php">
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">按IP搜索</div>
                                                <input class="form-control" type="text" name="ip" value="{$data['search_ip']|escape}" placeholder="例: 127.0.0.1">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-lg-12">
                                <hr class="mt5 mb15">
                                <button type="submit" class="king-btn king-info">查询</button>
                                <a href="logs.php" class="king-btn king-success">重置</a>
                            </div>

                            </form>
                        </div>
                    </div>
                </div>
                <!---->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        所有记录
                    </div>
                    <div class='panel-body'>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>时间</th>
                                        <th>IP</th>
                                        <th>UA</th>
                                        <th>预览</th>
                                        <th>SIZE</th>
                                        <th>图片等级</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $data['logs'] as $key => $value}
                                    <tr>
                                        <td>{$value['id']}</td>
                                        <td>{$value['date']|escape}</td>
                                        <td>{$value['ip']|escape}</td>
                                        <td style="max-width:220px;word-break:break-all">{$value['ua']|escape}</td>
                                        <td><a href="../{$value['path']|escape:'url'}">{$value['path']|escape}</a> </td>
                                        <td>{$value['size']|escape}</td>
                                        <td>{$value['level']}</td>
                                        <td>{if $value['see']}<span class="label label-success">正常</span>{else}<span class="label label-danger">已删除</span>{/if}</td>
                                        <td>
                                            <form method="post" action="images.php?type=del" style="display:inline" onsubmit="return confirm('确认删除该图片?')">
                                                <input type="hidden" name="_csrf" value="{$csrf}">
                                                <input type="hidden" name="key" value="{$value['id']}">
                                                <button type="submit" class="btn btn-danger btn-xs"{if !$value['see']} disabled{/if}>删除</button>
                                            </form>
                                            <form method="post" action="images.php?type=restore" style="display:inline">
                                                <input type="hidden" name="_csrf" value="{$csrf}">
                                                <input type="hidden" name="key" value="{$value['id']}">
                                                <button type="submit" class="btn btn-success btn-xs"{if $value['see']} disabled{/if}>恢复</button>
                                            </form>
                                        </td>
                                    </tr>
                                    {/foreach}
                                    {if count($data['logs'])==0}
                                    <tr><td colspan="9" class="text-center text-muted">没有记录</td></tr>
                                    {/if}
                                    <tfoot>
                                    <tr>
                                    <td colspan="9">
                                    <div class="pagination-info pull-left">共有{$data['count']}条，每页显示：50条，第 {$data['page']}/{$data['total_pages']} 页</div>
                                    <div class="pull-right king-page-box">
                                        <ul class="pagination pagination-small pull-right">
                                          <li{if $data['page']<=1} class="disabled"{/if}><a href="logs.php?page=1{if $data['search_ip']}&ip={$data['search_ip']|escape:'url'}{/if}">«</a></li>
                                          {for $p=max(1,$data['page']-4) to min($data['total_pages'],$data['page']+4)}
                                          <li{if $p==$data['page']} class="active"{/if}><a href="logs.php?page={$p}{if $data['search_ip']}&ip={$data['search_ip']|escape:'url'}{/if}">{$p}</a></li>
                                          {/for}
                                          <li{if $data['page']>=$data['total_pages']} class="disabled"{/if}><a href="logs.php?page={$data['total_pages']}{if $data['search_ip']}&ip={$data['search_ip']|escape:'url'}{/if}">»</a></li>
                                        </ul>
                                    </div>
                                    </td>
                                    </tr>
                                    </tfoot>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.container-fluid -->

{include file="tpl_footer.php"}
