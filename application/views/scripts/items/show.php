
<?php head(array('title' => item('Dublin Core', 'Title'), 'bodyid'=>'items','bodyclass' => 'show')); ?>

<?php 
require_once 'Omeka/Core.php';
$core = new Omeka_Core;

try {
    $db = $core->getDb();
	$db->query("SET NAMES 'utf8'");
   
    //Force the Zend_Db to make the connection and catch connection errors
    try {
        $mysqli = $db->getConnection()->getConnection();
    } catch (Exception $e) {
        throw new Exception("<h1>MySQL connection error: [" . mysqli_connect_errno() . "]</h1>" . "<p>" . $e->getMessage() . '</p>');
    }
} catch (Exception $e) {
	die($e->getMessage() . '<p>Please refer to <a href="http://omeka.org/codex/">Omeka documentation</a> for help.</p>');
}
?>
<div id="page-body" class="two_column">

<?php require_once('./application/views/scripts/common/menu.php'); ?>

<div class="column" id="column-d">
    <?php 
    if ($_GET["eidteaser"]){
    $eidteaser = preg_replace("/[^0-9]/", "", $_GET['eidteaser']);
    echo returntoexhibitfromitem($eidteaser);
    }
    ?>
    
	<h1 class="section_title"><?php echo item('Dublin Core', 'Title'); ?></h1>
    
    <?php //echo custom_show_item_metadata(); ?>
	
	<!-- The following returns all of the files associated with an item. -->
	<div id="itemfiles" class="element">
	    <?php echo '<br><h2 style="color:#90A886; font-size:13px;">'.__('Access the Resource').':</h2>'; ?>
		<div class="element-text"><?php //echo display_files_for_item(); ?></div>
        
        <?php
		$sql="SELECT * FROM metadata_record WHERE object_id=".$item->id." and object_type='item'";
		$execrecord=$db->query($sql);
		$datarecord=$execrecord->fetch();
		
		$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=32";
$exec5=$db->query($sql);
$data51=$exec5->fetch();

$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=34";
$exec5=$db->query($sql);
$data52=$exec5->fetch();

$uri=WEB_ROOT;	
$sql="SELECT a.* FROM metadata_element_value a join metadata_element_hierarchy b ON b.id=a.element_hierarchy 
		WHERE a.record_id=".$datarecord['id']." and a.element_hierarchy=33";
$exec5=$db->query($sql);
$dataformat=$exec5->fetch();
if($dataformat['vocabulary_record_id']>0){
$sql2 = "SELECT * FROM metadata_vocabulary_record WHERE id=" . $dataformat['vocabulary_record_id'] . " ";
$exec2 = $db->query($sql2);
$dataformatfromvoc = $exec2->fetch();
}
			if(item_has_files($item) and $item->item_type_id==6 and !stripos($data51['value'],".emf")>0 and !stripos($data51['value'],".tif")>0 and !stripos($data51['value'],".tiff")>0){
				
				echo '<a href="'.$data51['value'].'"  class="lightview"><img src="'.$data51['value'].'" style=" max-width:500px;"/></a><br>';
				
				 } elseif($item->item_type_id==11 or $item->item_type_id==20 or $item->item_type_id==6){ 
		   ?>
		   <div style="float:left; margin-top:10px;">
		   <?php
		   if(stripos($data51['value'],".jpg")>0 or stripos($data51['value'],".gif")>0 or stripos($data51['value'],".jpeg")>0 or stripos($data51['value'],".png")>0 or stripos($data51['value'],".bmp")>0 or stripos($data51['value'],"content/thumbs/src")>0 or $dataformatfromvoc['value']=="IMAGE"){
		   
		   if(stripos($data51['value'],"artpast.org/oaipmh/getimage")>0){
		   
		   echo '<a href="http://europeanastatic.eu/api/image?type=IMAGE&uri='.$data51['value'].'"  class="lightview"><img src="http://europeanastatic.eu/api/image?type=IMAGE&uri='.$data51['value'].'" style=" max-width:400px;"/></a><br>';
		   
		   }else{
		   
		   echo '<a href="'.$data51['value'].'"  class="lightview"><img src="'.$data51['value'].'" style=" max-width:400px;"/></a><br>';
		   
		   }
		   
		   }elseif(stripos($data51['value'],".pdf")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('application/views/scripts/images/files-icons/pdf.png').'"/></a><br>';
		   
		   }elseif(stripos($data51['value'],".tiff")>0 or stripos($data51['value'],".tif")>0){ 
		   
		   //http://education.natural-europe.eu/natural_europe/custom/phpThumb/phpThumb.php?src=/natural_europe/archive/files/riekko-ansasta2_72a2f5e439.tif&w=135
		   echo '<a href="'.$uri.'/custom/phpThumb/phpThumb.php?src='.$data51['value'].'"  class="lightview"><img src="'.$uri.'/custom/phpThumb/phpThumb.php?src='.$data51['value'].'"  style=" max-width:500px; max-height:400px;"/></a><br>';
		   
		   }elseif(stripos($data51['value'],".doc")>0 or stripos($data51['value'],"docx")>0  or stripos($dataformatfromvoc['value'],"word")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('application/views/scripts/images/files-icons/word.png').'" /></a><br>';
		   
		   }elseif(stripos($data51['value'],".ppt")>0 or stripos($data51['value'],".pptx")>0 or stripos($data51['value'],".pps")>0 or stripos($dataformatfromvoc['value'],"powerpoint")>0){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="'.uri('application/views/scripts/images/files-icons/powerpoint.png').'" /></a><br>';
		   
		   }elseif(stripos($data51['value'],".emf")>0 ){
		   
		   echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   	
		   }elseif(stripos($data51['value'],".html")>0 or stripos($data51['value'],".htm")>0 or stripos($data51['value'],".asp")>0 or stripos($dataformatfromvoc['value'],"HTML")>0 or stripos($dataformatfromvoc['value'],"Html")>0 or $dataformatfromvoc['value']=='html' or $dataformatfromvoc['value']=='html/text' or $dataformatfromvoc['value']=='text/html' or $dataformatfromvoc['value']=='HTML'){
		  
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url='.$data51['value'].'"/></a><br>';
		   //echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   } else{ 
		   
		   echo '<a href="'.$data51['value'].'" target="_blank"><img src="http://img.bitpixels.com/getthumbnail?code=29089&size=200&url='.$data51['value'].'"/></a><br>';
		   	//echo '<a href="'.$data51['value'].'" target="_blank">'.$data51['value'].'</a><br>';
		   
		   }
	   		echo "<br>";
		   
		  
		  
		  ?>
          </div>
          <div style="float:left; margin-left:15px;">
          <?php if(stripos($data52['value'],"europeana")>0){ ?>
          <?php echo '<a href="'.$data52['value'].'" target="_blank"><img src="'.uri("admin/themes/default/items/images/europeana-logo-en.png").'" style="max-width:150px;"></a>'; ?>
          <?php } ?>
          </div>
          <br style="clear:both;">
          <?php } ?>
	</div>
    
    <h2 style="color:#90A886; font-size:13px;"><?php echo __('Given Metadata'); ?>:</h2> 


