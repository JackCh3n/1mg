{include file="tpl_header.php"}
{include file="tpl_navbar.php"}

<div class="main-wrap">
    {if $archive_msg}<div class="alert alert-danger">{$archive_msg|escape}</div>{/if}

    <!-- 统计卡片 -->
    <div class="row stat-row">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-num">{$data['today_upload']}</div>
                <div class="stat-label">今日上传</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-num">{$data['week_upload']}</div>
                <div class="stat-label">近 7 天</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-num">{$data['online_total']}</div>
                <div class="stat-label">在线记录(主库)</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-num">{$archive_total}</div>
                <div class="stat-label">已归档记录</div>
            </div>
        </div>
    </div>

    <!-- 活跃日历 -->
    <div class="panel panel-default">
        <div class="panel-heading">活跃日历 · 最近一年上传</div>
        <div class="panel-body">
            <div id="calendar" class="calendar-box" data-json='{$calendar_json}' data-total="共 {$data['online_total']} 条在线记录"></div>
        </div>
    </div>

    <!-- 归档管理 -->
    <div class="panel panel-default">
        <div class="panel-heading">
            数据归档
            <span class="text-muted ml10">在线保留 {$retention_online} 天 → 归档备份 {$retention_archive} 天 → 到期删除</span>
            <form method="get" action="index.php" style="display:inline;float:right">
                <input type="hidden" name="action" value="archive_run">
                <input type="hidden" name="token" value="{$csrf}">
                <button type="submit" class="king-btn king-info btn-sm" onclick="return confirm('立即执行归档?')">立即归档</button>
            </form>
        </div>
        <div class="panel-body">
            <p class="text-muted">上次执行: {$archive_last_run|default:'从未'} · 归档文件存放于 data/archive/ · cron 接口: archive.php?type=cron&amp;who=口令</p>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr><th>日期</th><th>记录数</th><th>大小</th><th>下载</th></tr>
                    </thead>
                    <tbody>
                        {foreach $archive_files as $f}
                        <tr>
                            <td>{$f['yyyymmdd']}</td>
                            <td>{$f['rows']}</td>
                            <td>{format_size($f['size'])}</td>
                            <td><a class="king-btn king-default btn-sm" href="archive.php?type=download&amp;file={$f['yyyymmdd']}">下载 .csv.gz</a></td>
                        </tr>
                        {/foreach}
                        {if count($archive_files)==0}
                        <tr><td colspan="4" class="text-center text-muted">暂无归档(在线记录都在保留期内)</td></tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{include file="tpl_footer.php"}
