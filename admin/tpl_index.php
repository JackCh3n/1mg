{include file="tpl_header.php"}

    {if $archive_msg}<div class="alert alert-danger">{$archive_msg|escape}</div>{/if}

    <!-- 统计卡片 -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-num">{$data['today_upload']}</div>
            <div class="stat-label">今日上传</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{$data['week_upload']}</div>
            <div class="stat-label">近 7 天</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{$data['online_total']}</div>
            <div class="stat-label">在线记录(主库)</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{$archive_total}</div>
            <div class="stat-label">已归档记录</div>
        </div>
    </div>

    <!-- 活跃日历 -->
    <div class="acard">
        <div class="acard-head">活跃日历 <span class="sub">最近一年每日上传 · 颜色跟随站点皮肤</span></div>
        <div class="acard-body">
            <div id="calendar" class="calendar-box" data-json='{$calendar_json}'></div>
        </div>
    </div>

    <!-- 归档管理 -->
    <div class="acard">
        <div class="acard-head">
            数据归档
            <span class="sub">在线保留 {$retention_online} 天 → 归档备份 {$retention_archive} 天 → 到期删除</span>
            <span class="head-right">
                <a class="btn btn-sm" href="index.php?action=archive_run&amp;token={$csrf}" onclick="return confirm('立即执行归档?')">立即归档</a>
            </span>
        </div>
        <div class="acard-body">
            <p class="text-muted" style="margin-top:0;font-size:13px">上次执行: {$archive_last_run|default:'从未'} · 备份位于 data/archive/ · cron 接口: <code>admin/archive.php?type=cron&amp;who=口令</code></p>
            <table class="table">
                <thead>
                    <tr><th>日期</th><th>记录数</th><th>大小</th><th style="width:130px">下载</th></tr>
                </thead>
                <tbody>
                    {foreach $archive_files as $f}
                    <tr>
                        <td>{$f['yyyymmdd']}</td>
                        <td>{$f['rows']}</td>
                        <td>{format_size($f['size'])}</td>
                        <td><a class="btn btn-xs" href="archive.php?type=download&amp;file={$f['yyyymmdd']}">下载 .csv.gz</a></td>
                    </tr>
                    {/foreach}
                    {if count($archive_files)==0}
                    <tr><td colspan="4" class="empty-tip">暂无归档 · 在线记录都在保留期内</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

{include file="tpl_footer.php"}
