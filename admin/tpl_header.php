<!DOCTYPE html>
<html lang="zh-CN" data-default-skin="{$default_skin}" data-default-accent="{$default_accent}">
<head>
	<meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title|escape} - 后台管理</title>
    <link href="../view/site.css" rel="stylesheet">
    <link href="../view/admin/admin.css" rel="stylesheet">
    <script src="../view/theme.js"></script>
</head>
<body class="admin-body">
    <aside class="side">
        <div class="side-logo">
            <span class="logo-dot"></span> {$title|escape} <small>ADMIN</small>
        </div>
        <nav class="side-nav">
            <a href="index.php"{if isset($nav_active) && $nav_active=='index'} class="active"{/if}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                仪表盘
            </a>
            <a href="logs.php"{if isset($nav_active) && $nav_active=='logs'} class="active"{/if}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
                上传日志
            </a>
            <a href="images.php"{if isset($nav_active) && $nav_active=='images'} class="active"{/if}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.8" cy="8.8" r="1.8"/><path d="M21 15.5l-4.6-4.6a1.6 1.6 0 0 0-2.3 0L4 21"/></svg>
                图片管理
            </a>
            <a href="seting.php"{if isset($nav_active) && $nav_active=='seting'} class="active"{/if}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.03 1.56V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1.11-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.56-1.03H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.56-1.11 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.08a1.7 1.7 0 0 0 1.03-1.56V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.08a1.7 1.7 0 0 0 1.56 1.03H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.56 1.03z"/></svg>
                网站设置
            </a>
        </nav>
        <div class="side-foot">
            <span title="{$admin_user|escape}">{$admin_user|escape}</span>
            <a href="logout.php" title="退出登录">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                退出
            </a>
        </div>
    </aside>
    <div class="admin-main">
        <header class="topbar">
            {assign var="_titles" value=['index'=>'仪表盘','logs'=>'上传日志','images'=>'图片管理','seting'=>'网站设置']}
            <h1 class="page-title">{if isset($_titles[$nav_active])}{$_titles[$nav_active]}{/if}</h1>
            <div class="topbar-right">
                <span class="who">{$admin_user|escape}</span>
                <button type="button" class="theme-toggle" data-mg-theme-toggle title="切换明暗皮肤">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2.5M12 19.5V22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2 12h2.5M19.5 12H22M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></svg>
                </button>
            </div>
        </header>
        <main class="content">
