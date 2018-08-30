$(function () {
    $("#file").fileinput({
        uploadUrl: 'upload.php',
        allowedFileExtensions : ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
        overwriteInitial: false,
        maxFileSize: 5120,
        maxFilesNum: 10,
        maxFileCount: 10,

    });
    $('#file').on('fileuploaded', function(event, data, previewId, index) {
        var form = data.form, files = data.files, extra = data.extra, response = data.response, reader = data.reader;
        if(response.code == 'success') {
            if ( $("showurl").css("display") ) {
                $('#urlcode').append(response.data.url + "\n");
                $('#htmlcode').append("&lt;img src=\""+ response.data.url +"\" alt=\""+ files[index].name +"\" title=\""+ files[index].name +"\" /&gt;" + "\n");
                $('#bbcode').append("[img]"+ response.data.url +"[/img]" + "\n");
                $('#markdown').append("!["+ files[index].name +"](" + response.data.url + ")" + "\n");
                $('#markdownlinks').append("[!["+ files[index].name +"](" + response.data.url + ")]" +"(" + response.data.url + ")" + "\n");
                // $('#deletecode').append(response.data.delete + "\n");
                
            } else if (response.data.url) {
                $("#showurl").show();
                $('#urlcode').append(response.data.url + "\n");
                $('#htmlcode').append("&lt;img src=\""+ response.data.url +"\" alt=\""+ files[index].name +"\" title=\""+ files[index].name +"\" /&gt;" + "\n");
                $('#bbcode').append("[img]"+ response.data.url +"[/img]" + "\n");
                $('#markdown').append("!["+ files[index].name +"](" + response.data.url + ")" + "\n");
                $('#markdownlinks').append("[!["+ files[index].name +"](" + response.data.url + ")]" +"(" + response.data.url + ")" + "\n");
                // $('#deletecode').append(response.data.delete + "\n");
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
               $('#urlcode').append(value + "\n");
               $('#htmlcode').append("&lt;img src=\""+ value +"\" alt=\""+ data['initialPreviewConfig'][index]['caption'] +"\" title=\""+ data['initialPreviewConfig'][index]['caption'] +"\" /&gt;" + "\n");
                $('#bbcode').append("[img]"+ value +"[/img]" + "\n");
                $('#markdown').append("!["+ data['initialPreviewConfig'][index]['caption'] +"](" + value + ")" + "\n");
                $('#markdownlinks').append("[!["+ data['initialPreviewConfig'][index]['caption'] +"](" + value + ")]" +"(" + value+ ")" + "\n");
            });
            console.log(data);
        $('button.close.fileinput-remove').hide();
        console.log('q')

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
    $('#exit').click(function () {
        alert('1111')
    })

})