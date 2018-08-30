{include file="tpl_header.php"}
{include file="tpl_navbar.php"}

<div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row page-header-box">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            概述
                        </h1>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row data-panel">
                    <div class="col-lg-3 col-md-6">
                        <div class="king-widget2">
                            <div class="king-widget-content p20 bg-info">
                                <div class="king-counter king-counter-lg">
                                    <div class="king-counter-label text-uppercase f16">今日上传</div>
                                    <div class="king-counter-number-group">
                                        <span class="king-counter-number white">{$data['today_upload']}</span>
                                        <span class="king-counter-icon ml10 white">
                                            <i class="fa fa-image"></i>
                                        </span>
                                    </div>
                                    <div class="king-counter-label text-uppercase f16">Images</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="king-widget2">
                            <div class="king-widget-content p20 bg-info">
                                <div class="king-counter king-counter-lg">
                                    <div class="king-counter-label text-uppercase f16">删除非法</div>
                                    <div class="king-counter-number-group">
                                        <span class="king-counter-number white">{$data['today_illegal']}</span>
                                        <span class="king-counter-icon ml10 white">
                                            <i class="fa fa-image"></i>
                                        </span>
                                    </div>
                                    <div class="king-counter-label text-uppercase f16">Images</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="king-widget2">
                            <div class="king-widget-content p20 bg-info">
                                <div class="king-counter king-counter-lg">
                                    <div class="king-counter-label text-uppercase f16">总共上传</div>
                                    <div class="king-counter-number-group">
                                        <span class="king-counter-number white">{$data['all_upload']}</span>
                                        <span class="king-counter-icon ml10 white">
                                            <i class="fa fa-image"></i>
                                        </span>
                                    </div>
                                    <div class="king-counter-label text-uppercase f16">Images</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="king-widget2">
                            <div class="king-widget-content p20 bg-info">
                                <div class="king-counter king-counter-lg">
                                    <div class="king-counter-label text-uppercase f16">删除非法</div>
                                    <div class="king-counter-number-group">
                                        <span class="king-counter-number white">{$data['all_illegal']}</span>
                                        <span class="king-counter-icon ml10 white">
                                            <i class="fa fa-image"></i>
                                        </span>
                                    </div>
                                    <div class="king-counter-label text-uppercase f16">Images</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.row -->

                <div class="row charts">
                    <div class="col-md-6">
                        <div class="king-block king-block-bordered mb30">
                            <div class="king-block-header">
                                <h3 class="king-block-title">数据</h3>
                            </div>
                            <div class="king-block-content">
                                <div class="chart" id="chartA"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="king-block king-block-bordered mb30">
                            <div class="king-block-header">
                                <h3 class="king-block-title">数据</h3>
                            </div>
                            <div class="king-block-content">
                                <div class="chart" id="chartC"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

{include file="tpl_footer.php"}