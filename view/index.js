/**
 * 前台逻辑: 上传(fileinput) + 秒传预检(SparkMD5) + 粘贴/全页拖拽上传 + 结果tabs + 内容区切换
 */
$(function () {
    //无Bootstrap环境:fileinput的缩放预览依赖$.fn.modal,给空实现防止崩溃(缩放功能已禁用)
    if (!$.fn.modal) { $.fn.modal = function () { return this; }; }

    /* ---------- 结果 tabs(原生) ---------- */
    $('#result-tabs').on('click', 'button', function () {
        $('#result-tabs button').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        $('#pane-' + $(this).attr('data-tab')).addClass('active');
    });

    function appendUrl(url, name) {
        name = name || 'image';
        $('#urlcode').append(url + "\n");
        $('#htmlcode').append("&lt;img src=\"" + url + "\" alt=\"" + name + "\" title=\"" + name + "\" /&gt;" + "\n");
        $('#bbcode').append("[img]" + url + "[/img]" + "\n");
        $('#markdown').append("![" + name + "](" + url + ")" + "\n");
        $('#markdownlinks').append("[![" + name + "](" + url + ")](" + url + ")" + "\n");
    }
    function appendDelete(deleteUrl) {
        if (!deleteUrl) return;
        $('#deletecode').append(deleteUrl + "\n");
    }
    function showResults() {
        $('#showurl').show();
    }

    //API令牌(后台开启接口鉴权时随上传/预检携带)
    var API_TOKEN = $('#file').attr('data-api-token') || '';
    //后台可配的上传限制
    var MAX_SIZE = parseInt($('#file').attr('data-max-size'), 10) || 5;
    var MAX_COUNT = parseInt($('#file').attr('data-max-count'), 10) || 10;

    /* ---------- 分块计算md5(秒传预检),SparkMD5加载失败时自动跳过 ---------- */
    function computeFileMd5(file, callback) {
        if (typeof SparkMD5 === 'undefined' || !window.FileReader || !file) return;
        var blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
            chunkSize = 2097152,
            chunks = Math.ceil(file.size / chunkSize),
            currentChunk = 0,
            spark = new SparkMD5.ArrayBuffer(),
            fileReader = new FileReader();
        fileReader.onload = function (e) {
            spark.append(e.target.result);
            currentChunk++;
            if (currentChunk < chunks) { loadNext(); } else { callback(spark.end()); }
        };
        fileReader.onerror = function () { /*放弃预检,走正常上传*/ };
        function loadNext() {
            var start = currentChunk * chunkSize,
                end = start + chunkSize >= file.size ? file.size : start + chunkSize;
            fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
        }
        loadNext();
    }

    /* ---------- 上传控件 ---------- */
    $("#file").fileinput({
        uploadUrl: 'upload.php',
        uploadExtraData: function () { return { api_token: API_TOKEN }; },
        allowedFileExtensions: ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp'],
        browseLabel: '选择图片',
        removeLabel: '清除',
        uploadLabel: '开始上传',
        cancelLabel: '取消',
        dropZoneTitle: '把图片拖拽到这里,或点击选择',
        dropZoneClickTitle: '',
        overwriteInitial: false,
        maxFileSize: MAX_SIZE * 1024,
        maxFilesNum: MAX_COUNT,
        maxFileCount: MAX_COUNT,
        showCaption: false,
        autoOrientImage: false,
        showZoom: false,
        showCancel: false,
        fileActionSettings: { showZoom: false, showDrag: false }
    });

    /* ---------- 粘贴上传 + 全页拖拽 ---------- */
    //把File对象塞进fileinput队列
    function addFiles(files) {
        if (!files || !files.length || typeof DataTransfer === 'undefined') return;
        var dt = new DataTransfer();
        for (var i = 0; i < files.length; i++) {
            if (/^image\//.test(files[i].type)) dt.items.add(files[i]);
        }
        if (!dt.files.length) return;
        var input = document.getElementById('file');
        input.files = dt.files;
        $(input).trigger('change');
    }

    //Ctrl+V 粘贴截图直接上传
    document.addEventListener('paste', function (e) {
        if (!e.clipboardData || !e.clipboardData.items) return;
        var files = [];
        for (var i = 0; i < e.clipboardData.items.length; i++) {
            var item = e.clipboardData.items[i];
            if (item.kind === 'file' && /^image\//.test(item.type)) {
                var f = item.getAsFile();
                if (f) {
                    //剪贴板图片常没有文件名,补一个
                    if (!f.name) {
                        f = new File([f], 'paste-' + Date.now() + '-' + i + '.' + (f.type.split('/')[1] || 'png'), { type: f.type });
                    }
                    files.push(f);
                }
            }
        }
        if (files.length) addFiles(files);
    });

    //拖拽图片到页面任意位置上传(拖到上传控件内时交给fileinput原生处理)
    var dragDepth = 0;
    document.addEventListener('dragenter', function (e) {
        if (e.dataTransfer && e.dataTransfer.types && Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') !== -1) {
            dragDepth++;
            document.body.classList.add('drag-hint');
        }
    });
    document.addEventListener('dragleave', function () {
        if (dragDepth > 0 && --dragDepth === 0) document.body.classList.remove('drag-hint');
    });
    document.addEventListener('dragover', function (e) { e.preventDefault(); });
    document.addEventListener('drop', function (e) {
        dragDepth = 0;
        document.body.classList.remove('drag-hint');
        if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) return;
        if ($(e.target).closest('.file-input').length) return;
        e.preventDefault();
        addFiles(e.dataTransfer.files);
    });

    //秒传状态: previewId -> {md5, state: idle|checking|new|exists, url}
    //选择文件时只算md5(不发请求);秒传预检推迟到点击上传时执行
    var md5Map = {};

    $('#file').on('fileloaded', function (event, file, previewId, index, reader) {
        md5Map[previewId] = { md5: '', state: 'idle', url: '' };
        if (typeof SparkMD5 === 'undefined') return;
        computeFileMd5(file, function (md5) {
            if (md5Map[previewId]) md5Map[previewId].md5 = md5;
        });
    });

    $('#file').on('filepreupload', function (event, data, previewId, index) {
        var info = md5Map[previewId];
        var fname = data.files[0] ? data.files[0].name : 'image';
        if (!info) return;                       //无状态记录,直接放行
        if (info.state === 'new') return;        //已确认服务器没有,正常上传
        if (info.state === 'exists') {           //已命中秒传,跳过传输
            showResults();
            return { message: fname + ' 已存在,秒传成功' };
        }
        if (info.state === 'checking') {         //上一轮检测还没完成
            return { message: fname + ' 正在秒传检测…' };
        }
        //idle: 先中止本次上传,异步检测后自动恢复
        info.state = 'checking';
        var proceed = function () {
            $.getJSON('check.php', { md5: info.md5, api_token: API_TOKEN })
                .done(function (res) {
                    if (res && res.code == 'success') {
                        info.state = 'exists';
                        info.url = res.data.url;
                        showResults();
                        appendUrl(res.data.url, fname);
                        appendDelete(res.data.delete);
                        $('#' + previewId).find('.kv-file-remove').click();
                        //秒传成功:收起进度条,把红色中止告警转成绿色成功提示
                        $('.file-input .kv-upload-progress').hide();
                        setTimeout(function () {
                            $('.file-input .alert-danger').each(function () {
                                if (this.textContent.indexOf('已存在,秒传成功') > -1) {
                                    this.classList.remove('alert-danger');
                                    this.classList.add('alert-success');
                                }
                            });
                        }, 0);
                    } else {
                        resume();
                    }
                })
                .fail(function () { resume(); });
        };
        var resume = function () {
            //检测到服务器没有此文件,恢复上传(状态new后preupload放行,不会递归)
            if (md5Map[previewId] === info) info.state = 'new';
            $('#file').fileinput('upload');
        };
        //md5未算好或检测异常时,3秒兜底恢复上传
        var guard = setTimeout(function () {
            if (info.state === 'checking') resume();
        }, 3000);
        var origResume = resume;
        resume = function () { clearTimeout(guard); origResume(); };
        if (info.md5) { proceed(); }
        else {
            computeFileMd5(data.files[0], function (md5) {
                info.md5 = md5;
                proceed();
            });
        }
        return { message: fname + ' 正在秒传检测…' };
    });

    $('#file').on('fileuploaded', function (event, data, previewId, index) {
        var response = data.response, files = data.files;
        if (response.code == 'success') {
            showResults();
            appendUrl(response.data.url, files[index] ? files[index].name : 'image');
            appendDelete(response.data.delete);
        }
    });

    /* ---------- 内容区切换 ---------- */
    function switchTo(nav) {
        $('#nav-links a').removeClass('active');
        $('#nav-links a[data-nav="' + nav + '"]').addClass('active');
    }
    function backBtn() {
        return '<p style="margin-top:16px"><button type="button" class="btn btn-sm" id="back-upload">返回上传</button></p>';
    }
    function bindBack() {
        $('#back-upload').click(function () { location.hash = ''; location.reload(); });
    }
    //复制链接按钮(事件委托,动态内容也可用)
    $(document).on('click', '.copy-url', function () {
        var btn = this, url = this.getAttribute('data-url');
        function done() {
            btn.textContent = '已复制 ✓';
            setTimeout(function () { btn.textContent = '复制链接'; }, 1500);
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done, function () { prompt('复制链接:', url); });
        } else {
            prompt('复制链接:', url);
        }
    });

    $('#today').click(function () {
        switchTo('today');
        $('#qita').show().html('<div class="card"><h2 style="margin:0 0 12px">今日上传</h2><p class="text-muted">加载中…</p></div>');
        $('#sec-upload').hide();
        $.getJSON('user/today.php', function (data) {
            $("#file").fileinput('destroy').fileinput($.extend({
                uploadUrl: 'upload.php',
                uploadExtraData: function () { return { api_token: API_TOKEN }; },
                allowedFileExtensions: ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp'],
                browseLabel: '选择图片',
                removeLabel: '清除',
                uploadLabel: '开始上传',
                cancelLabel: '取消',
                dropZoneTitle: '把图片拖拽到这里,或点击选择',
                dropZoneClickTitle: '',
                maxFileSize: MAX_SIZE * 1024,
                showCaption: false,
        autoOrientImage: false,
                showZoom: false,
                showCancel: false,
                fileActionSettings: { showZoom: false, showDrag: false }
            }, data));
            var n = (data.initialPreview || []).length;
            var html = '<div class="card"><h2 style="margin:0 0 12px">今日上传</h2>';
            if (!n) {
                html += '<p class="text-muted" style="margin:18px 0">今天还没有图片,上传第一张吧。</p>' + backBtn();
            } else {
                html += '<p class="text-muted" style="margin:0 0 14px">今天已有 <b>' + n + '</b> 张图片,点击图片在新窗口查看,或直接复制链接:</p>';
                html += '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px">';
                $.each(data.initialPreview, function (i, url) {
                    var name = (data.initialPreviewConfig[i] && data.initialPreviewConfig[i].caption) || ('image-' + (i + 1));
                    html += '<div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--panel-2)">'
                        + '<a href="' + url + '" target="_blank"><img src="' + url + '" style="width:100%;height:110px;object-fit:cover;display:block" loading="lazy" alt=""></a>'
                        + '<div style="padding:7px 9px"><button type="button" class="btn btn-xs copy-url" data-url="' + url + '" style="width:100%">复制链接</button></div>'
                        + '</div>';
                });
                html += '</div>' + backBtn();
            }
            html += '</div>';
            $('#qita').html(html);
            bindBack();
        });
        return false;
    });

    $('#about').click(function () {
        switchTo('about');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<div class="card"><h1 style="margin:0 0 14px">关于 1mg 图床</h1>' +
            '<p>在法律允许范围内,请随意使用本图床。</p>' +
            '<h3>严禁上传及分享如下类型的图片:</h3><ul>' +
            '<li>含有色情、暴力、宣扬恐怖主义的图片</li>' +
            '<li>侵犯版权、未经授权的图片</li>' +
            '<li>其他违反中华人民共和国法律的图片</li></ul>' +
            backBtn() + '</div>'
        );
        bindBack();
        return false;
    });

    $('#contact').click(function () {
        switchTo('contact');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<div class="card"><h1 style="margin:0 0 14px">联系我们</h1>' +
            '<div class="callout"><p style="margin:0">如果是讨论技术问题或者报告 bug,请到 <a href="https://github.com/lenyuadmin/1mg/issues" target="_blank" rel="noopener">GitHub Issues</a> 提交,以免问题石沉大海。</p></div>' +
            backBtn() + '</div>'
        );
        bindBack();
        return false;
    });

    $('#tos').click(function () {
        switchTo('tos');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<div class="card"><h1 style="margin:0 0 14px">服务条款 Terms of Service</h1><ul>' +
            '<li>在不违反当地法律法规的情况下,请随意使用本图床服务.</li>' +
            '<li>侵权的图片, 包括侵犯个人私隐、企业版权等;</li>' +
            '<li>含有成人內容/擦边/偷拍/过分裸露情节的图片;</li>' +
            '<li>煽动暴力、宣扬宗教、种族主义、种族仇恨等;</li>' +
            '<li>含有恐怖、血腥场面的图片;</li>' +
            '<li>含有 VPN 相关信息的图片;</li>' +
            '<li>含有违规信息的二维码图片;</li>' +
            '<li>含有色情网站水印标记的图片;</li>' +
            '<li>其他非法图片(包括但不限于赌博、电脑病毒、木马、诈骗、假冒药品等非法行为);</li>' +
            '<li>违反中华人民共和国法律法规的图片;</li>' +
            '<li>管理员有权删除我们认为不合适的图片.</li>' +
            '<li>我们会保留随时变更或修改服务条款部份或全部內容的权利.</li></ul>' +
            backBtn() + '</div>'
        );
        bindBack();
        return false;
    });
});
