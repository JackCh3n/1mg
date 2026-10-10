{include file="tpl_header.php"}

    {if $msg}<div class="alert alert-success">{$msg|escape}</div>{/if}

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-num">{$pending_count}</div>
            <div class="stat-label">待审核图片</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{$checked_count}</div>
            <div class="stat-label">已鉴定图片</div>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">IP 黑名单 <span class="sub">被封禁的 IP 无法上传;审核页/日志页可一键封禁</span></div>
        <div class="acard-body">
            <table class="table">
                <thead><tr><th>IP</th><th>原因</th><th>封禁时间</th><th style="width:70px">操作</th></tr></thead>
                <tbody>
                    {foreach $ban_list as $b}
                    <tr>
                        <td>{$b['ip']|escape}</td>
                        <td class="text-muted">{$b['reason']|escape}</td>
                        <td class="text-muted">{$b['date']|escape}</td>
                        <td><form method="post" action="moderation.php?type=unban_ip" style="display:inline">
                            <input type="hidden" name="_csrf" value="{$csrf}">
                            <input type="hidden" name="ip" value="{$b['ip']|escape}">
                            <button type="submit" class="btn btn-xs btn-success">解封</button></form></td>
                    </tr>
                    {/foreach}
                    {if count($ban_list)==0}<tr><td colspan="4" class="empty-tip">黑名单为空</td></tr>{/if}
                </tbody>
            </table>
            <form method="post" action="moderation.php?type=ban_ip" class="inline-form" style="margin-top:12px">
                <input type="hidden" name="_csrf" value="{$csrf}">
                <input type="text" class="form-control" name="ip" placeholder="要封禁的 IP,例 1.2.3.4" required style="max-width:220px">
                <input type="text" class="form-control" name="reason" placeholder="原因(可选)" style="max-width:220px">
                <button type="submit" class="btn btn-danger btn-sm">封禁 IP</button>
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">
            内容审核
            <span class="sub">自动检测调用 moderatecontent 接口(每轮12张),也可人工逐张判定</span>
            <span class="head-right">
                <a class="btn btn-sm" href="moderation.php?action=auto&amp;token={$csrf}" onclick="return confirm('调用鉴黄接口检测12张待审图片?')">运行自动检测</a>
            </span>
        </div>
        <div class="acard-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px">
                {foreach $pending as $p}
                <div style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;background:var(--panel-2)">
                    <a href="../{$p['path']|escape:'url'}" target="_blank">
                        <img src="../{$p['path']|escape:'url'}" style="width:100%;height:140px;object-fit:cover;display:block" loading="lazy" alt="">
                    </a>
                    <div style="padding:8px 10px">
                        <div class="text-muted" style="font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{$p['path']|escape}">{$p['path']|escape}</div>
                        <div class="text-muted" style="font-size:11px">{$p['date']|escape}</div>
                        <div style="display:flex;gap:8px;margin-top:8px">
                            <form method="post" action="moderation.php" style="flex:1">
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="key" value="{$p['id']}">
                                <input type="hidden" name="do" value="ok">
                                <button type="submit" class="btn btn-xs btn-success" style="width:100%">正常</button>
                            </form>
                            <form method="post" action="moderation.php" style="flex:1" onsubmit="return confirm('确认删除该图片?')">
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="key" value="{$p['id']}">
                                <input type="hidden" name="do" value="adult">
                                <button type="submit" class="btn btn-xs btn-danger" style="width:100%">违规删除</button>
                            </form>
                        </div>
                    </div>
                </div>
                {/foreach}
                {if count($pending)==0}
                <div class="empty-tip" style="grid-column:1/-1">没有待审核的图片</div>
                {/if}
            </div>
            {if count($pending)>0 && $pending_count>count($pending)}
            <p class="text-muted" style="font-size:12.5px;margin-bottom:0">当前显示最新 {$pending|count} 张,共 {$pending_count} 张待审</p>
            {/if}
        </div>
    </div>

{include file="tpl_footer.php"}
