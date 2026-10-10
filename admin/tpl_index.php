{include file="tpl_header.php"}

    {if $archive_msg}<div class="alert alert-danger">{$archive_msg|escape}</div>{/if}
    {if isset($smarty.get.imported)}<div class="alert alert-success">归档导入完成: 新增 {$smarty.get.imported} 条,跳过(已存在) {$smarty.get.skipped} 条</div>{/if}
    {if $orphan_msg}<div class="alert alert-success">{$orphan_msg|escape}</div>{/if}

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
                <a class="btn btn-sm" href="archive.php?type=backup" title="下载 SQLite 数据库的 gzip 压缩备份">备份数据库</a>
                <a class="btn btn-sm" href="index.php?action=archive_run&amp;token={$csrf}" onclick="return confirm('立即执行归档?')">立即归档</a>
            </span>
        </div>
        <div class="acard-body">
            <p class="text-muted" style="margin-top:0;font-size:13px">
                上次执行: {$archive_last_run|default:'从未'} · 备份位于 data/archive/ · cron 接口: <code>admin/archive.php?type=cron&amp;who=口令</code>
                · 图片目录占用 <b>{$disk_size}</b> / {$disk_files} 个文件
            </p>
            <p style="margin-top:0"><a class="btn btn-sm" href="index.php?action=orphan&amp;token={$csrf}" onclick="return confirm('扫描并清理磁盘上没有数据库记录的孤儿图片文件?')">扫描清理孤儿文件</a></p>
            <table class="table">
                <thead>
                    <tr><th>日期</th><th>记录数</th><th>大小</th><th style="width:210px">操作</th></tr>
                </thead>
                <tbody>
                    {foreach $archive_files as $f}
                    <tr>
                        <td>{$f['yyyymmdd']}</td>
                        <td>{$f['rows']}</td>
                        <td>{format_size($f['size'])}</td>
                        <td>
                            <a class="btn btn-xs" href="archive.php?type=download&amp;file={$f['yyyymmdd']}">下载</a>
                            <a class="btn btn-xs btn-success" href="archive.php?type=import&amp;file={$f['yyyymmdd']}&amp;token={$csrf}" onclick="return confirm('把该归档中的记录导回主库?(已存在的自动跳过)')">导入</a>
                        </td>
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
