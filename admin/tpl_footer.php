        </main>
    </div><!-- /.admin-main -->

    {if isset($page_jscode)}
    	{$page_jscode}
    {/if}

    {if isset($page_js)}
    	<script src="../view/admin/js/{$page_js}.js" type="text/javascript"></script>
    {/if}
<script>
(function(){
    var btn=document.getElementById('mobile-nav-btn'), menu=document.getElementById('mobile-nav-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function(e){ e.stopPropagation(); menu.classList.toggle('open'); });
    document.addEventListener('click', function(e){ if (!menu.contains(e.target)) menu.classList.remove('open'); });
})();
</script>
</body>

</html>
