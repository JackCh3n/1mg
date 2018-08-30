{include file="tpl_header.php"}
    <link href="../view/bootstrap-fileinput-4.4.9/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
    <!-- <script src="https://cdnjs.loli.net/ajax/libs/jquery/2.1.4/jquery.min.js"></script> -->
    
    <!-- <script src="https://cdnjs.loli.net/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script> -->
{include file="tpl_navbar.php"}
<div class="container-fluid">
                <div class="row page-header-box">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            表格
                        </h1>
                    </div>
                </div>
<form enctype="multipart/form-data">
    <div class="form-group">
        <input id="file" type="file" multiple class="file" data-overwrite-initial="false" data-min-file-count="1" data-max-file-count="10" name="file" accept="image/*">
    </div>
</form>

            </div>
            <!-- /.container-fluid -->

{include file="tpl_footer.php"}