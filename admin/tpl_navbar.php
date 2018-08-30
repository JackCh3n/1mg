</head>

<body>

    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">
                    <i class="fa fa-leaf f20 mr5"></i>
                    {$title} - 后台管理系统
                </a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu message-dropdown">
                        <li class="message-preview">
                            <a href="javascript:;">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="../view/admin/img/50x50.png" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading"><strong>qcloud</strong>
                                        </h5>
                                        <p class="small text-muted">Yesterday at 4:32 PM</p>
                                        <p>当前有一条未读的短消息...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-footer">
                            <a href="javascript:;">查看所有消息</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu alert-dropdown">
                        <li>
                            <a href="javascript:;"><span class="label label-default">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li>
                            <a href="javascript:;"><span class="label label-primary">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li>
                            <a href="javascript:;"><span class="label label-success">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li>
                            <a href="javascript:;"><span class="label label-info">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li>
                            <a href="javascript:;"><span class="label label-warning">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li>
                            <a href="javascript:;"><span class="label label-danger">&nbsp;&nbsp;提醒内容&nbsp;&nbsp;</span></a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:;" class='text-center'>查看所有提醒</a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> admin <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:;"><i class="fa fa-fw fa-user"></i> 用户</a>
                        </li>
                        <li>
                            <a href="javascript:;"><i class="fa fa-fw fa-envelope"></i> 消息盒</a>
                        </li>
                        <li>
                            <a href="javascript:;"><i class="fa fa-fw fa-gear"></i> 设置</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:;"><i class="fa fa-fw fa-power-off"></i> 退出</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <!-- <li class="active"> -->
                    <li>
                        <a href="index.php"><i class="fa fa-fw fa-dashboard"></i> 首页</a>
                    </li>
                    <li>
                        <a href="logs.php"><i class="fa fa-fw fa-table"></i> 日志</a>
                    </li>
                    <li>
                        <a href="images.php"><i class="fa fa-fw fa-edit"></i> 图片管理</a>
                    </li>
                    <li>
                        <a href="seting.php"><i class="fa fa-fw fa-wrench"></i> 网站设置</a>
                    </li>

            </div>
            <!-- /.navbar-collapse -->
        </nav>