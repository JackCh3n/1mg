    //声明必要的变量
    var fileReader = new FileReader(), md5box = document.getElementById('md5box');
    //文件分割方法（注意兼容性）
    blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
        file = document.getElementById("filemd5").files[0],
        
        //文件每块分割2M，计算分割详情
        chunkSize = 2097152,
        chunks = Math.ceil(file.size / chunkSize),
        currentChunk = 0,
 
        //创建md5对象（基于SparkMD5）
        spark = new SparkMD5();  
    var filename = document.getElementById("filemd5").files[0].name;
    var filesize = document.getElementById("filemd5").files[0].size/1024;
    filesize = filesize.toFixed(2);
        //每块文件读取完毕之后的处理
    fileReader.onload = function (e) {
        // console.log("读取文件", currentChunk + 1, "/", chunks);
        //每块交由sparkMD5进行计算
        spark.appendBinary(e.target.result);
        currentChunk++;
 
        //如果文件处理完成计算MD5，如果还有分片继续处理
        if (currentChunk < chunks) {
            loadNext();
        } else {
            // 前台显示Hash
            // md5box.innerHTML = '文件：' + filename +'('+filesize+'KB)<div class="alert alert-success">MD5 Hash<h3 style="color:red;">' + spark.end()+"</h3></div>";
        }
    };
        //处理单片文件的上传
    function loadNext() {
        var start = currentChunk * chunkSize, end = start + chunkSize >= file.size ? file.size : start + chunkSize;
 
        fileReader.readAsBinaryString(blobSlice.call(file, start, end));
    }
 
    loadNext();