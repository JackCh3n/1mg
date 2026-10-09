/**
 * 前台逻辑: 上传(fileinput) + 秒传预检(SparkMD5) + 粘贴/全页拖拽上传 + 结果tabs + 内容区切换
 */
$(function () {

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
        allowedFileExtensions: ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
        overwriteInitial: false,
        maxFileSize: 5120,
        maxFilesNum: 10,
        maxFileCount: 10,
        showCaption: false,
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

    //已加载文件的md5缓存: previewId -> {md5, exists, url}
    var md5Map = {};

    //文件加入队列先算md5,服务器已有则直接展示地址并移出队列(秒传)
    $('#file').on('fileloaded', function (event, file, previewId, index, reader) {
        if (typeof SparkMD5 === 'undefined') return;
        computeFileMd5(file, function (md5) {
            $.getJSON('check.php', { md5: md5, api_token: API_TOKEN }, function (res) {
                if (res && res.code == 'success') {
                    md5Map[previewId] = { md5: md5, exists: true, url: res.data.url };
                    showResults();
                    appendUrl(res.data.url, file.name);
                    appendDelete(res.data.delete);
                    $('#' + previewId).find('.kv-file-remove').click();
                } else {
                    md5Map[previewId] = { md5: md5, exists: false };
                }
            });
        });
    });

    //兜底:上传请求发出前若秒传结果已出,取消该文件上传
    $('#file').on('filepreupload', function (event, data, previewId, index) {
        var info = md5Map[previewId];
        if (info && info.exists) {
            showResults();
            return { message: data.files[0].name + ' 已存在,秒传成功' };
        }
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

    $('#today').click(function () {
        switchTo('today');
        $('#qita').show().html('<h2>今日上传</h2><p>加载中…</p>');
        $('#sec-upload').hide();
        $.getJSON('user/today.php', function (data) {
            $("#file").fileinput('destroy').fileinput($.extend({
                uploadUrl: 'upload.php',
                uploadExtraData: function () { return { api_token: API_TOKEN }; },
                allowedFileExtensions: ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
                maxFileSize: 5120,
                showCaption: false,
                showZoom: false,
                showCancel: false,
                fileActionSettings: { showZoom: false, showDrag: false }
            }, data));
            var html = '<h2>今日上传</h2><p>今天已有 ' + data.initialPreview.length + ' 张图片,点击复制链接:</p><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px">';
            $.each(data.initialPreview, function (i, url) {
                html += '<a href="' + url + '" target="_blank"><img src="' + url + '" style="width:100%;height:110px;object-fit:cover;border-radius:10px;border:1px solid var(--border)" loading="lazy"></a>';
            });
            html += '</div><p style="margin-top:14px"><button type="button" class="btn btn-sm" id="back-upload">返回上传</button></p>';
            $('#qita').html(html);
            $('#back-upload').click(function () { location.hash = ''; location.reload(); });
        });
        return false;
    });

    $('#about').click(function () {
        switchTo('about');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<h1>关于 1mg 图床</h1><p>在法律允许范围内,请随意使用本图床。</p>' +
            '<h3>严禁上传及分享如下类型的图片:</h3><ul>' +
            '<li>含有色情、暴力、宣扬恐怖主义的图片</li>' +
            '<li>侵犯版权、未经授权的图片</li>' +
            '<li>其他违反中华人民共和国法律的图片</li></ul>' +
            '<p><button type="button" class="btn btn-sm" id="back-upload">返回上传</button></p>'
        );
        $('#back-upload').click(function () { location.hash = ''; location.reload(); });
        return false;
    });

    $('#contact').click(function () {
        switchTo('contact');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<h1>联系我们</h1><div class="callout"><p>如果是讨论技术问题或者报告 bug,请到 <a href="https://github.com/lenyuadmin/1mg/issues" target="_blank" rel="noopener">GitHub Issues</a> 提交,以免问题石沉大海。</p></div>' +
            '<p><button type="button" class="btn btn-sm" id="back-upload">返回上传</button></p>'
        );
        $('#back-upload').click(function () { location.hash = ''; location.reload(); });
        return false;
    });

    $('#tos').click(function () {
        switchTo('tos');
        $('#sec-upload').hide();
        $('#qita').show().html(
            '<h1>服务条款 Terms of Service</h1><ul>' +
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
            '<p><button type="button" class="btn btn-sm" id="back-upload">返回上传</button></p>'
        );
        $('#back-upload').click(function () { location.hash = ''; location.reload(); });
        return false;
    });
});