<?php
show_metadata_info($item->id,'item',$_SESSION['get_language']);

?>
	
	<!-- If the item belongs to a collection, the following creates a link to that collection. 
	<?php //if (item_belongs_to_collection()): ?>
    <div id="collection" class="element">
        <h3>Collection</h3>
        <div class="element-text"><p><?php// echo link_to_collection_for_item(); ?></p></div>
    </div>
    <?php //endif; ?> -->

    <!-- The following prints a list of all tags associated with the item -->
	<!--<?php //if (item_has_tags()): ?>
	<div id="item-tags" class="element">
		<h3>Tags</h3>
		<div class="element-text"><?php //echo item_tags_as_string(); ?></div> 
	</div>
	<?php //endif;?> -->
	
	<!-- The following prints a citation for this item. -->
	<!--<div id="item-citation" class="element">
    	<h3>Citation</h3>
    	<div class="element-text"><?php //echo item_citation(); ?></div>
	</div> -->
	
	<?php echo plugin_append_to_items_show(); ?>

	<!--<ul class="item-pagination navigation">
	    <li id="previous-item" class="previous"><?php //echo link_to_previous_item('Previous Item'); ?></li>
	    <li id="next-item" class="next"><?php //echo link_to_next_item('Next Item'); ?></li>
	</ul> -->



	
</div>		
<div class="clear"></div><!--clear DIV NEEDS TO BE ADDED TO ALL TEMPLATES-->
</div><!--end page-body div-->
<div class="clear"></div><!--clear DIV NEEDS TO BE ADDED TO ALL TEMPLATES-->
</div><!--end page-container div-->

<?php foot(); ?>

<?php /*   original code from omeka
<?php head(array('title' => item('Dublin Core', 'Title'), 'bodyid'=>'items','bodyclass' => 'show')); ?>

<div id="primary">

    <h1><?php echo item('Dublin Core', 'Title'); ?></h1>

    <?php echo custom_show_item_metadata(); ?>

    <!-- The following returns all of the files associated with an item. -->
    <div id="itemfiles" class="element">
        <h3><?php echo __('Files'); ?></h3>
        <div class="element-text"><?php echo display_files_for_item(); ?></div>
    </div>

    <!-- If the item belongs to a collection, the following creates a link to that collection. -->
    <?php if (item_belongs_to_collection()): ?>
    <div id="collection" class="element">
        <h3><?php echo __('Collection'); ?></h3>
        <div class="element-text"><p><?php echo link_to_collection_for_item(); ?></p></div>
    </div>
    <?php endif; ?>

    <!-- The following prints a list of all tags associated with the item -->
    <?php if (item_has_tags()): ?>
    <div id="item-tags" class="element">
        <h3><?php echo __('Tags'); ?></h3>
        <div class="element-text"><?php echo item_tags_as_string(); ?></div>
    </div>
    <?php endif;?>

    <!-- The following prints a citation for this item. -->
    <div id="item-citation" class="element">
        <h3><?php echo __('Citation'); ?></h3>
        <div class="element-text"><?php echo item_citation(); ?></div>
    </div>

    <?php echo plugin_append_to_items_show(); ?>

    <ul class="item-pagination navigation">
        <li id="previous-item" class="previous"><?php echo link_to_previous_item(); ?></li>
        <li id="next-item" class="next"><?php echo link_to_next_item(); ?></li>
    </ul>

</div><!-- end primary -->

<?php foot(); ?>
 * 
 */?>
