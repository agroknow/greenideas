				<?php if(isset($_GET['nhm']) and ($_GET['nhm']=='MNHN' or $_GET['nhm']=='TNHM' or $_GET['nhm']=='NHMC' or $_GET['nhm']=='JME' or $_GET['nhm']=='HNHM' or $_GET['nhm']=='AC')){ ?>

				<?php } else{ ?>

<div id="page-footer" style="height:64px;">
		<ul id="nav-footer-menu">
		   <a href="www.greenideasproject.org" target="_blank"><?php echo __('Green Ideas Project website'); ?></a> - <a style="font-size: 8px; font-weight: normal;" href='http://www.bitpixels.com/' target="_blank">Website thumbnails provided by BitPixels</a><br>
           <?php echo __('Green Ideas Pathway Authoring and Metadata Tool.  &copy; 2013'); ?>
        </ul><!--end nav-footer-menu ul-->


</div><!--end page-footer div-->

				<?php } ?>
</body>
</html>