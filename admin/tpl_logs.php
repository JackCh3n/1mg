{include file="tpl_header.php"}
{include file="tpl_navbar.php"}
<div class="container-fluid">
                <div class="row page-header-box">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            表格
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
                        <form role="form">
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">操作人员</div>
                                                <input class="form-control" type="email">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">           
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">标题内容</div>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                                    
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">操作邮箱</div>
                                                <input class="form-control" type="email">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">密码类型</div>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">操作结果</div>
                                                <input class="form-control" type="text">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-sm-6 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon">操作类型</div>
                                                <input type="text" class="form-control">
                                            </div>
                                        </div>
                            </div>
                            <div class="col-lg-12">
                                <hr class="mt5 mb15">
                                <button type="submit" class="king-btn king-info">查询</button>    
                                <button type="submit" class="king-btn king-success">重置</button> 
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
                                        <th>IP</th>
                                        <th>UA</th>
                                        <th>预览</th>
                                        <th>SIZE</th>
                                        <th>图片等级</th>
                                        <th>已经删除</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $data['logs'] as $key => $value}
                                    <tr>
                                        <th>{$value['id']}</th>
                                        <th>{$value['ip']}</th>
                                        <th>{$value['ua']}</th>
                                        <th><a href="../{$value['path']}">{$value['path']}</a> </th>
                                        <th>{$value['size']}</th>
                                        <th>{$value['level']}</th>
                                        <th>{$value['see']}</th>
                                    </tr>
                                    {/foreach}
                                    <tfoot>
                                    <tr>
                                    <td colspan="7">
                                    <div class="pagination-info pull-left">共有{$data['count']}条，每页显示：50条</div>
                                    <div class="pull-right king-page-box">
                                        <ul class="pagination pagination-small pull-right">
                                          <li class="disabled"><a>«</a></li>
                                          <li class="active"><a>1</a></li>
                                          <li><a href="javascript:void(0);">2</a></li>
                                          <li><a href="javascript:void(0);">3</a></li>
                                          <li><a href="javascript:void(0);">4</a></li>
                                          <li><a href="javascript:void(0);">»</a></li>
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