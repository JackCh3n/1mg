$(function () {
    //已加载文件的md5缓存: previewId -> {md5, exists, url}
    var md5Map = {};

    function appendUrl(url, name) {
        name = name || 'image';
        $('#urlcode').append(url + "\n");
        $('#htmlcode').append("&lt;img src=\""+ url +"\" alt=\""+ name +"\" title=\""+ name +"\" /&gt;" + "\n");
        $('#bbcode').append("[img]"+ url +"[/img]" + "\n");
        $('#markdown').append("!["+ name +"](" + url + ")" + "\n");
        $('#markdownlinks').append("[!["+ name +"](" + url + ")]" +"(" + url + ")" + "\n");
    }

    //分块计算文件md5(用于秒传预检),SparkMD5加载失败时跳过,不影响正常上传
    function computeFileMd5(file, callback) {
        if (typeof SparkMD5 === 'undefined' || !window.FileReader || !file) {
            return;
        }
        var blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
            chunkSize = 2097152,
            chunks = Math.ceil(file.size / chunkSize),
            currentChunk = 0,
            spark = new SparkMD5.ArrayBuffer(),
            fileReader = new FileReader();
        fileReader.onload = function (e) {
            spark.append(e.target.result);
            currentChunk++;
            if (currentChunk < chunks) {
                loadNext();
            } else {
                callback(spark.end());
            }
        };
        fileReader.onerror = function () {
            //读取出错就放弃秒传预检,走正常上传
        };
        function loadNext() {
            var start = currentChunk * chunkSize,
                end = start + chunkSize >= file.size ? file.size : start + chunkSize;
            fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
        }
        loadNext();
    }

    $("#file").fileinput({
        uploadUrl: 'upload.php',
        allowedFileExtensions : ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
        overwriteInitial: false,
        maxFileSize: 5120,
        maxFilesNum: 10,
        maxFileCount: 10,
    });

    //文件加入队列后先算md5,若服务器已有同一文件则直接展示地址并从队列移除(秒传)
    $('#file').on('fileloaded', function(event, file, previewId, index, reader) {
        if (typeof SparkMD5 === 'undefined') {
            return;
        }
        computeFileMd5(file, function(md5) {
            $.getJSON('check.php', {md5: md5}, function (res) {
                if (res && res.code == 'success') {
                    md5Map[previewId] = {md5: md5, exists: true, url: res.data.url};
                    $('#showurl').show();
                    appendUrl(res.data.url, file.name);
                    //从待上传列表中移除,无需再传
                    $('#' + previewId).find('.kv-file-remove').click();
                } else {
                    md5Map[previewId] = {md5: md5, exists: false};
                }
            });
        });
    });

    //兜底:上传请求发出前若秒传结果已出,直接取消该文件的上传
    $('#file').on('filepreupload', function(event, data, previewId, index) {
        var info = md5Map[previewId];
        if (info && info.exists) {
            $('#showurl').show();
            return {message: data.files[0].name + ' 已存在,秒传成功'};
        }
    });

    $('#file').on('fileuploaded', function(event, data, previewId, index) {
        var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
        if(response.code == 'success') {
            //原代码 $("showurl") 少了 # 号,选择器永远无效,导致判断走错分支
            if ( $("#showurl").css("display") != 'none' ) {
                appendUrl(response.data.url, files[index] ? files[index].name : 'image');
            } else {
                $("#showurl").show();
                appendUrl(response.data.url, files[index] ? files[index].name : 'image');
            }
        }
    });
    $('#today').click(function () {
        $('#qita').hide();
        $('#uploads').show();
        $('li').removeClass('active');
        $('#n_t').addClass('active');
        $.getJSON('user/today.php',function (data) {
            $("#file").fileinput('destroy').fileinput(data);
            $("#showurl").show();
            $.each(data['initialPreview'],function (index,value) {
               appendUrl(value, data['initialPreviewConfig'][index]['caption']);
            });
        $('button.close.fileinput-remove').hide();

        });
    });
    $('#about').click(function () {
        $('#uploads').hide();
        $('#qita').show();
        $('li').removeClass('active');
        $('#n_a').addClass('active');
        $('#qita').html('<h1>关于 1mg 图床</h1><p>在法律允许范围内，请随意使用本图床。</p><h2>严禁上传及分享如下类型的图片：</h2><ul><li>含有色情、暴力、宣扬恐怖主义的图片</li><li>侵犯版权、未经授权的图片</li><li>其他违反中华人民共和国法律的图片</li><li>其他违反日本法律的图片</li></ul>');
    });
    $('#contact').click(function () {
        $('#uploads').hide();
        $('#qita').show();
        $('li').removeClass('active');
        $('#n_c').addClass('active');
        $('#qita').html('<h1>联系我们</h1><div class="alert alert-warning"> <p>如果是讨论技术问题或者报告bug，请最好还是新建一个issue展开讨论，以免你提出的问题石沉大海。</p> <p>点此进入<a href="https://github.com/lenyuadmin/1mg/issuess">issues列表页</a>。</p></div> </div>');
    });
    $('#n_s').click(function () {
        $('#uploads').hide();
        $('#qita').show();
        $('li').removeClass('active');
        $('#n_s').addClass('active');
        $('#qita').html('<div class="c24 center-box"> <div class="header default-margin-bottom"> <h2>服务条款 Terms of Service</h2> </div> <div class="text-content"> <ul> <li>在不违反当地法律法规的情况下，请随意使用本图床服务.</li><h3>以下类型的图片均不允许上传至本网站:</h3><p></p><li>侵权的图片, 包括侵犯个人私隐、企业版权等;</li><li>含有成人內容/擦边/偷拍/过分裸露情节的图片;</li> <li>煽动暴力、宣扬宗教、种族主义、种族仇恨等;</li> <li>含有恐怖、血腥场面的图片;</li><li>含有VPN相关信息的图片;</li> <li>含有违规信息的二维码图片;</li> <li>含有色情网站水印标记的图片;</li> <li>其他非法图片(包括但不限于赌博、电脑病毒、木马、诈骗、假冒药品等非法行为);</li> <li>违反中华人民共和国法律法规的图片;</li> <li>违反美国、加拿大、欧盟法律法规的图片;</li> <li>违反新加坡、中国台湾、中国香港等国家或地区法律法规的图片;</li> <li>管理员有权删除我们认为不合适的图片.</li> <li>我们会保留随时变更或修改服务条款部份或全部內容的权利.</li> </ul></div> </div>');
    });
    var user_gravatar=$.cookie('user_gravatar');
    if (user_gravatar!=null) {
        $('#gravatar').show();
        $('.dropdown').show();
        $('#gravatar').attr('src','https://gravatar.loli.net/avatar/'+user_gravatar);
    }
})
