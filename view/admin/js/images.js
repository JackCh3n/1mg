
$(function () {
    $("#file").fileinput({
        uploadUrl: 'upload.php',
        allowedFileExtensions : ['jpeg', 'jpg', 'png', 'gif', 'bmp'],
        overwriteInitial: false,
        maxFileSize: 5120,
        maxFilesNum: 10,
        maxFileCount: 10,

    });
    $.getJSON('images.php?type=json',function (data) {
        $("#file").fileinput('destroy').fileinput(data);
        console.log(data);

    $('button.close.fileinput-remove').hide();
    console.log('q')

    });
})