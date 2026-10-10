/**
 * 图片管理页: 上传控件 + 最近上传网格(带删除)
 */
$(function () {
    //无Bootstrap环境:fileinput的缩放预览依赖$.fn.modal,给空实现防止崩溃(缩放功能已禁用)
    if (!$.fn.modal) { $.fn.modal = function () { return this; }; }
    var csrf = $('#recent-box').attr('data-csrf');

    //上传控件
    $("#file").fileinput({
        uploadUrl: '../upload.php',
        allowedFileExtensions: ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
        overwriteInitial: false,
        maxFileSize: 5120,
        maxFilesNum: 10,
        maxFileCount: 10,
        showBrowse: true,
        browseLabel: '选择或拖拽图片',
        showRemove: true,
        showUpload: true,
        showCaption: false,
        autoOrientImage: false,
        showZoom: false,
        showCancel: false,
        fileActionSettings: { showZoom: false, showDrag: false }
    });

    function loadRecent() {
        $.getJSON('images.php?type=json', function (data) {
            var box = $('#recent-box');
            box.empty();
            if (!data.initialPreview || !data.initialPreview.length) {
                box.html('<div class="empty-tip">暂无图片</div>');
                return;
            }
            var grid = $('<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px"></div>');
            $.each(data.initialPreview, function (i, url) {
                var cfg = data.initialPreviewConfig[i] || {};
                var item = $('<div class="img-item" style="border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--panel-2)"></div>');
                item.append('<a href="' + url + '" target="_blank"><img src="' + url + '" style="width:100%;height:110px;object-fit:cover;display:block" loading="lazy"></a>');
                var foot = $('<div style="padding:7px 9px;display:flex;align-items:center;gap:6px"></div>');
                foot.append('<span class="text-muted" style="font-size:11px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + (cfg.caption || '') + '">' + (cfg.caption || '') + '</span>');
                var del = $('<button type="button" class="btn btn-xs btn-danger">删除</button>');
                del.attr('data-key', cfg.key).attr('data-file', cfg.caption || '');
                foot.append(del);
                item.append(foot);
                grid.append(item);
            });
            box.append(grid);
        });
    }

    //删除(软删,带CSRF)
    $(document).on('click', '#recent-box .btn-danger', function () {
        var btn = $(this);
        if (!confirm('确认删除图片 ' + (btn.attr('data-file') || '') + ' ?')) return;
        $.post('images.php?type=del', { key: btn.attr('data-key'), _csrf: csrf }, function (res) {
            if (res && res.code == 'success') {
                btn.closest('.img-item').fadeOut(150, function () { $(this).remove(); });
            } else {
                alert((res && res.error) || '删除失败');
            }
        }, 'json');
    });

    loadRecent();
});
