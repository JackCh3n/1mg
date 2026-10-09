{include file="tpl_header.php"}

    <div class="acard">
        <div class="acard-head">筛选 <span class="sub">参数化查询,支持按 IP 检索</span></div>
        <div class="acard-body">
            <form method="get" action="logs.php" class="inline-form">
                <input class="form-control" type="text" name="ip" value="{$data['search_ip']|escape}" placeholder="按 IP 搜索,例: 127.0.0.1" style="max-width:260px">
                <button type="submit" class="btn btn-primary btn-sm">查询</button>
                <a href="logs.php" class="btn btn-sm">重置</a>
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">上传记录 <span class="sub">仅显示在线保留期内的数据,更早的请到「仪表盘 → 数据归档」下载</span></div>
        <div class="acard-body" style="overflow-x:auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th><th>时间</th><th>IP</th><th style="min-width:180px">UA</th><th>预览</th><th>大小</th><th>状态</th><th style="width:150px">操作</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $data['logs'] as $value}
                    <tr>
                        <td>{$value['id']}</td>
                        <td style="white-space:nowrap">{$value['date']|escape}</td>
                        <td>{$value['ip']|escape}</td>
                        <td style="max-width:220px;word-break:break-all" class="text-muted">{$value['ua']|escape}</td>
                        <td><a href="../{$value['path']|escape:'url'}" target="_blank">{$value['path']|escape}</a></td>
                        <td>{format_size((int)$value['size'])}</td>
                        <td>{if $value['see']}<span class="label label-success">正常</span>{else}<span class="label label-danger">已删除</span>{/if}</td>
                        <td>
                            <form method="post" action="images.php?type=del" style="display:inline" onsubmit="return confirm('确认删除该图片?')">
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="key" value="{$value['id']}">
                                <button type="submit" class="btn btn-xs btn-danger"{if !$value['see']} disabled{/if}>删除</button>
                            </form>
                            <form method="post" action="images.php?type=restore" style="display:inline">
                                <input type="hidden" name="_csrf" value="{$csrf}">
                                <input type="hidden" name="key" value="{$value['id']}">
                                <button type="submit" class="btn btn-xs btn-success"{if $value['see']} disabled{/if}>恢复</button>
                            </form>
                        </td>
                    </tr>
                    {/foreach}
                    {if count($data['logs'])==0}
                    <tr><td colspan="8" class="empty-tip">没有记录</td></tr>
                    {/if}
                </tbody>
            </table>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:10px">
                <span class="pager-info">共 {$data['count']} 条 · 第 {$data['page']} / {$data['total_pages']} 页</span>
                <div class="pager">
                    <a href="logs.php?page=1{if $data['search_ip']}&amp;ip={$data['search_ip']|escape:'url'}{/if}">«</a>
                    {for $p=max(1,$data['page']-4) to min($data['total_pages'],$data['page']+4)}
                        {if $p==$data['page']}<span class="current">{$p}</span>{else}<a href="logs.php?page={$p}{if $data['search_ip']}&amp;ip={$data['search_ip']|escape:'url'}{/if}">{$p}</a>{/if}
                    {/for}
                    <a href="logs.php?page={$data['total_pages']}{if $data['search_ip']}&amp;ip={$data['search_ip']|escape:'url'}{/if}">»</a>
                </div>
            </div>
        </div>
    </div>

{include file="tpl_footer.php"}
