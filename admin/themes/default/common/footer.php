</div>
<div id="footer" class="group">
<?php /* ?>
    <?php admin_plugin_footer(); ?>
    <p><a href="http://omeka.org" id="omeka-logo"><?php echo __('Powered by Omeka'); ?></a> | <a href="http://omeka.org/codex/"><?php echo __('Documentation'); ?></a> | <a href="http://omeka.org/forums/"><?php echo __('Support Forums'); ?></a></p>
    <p id="system-info">
     <?php echo __('Version %s', OMEKA_VERSION); ?> 
    <?php if (get_option('display_system_info') 
           && has_permission('SystemInfo', 'index')): ?>
        | <a href="<?php echo html_escape(uri('system-info')); ?>"><?php echo __('More information about your system'); ?></a>
    <?php endif; ?>
    </p>
 <?php */ ?>
    <div id="page-footer" style="height:64px; width: 100%; text-align: center;">
		   <a href="javascript:void(0);" target="_blank"><?php echo __('Green Ideas Project website'); ?></a> - <a style="font-size: 8px; font-weight: normal;" href='http://www.bitpixels.com/' target="_blank">Website thumbnails provided by BitPixels</a><br>
           <?php echo __('Green Ideas Pathway Authoring and Metadata Tool.  &copy; 2013'); ?>

</div><!--end page-footer div-->

</div>
</div>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45635533-1', 'greenideasproject.org');
  ga('send', 'pageview');

</script>
</body>
</html>
