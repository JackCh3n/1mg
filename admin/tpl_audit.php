{include file="tpl_header.php"}

    <div class="acard">
        <div class="acard-head">管理员操作审计 <span class="sub">记录所有后台敏感操作</span></div>
        <div class="acard-body" style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr><th>ID</th><th>时间</th><th>操作人</th><th>动作</th><th>对象</th><th>IP</th></tr>
                </thead>
                <tbody>
                    {foreach $logs as $l}
                    <tr>
                        <td>{$l['id']}</td>
                        <td style="white-space:nowrap">{$l['date']|escape}</td>
                        <td>{$l['username']|escape}</td>
                        <td>{$l['action']|escape}</td>
                        <td style="max-width:320px;word-break:break-all" class="text-muted">{$l['target']|escape}</td>
                        <td>{$l['ip']|escape}</td>
                    </tr>
                    {/foreach}
                    {if count($logs)==0}
                    <tr><td colspan="6" class="empty-tip">暂无记录</td></tr>
                    {/if}
                </tbody>
            </table>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:10px">
                <span class="pager-info">共 {$count} 条 · 第 {$page} / {$total_pages} 页</span>
                <div class="pager">
                    <a href="audit.php?page=1">«</a>
                    {for $p=max(1,$page-4) to min($total_pages,$page+4)}
                        {if $p==$page}<span class="current">{$p}</span>{else}<a href="audit.php?page={$p}">{$p}</a>{/if}
                    {/for}
                    <a href="audit.php?page={$total_pages}">»</a>
                </div>
            </div>
        </div>
    </div>

{include file="tpl_footer.php"}
