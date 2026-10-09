

    </div>
    <!-- /#wrapper -->

	<!-- 如果要使用Bootstrap的js插件，必须先调入jQuery -->
	<script src="https://cdnjs.loli.net/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
	<!-- 包括所有bootstrap的js插件或者可以根据需要使用的js插件调用　-->
	<script src="https://cdnjs.loli.net/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

    {if isset($page_js)}
    	<script src="../view/admin/js/{$page_js}.js" type="text/javascript"></script>
    {/if}

    {if isset($page_jscode)}
    	{$page_jscode}
    {/if}

</body>

</html>
