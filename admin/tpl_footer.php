
        

        

    </div>
    <!-- /#wrapper -->

	<!-- 如果要使用Bootstrap的js插件，必须先调入jQuery -->
	<script src="https://cdnjs.loli.net/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<!-- 包括所有bootstrap的js插件或者可以根据需要使用的js插件调用　-->
	<script src="https://o.qcloud.com/static_api/v3/assets/bootstrap-3.3.4/js/bootstrap.min.js"></script>

    <!-- 包括所有kendoui的js插件或者可以根据需要使用的js插件调用　-->
    <script src="https://o.qcloud.com/static_api/v3/assets/kendoui-2015.2.624/js/kendo.all.min.js"></script>
    
    {if isset($page_js)}
    	<script src="../view/admin/js/{$page_js}.js" type="text/javascript"></script>
    {/if}

    {if isset($page_jscode)}
    	{$page_jscode}
    {/if}

</body>

</html>
