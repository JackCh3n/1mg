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
                <a class="navbar-brand" href="index.php">
                    <i class="fa fa-leaf f20 mr5"></i>
                    {$title|escape} - 后台管理系统
                </a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> {$admin_user|escape} <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="seting.php"><i class="fa fa-fw fa-gear"></i> 设置</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="logout.php"><i class="fa fa-fw fa-power-off"></i> 退出</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li{if isset($nav_active) && $nav_active=='index'} class="active"{/if}>
                        <a href="index.php"><i class="fa fa-fw fa-dashboard"></i> 首页</a>
                    </li>
                    <li{if isset($nav_active) && $nav_active=='logs'} class="active"{/if}>
                        <a href="logs.php"><i class="fa fa-fw fa-table"></i> 日志</a>
                    </li>
                    <li{if isset($nav_active) && $nav_active=='images'} class="active"{/if}>
                        <a href="images.php"><i class="fa fa-fw fa-edit"></i> 图片管理</a>
                    </li>
                    <li{if isset($nav_active) && $nav_active=='seting'} class="active"{/if}>
                        <a href="seting.php"><i class="fa fa-fw fa-wrench"></i> 网站设置</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>
