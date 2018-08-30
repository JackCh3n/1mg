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
    $('li').attr('class','');
    $('#n_t').attr('class','active');
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
    $('li').attr('class','');
    $('#n_a').attr('class','active');
    $('#qita').html('<h1>关于 1mg 图床</h1><p>在法律允许范围内，请随意使用本图床。</p><h2>严禁上传及分享如下类型的图片：</h2><ul><li>含有色情、暴力、宣扬恐怖主义的图片</li><li>侵犯版权、未经授权的图片</li><li>其他违反中华人民共和国法律的图片</li><li>其他违反日本法律的图片</li></ul>');
})
$('#contact').click(function () {
    $('#uploads').hide();
    $('#qita').show();
    $('li').attr('class','');
    $('#n_c').attr('class','active');
    $('#qita').html('<h1>联系我们</h1><div class="alert alert-warning"> <p>如果是讨论技术问题或者报告bug，请最好还是新建一个issue展开讨论，以免你提出的问题石沉大海。</p> <p>点此进入<a href="https://github.com/lenyuadmin/1mg/issuess">issues列表页</a>。</p></div> </div>');
})