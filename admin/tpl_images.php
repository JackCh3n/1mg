{include file="tpl_header.php"}

    <div class="acard">
        <div class="acard-head">上传图片 <span class="sub">支持 jpg / png / gif / bmp · 自动压缩 · 秒传去重</span></div>
        <div class="acard-body">
            <form enctype="multipart/form-data">
                <input id="file" type="file" multiple class="mg-upload" data-overwrite-initial="false" data-min-file-count="1" data-max-file-count="{$config['web']['max_files']}" data-max-size="{$config['web']['max_size_mb']}" data-max-count="{$config['web']['max_files']}" name="file" accept="image/*">
            </form>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head">最近上传 <span class="sub">点击缩略图上的删除按钮可下线图片</span></div>
        <div class="acard-body" id="recent-box" data-csrf="{$csrf}">
            <div class="empty-tip" id="recent-loading">加载中…</div>
        </div>
    </div>

{include file="tpl_footer.php"}
