<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.document
==============================================================================
*/

// Language files that should be included
$language_file = array('document', 'slideshow');

// setting the help
$help_content = 'documentslideshow';

// including the global Dokeos file
require '../inc/global.inc.php';

// including additional libraries
require_once 'slideshow.inc.php';
require_once api_get_path(LIBRARY_PATH) . 'document.lib.php';

// variable initialisation
$noPHP_SELF = true;
$path = Security::remove_XSS($_GET['curdirpath']);
$pathurl = urlencode($path);
$slide_id = Security::remove_XSS($_GET['slide_id']);
if ($path <> '/') {
	$folder = $path.'/';
} else {
	$folder = '/';
}
$sys_course_path = api_get_path(SYS_COURSE_PATH);

// Database table definitions
$tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
$propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);

// setting the breadcrumbs
$interbreadcrumb[] = array ("url" => Security::remove_XSS('document.php?curdirpath='.$pathurl), "name" => get_lang('Documents'));

$htmlHeadXtra[] =
"<script type=\"text/javascript\">
function confirmation (name) {
	if (confirm(\" ". get_lang("AreYouSureToDelete") ." \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

// Show hide images of gallery
if (isset($_GET['set_invisible']) || isset($_GET['set_visible'])) {
  if ($_GET['set_invisible']) {
   $update_id = Security::remove_XSS($_GET['set_invisible']);
			$visibility_command = 'invisible';
  } else {
   $update_id = Security::remove_XSS($_GET['set_visible']);
			$visibility_command = 'visible';
  }
 api_item_property_update($_course, TOOL_DOCUMENT, $update_id, $visibility_command, api_get_user_id());
}

// Displaying the header
Display :: display_tool_header(get_lang('Documents'));


// loading the slides from the session
/*
if (isset($_SESSION["image_files_only"])) {
	$image_files_only = $_SESSION["image_files_only"];
}
$total_slides = count($image_files_only);
*/
$total_slides = 0;
if($total_slides == 0 || (isset($_GET['slide_id']))){
 if (api_is_allowed_to_edit ()) { // Teacher
	  $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '".$path."/%' AND doc.path NOT LIKE '".$path."/%/%' AND prop.lastedit_type !='DocumentDeleted'";
 } else { // Student
	  $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '".$path."/%' AND doc.path NOT LIKE '".$path."/%/%' AND prop.visibility = 1 AND prop.lastedit_type !='DocumentDeleted'";
 }
	$result = api_sql_query($sql,__FILE__,__LINE__);
	$image_files_only = array();
	while($row = Database::fetch_array($result))
	{		
		$image_files_only[] = $row['title'];
		$index_image_files_only[] = $row['id'];
	}
}
$total_slides = count($image_files_only);
// calculating the current slide, next slide, previous slide and the number of slides
if ($slide_id <> "all") {
	if ($slide_id) {
		$slide = $slide_id;
	} else {
		$slide = 0;
	}

 if ($slide_id == '') {
  $previous_slide = '';
  $slide_id = $index_image_files_only[0];
  $next_slide = $index_image_files_only[1];
  $display_counter = count($index_image_files_only);
 }

 for ($i = 0; $i < count($index_image_files_only); $i++ ) {
  if ($slide_id == $index_image_files_only[$i]) {
   $previous_slide = $index_image_files_only[$i-1];
   $next_slide =  $index_image_files_only[$i+1];
   $display_counter = ($i +1);
  }
 }
	//$previous_slide = $slide -1;
	//$next_slide = $slide +1;
} // if ($slide_id<>"all")
$total_slides = count($image_files_only);

$query = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'folder' AND doc.path ='".urldecode($pathurl)."' AND prop.lastedit_type !='DocumentDeleted'";
$result = api_sql_query($query,__FILE__,__LINE__);
$row = Database::fetch_array($result);

$visibility_icon = ($row['visibility']==0)?'closedeye_tr':'dokeoseyeopen22';
$visibility_command = ($row['visibility']==0)?'set_visible':'set_invisible';
$visibility_title = ($row['visibility']==0)?'UnPublished':'Published';

?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

<?php
// Actions
echo '<div class="actions">';
echo '<a href="mediabox.php?'.api_get_cidReq().'">'.Display::return_icon('go_previous_32.png',get_lang('Documents')).' '.get_lang('Documents').'</a>';
if(api_is_allowed_to_edit()) {
	echo '<a href="mediabox.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'">'.Display::return_icon('mediaplayer.png',get_lang('Mediabox')).' '.get_lang('Mediabox').'</a>';
	echo '<a href="document.php?'.api_get_cidreq().'&action=exit_slideshow&curdirpath='.$pathurl.'">'.Display::return_icon('listview.png',get_lang('ListView')).' '.get_lang('ListView').'</a>';
}

// The image gallery is allowed for the students
if ($slide_id <> "all") {
  echo '<a href="slideshow.php?'.api_get_cidreq().'&slide_id=all&curdirpath='.$pathurl.'">'.Display::return_icon('gallery.png',get_lang('Gallery')).' '.get_lang('Gallery').'</a>';
}

if (api_is_allowed_to_edit()) {
	/*
	else {
		echo '<a href="slideshow.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'">'.Display::return_icon('slideshow.png',get_lang('Slideshow')).' '.get_lang('Slideshow').'</a>';
	}*/
	echo '<a href="upload.php?'.api_get_cidReq().'&path='.$pathurl.'">'.Display::return_icon('up.png',get_lang('UplUpload')).' '.get_lang('UplUpload').'</a>';
	//echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&'.$visibility_command.'='.$row['id'].'">'.Display::return_icon($visibility_icon.'png',get_lang('ChangeVisibility')).' '.get_lang('ChangeVisibility').'</a>';
}
/*
if ($slide_id <> "all") {
	echo '<a href="slideshow.php?slide_id=all&curdirpath='.$pathurl.'">'.Display::return_icon('thumbnails.png',get_lang('_show_thumbnails')).' '.get_lang('_show_thumbnails').'</a>';
} else {
	Display::display_icon('thumbnails_na.png',get_lang('_show_thumbnails')).' '.get_lang('_show_thumbnails').'</a>';
}
*/
//echo '<a href="slideshowoptions.php?curdirpath='.$pathurl.'">'.Display::return_icon('access_tool.gif',get_lang('_set_slideshow_options')).' '.get_lang('_set_slideshow_options').'</a>';
echo '</div>';

// Feedback messages
if (isset($_GET['msg']) ) {		
	if($_GET['msg'] == 'DEL') {
		Display::display_confirmation_message(get_lang('DocDeleted'));
	} elseif($_GET['msg'] == 'ERR') {				
		Display::display_error_message(get_lang('DocDeleteError'));
	} elseif($_GET['msg'] == 'ViMod') {				
		Display::display_confirmation_message(get_lang("ViMod"));
	} elseif($_GET['msg'] == 'ViModProb') {				
		Display::display_error_message(get_lang("ViModProb"));
	}	
}

// start the content div
echo '<div id="content">';

// display the tool title
//api_display_tool_title(get_lang('TemplateGallery'));

// =======================================================================
//				TREATING THE POST DATA FROM SLIDESHOW OPTIONS
// =======================================================================
// if we come from slideshowoptions.php we sessionize (new word !!! ;-) the options
if (isset ($_POST['Submit'])) {
	// we come from slideshowoptions.php
	$_SESSION["image_resizing"] = Security::remove_XSS($_POST['radio_resizing']);
	if ($_POST['radio_resizing'] == "resizing" && $_POST['width'] != '' && $_POST['height'] != '') {
		//echo "resizing";
		$_SESSION["image_resizing_width"] = Security::remove_XSS($_POST['width']);
		$_SESSION["image_resizing_height"] = Security::remove_XSS($_POST['height']);
	} else {
		//echo "unsetting the session heighte and width";
		$_SESSION["image_resizing_width"] = null;
		$_SESSION["image_resizing_height"] = null;
	}
} // if ($submit)


// The target height and width depends if we choose resizing or no resizing
if ($_SESSION["image_resizing"] == "resizing") {
	$target_width = $_SESSION["image_resizing_width"];
	$target_height = $_SESSION["image_resizing_height"];
} else {
	$image_width = $source_width;
	$image_height = $source_height;
}

// =======================================================================
//						THUMBNAIL VIEW
// =======================================================================
// this is for viewing all the images in the slideshow as thumbnails.
$image_tag = array ();
if ($slide_id == "all") {
//	$thumbnail_width = 100;
//	$thumbnail_height = 100;
	$thumbnail_width = 200;
	$thumbnail_height = 150;
	$row_items = 4;
 $count_index = 0;
	if (is_array($image_files_only)) {
		foreach ($image_files_only as $index => $one_image_file) {
			$image = $sys_course_path.$_course['path']."/document".$folder.$one_image_file;
			if (file_exists($image)) {
			/*	$image_height_width = resize_image($image, $thumbnail_width, $thumbnail_height, 1);

				$image_height = $image_height_width[0];
				$image_width = $image_height_width[1];*/

				list($twidth,$theight) = getimagesize($image);
				
				if($twidth > 200 || $theight > 150)
				{
				$image_height_width = resize_image($image, $thumbnail_width, $thumbnail_height, 1);
				
				$image_height = $image_height_width[0];
				$image_width = $image_height_width[1];	
				}
				else
				{
				$image_height = $theight;
				$image_width = $twidth;
				}

				if ($path and $path !== "/") {
					$doc_url = $path."/".$one_image_file;
				} else {
					$doc_url = $path.$one_image_file;
				}
				$image_tag[] = "<img src='download.php?doc_url=".$doc_url."' border='0' width='".$image_width."' height='".$image_height."' title='".$one_image_file."'>";
    $image_index_tag[] = $index_image_files_only[$index];
			}
   $count_index++;
		} // foreach ($image_files_only as $one_image_file)
	}
} // if ($slide_id=="all")

// creating the table
$html_table='';
$i = 0;
$count_image=count($image_tag);
$number_image=4;
$number_iteration=ceil($count_image/$number_image);

$p=0;
for ($k=0;$k<$number_iteration;$k++) {
	//echo '<tr height="'.$thumbnail_height.'">';
	for ($i=0;$i<$number_image;$i++) {
	//	echo '<div class="big_button four_buttons rounded grey_border float_l" style="clear:none; height:170px; width:220px; padding:0px;margin:0 5px 5px 5px;">';
		echo '<div class="mediabig_button four_buttons rounded grey_border float_l" style="clear:none; height:170px; width:220px; padding:0px;margin:0 5px 5px 5px;">';
		if (!is_null($image_tag[$p])) {
//			echo '<div class="sectionttitle"></div>';				
			echo '<div class="sectioncontent" style="padding: 10px;">';
			echo '<a href="slideshow.php?'.api_get_cidreq().'&slide_id='.$image_index_tag[$p].'&curdirpath='.$pathurl.' ">'.$image_tag[$p].'</a>';
			echo '</div>';
		}
		$p++;
		echo '</div>';
	}
}
echo '<div class="clear">&nbsp;</div>';
// =======================================================================
//						ONE AT A TIME VIEW
// =======================================================================
// this is for viewing all the images in the slideshow one at a time.
if ($slide_id !== "all") {
	$image = $sys_course_path.$_course['path']."/document".$folder.$image_files_only[$slide];

	if (file_exists($image)) {

		if (!isset($_REQUEST['linkfile'])) {
		  $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.id='".Database::escape_string(Security::remove_XSS($slide_id))."' AND prop.lastedit_type !='DocumentDeleted'";
		} else {
		  $sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path='".Database::escape_string(Security::remove_XSS($_REQUEST['linkfile']))."' AND prop.lastedit_type !='DocumentDeleted'";
		}
		
		$result = api_sql_query($sql,__FILE__,__LINE__);
		$row = Database::fetch_array($result);

		$title = $row['title'];
		$title_without_extension = str_ireplace(array('.jpg','.gif','.bmp','.png','.jpeg'),array('','','','',''),$title);
		$visibility_icon = ($row['visibility']==0)?'closedeye_tr':'dokeoseyeopen22';
		$visibility_command = ($row['visibility']==0)?'set_visible':'set_invisible';

		$image = $sys_course_path.$_course['path']."/document".$folder.$title;
		
		$image_height_width = resize_image($image, $target_width, $target_height);

		$image_height = $image_height_width[0];
		$image_width = $image_height_width[1];

		if ($_SESSION["image_resizing"] == "resizing") {
			$height_width_tags = 'width="'.$image_width.'" height="'.$image_height.'"';
		}

		list($width, $height) = getimagesize($image);

		// showing the comment of the image, Patrick Cool, 8 april 2005
		// this is done really quickly and should be cleaned up a little bit using the API functions
		

	/*	echo '<div class="section">';
			echo '<br/><div class="sectiontitle overflow_h" style="width:35%">';*/
			echo '<div class="quiz_content_actions">';
			echo '<div style="width:50%;padding-left:230px;">';
			if ($previous_slide > 0) {
				echo '<div class="float_l sectiontitleleft"><a href="slideshow.php?'.api_get_cidreq().'&slide_id='.$previous_slide.'&amp;curdirpath='.$pathurl.'">';
					echo '<img style="vertical-align:middle; margin:10px 0;" src="'.api_get_path(WEB_IMG_PATH).'previousbig.png" alt="">';
				echo '</a></div>';
			}
			else
			{
				echo '<div class="float_l sectiontitleleft">';
					echo '<img style="vertical-align:middle; margin:10px 0;" src="'.api_get_path(WEB_IMG_PATH).'previousbig.png" alt="">';
				echo '</div>';
			}
			if ($slide_id <> 'all') {
				echo '<div class="float_l sectiontitlecenter" style="padding:15px 0;">&nbsp;&nbsp;&nbsp;'.$title_without_extension.'&nbsp;<br/>&nbsp; '.$display_counter.' '.get_lang('Of').' '.$total_slides.'</div>';
			}	
			// next slide
			if ($next_slide !='' && $slide_id <> "all") {
				echo "<div class='float_l sectiontitleright'><a href='slideshow.php?".api_get_cidreq()."&slide_id=".$next_slide."&curdirpath=$pathurl'>";
					echo '<img style="vertical-align:middle; margin:10px 0;" src="'.api_get_path(WEB_IMG_PATH).'nextbig.png" alt="">';
				echo '</a></div>';
			}
			else
			{
				echo "<div class='float_l sectiontitleright'>";
					echo '<img style="vertical-align:middle; margin:5px 0;" src="'.api_get_path(WEB_IMG_PATH).'nextbig.png" alt="">';
				echo '</div>';
			}
			
		echo '	</div>';

		if($height <= 300) { 
			$style= 'height:330px;';
		} else 	{
			$style= 'height:530px;';
		}
		echo '	<div class="sectioncontent" style="'./*$style.*/' text-align:center;">';
		echo "<img src='download.php?doc_url=".$row['path']."' alt='".$title."' border='0'".$height_width_tags." style='margin:10px 0;' >";
		echo '	</div>';

		$aux= explode(".", htmlspecialchars($image_files_only[$slide]));
	    $ext= $aux[count($aux)-1];			
		echo '<div class="sectionfooter" style="text-align:center;">';	
			echo '<div style="margin:0 10px 10px 0">'.$title_without_extension." - ".get_lang('Size').': '.$width.'px x '.$height.'px</div>';
			if(api_is_allowed_to_edit())
			{			
				$url_path = $path;			
				$req_gid = '/'.$title;
				$forcedownload_link='document.php?action=download&id='.$url_path.$req_gid;
				$img_style = "margin:0 5px 20px 5px;";
			// delete
				echo '<a style="'.$img_style.'" href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&delete='.urlencode($row['path']).'&slide_id='.$next_slide.'" onclick="return confirmation(\''.basename($path).'\');"><img src="../img/delete.png"></a>';
			// visible or not
				echo '<a style="'.$img_style.'" href="slideshow.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&amp;'.$visibility_command.'='.$row['id'].'&slide_id='.$slide_id.'"><img src="../img/'.$visibility_icon.'.png" border="0" title="'.get_lang('Visible').'" alt="" /></a>';
			// download
				echo '<a style="'.$img_style.'" href="'.$forcedownload_link.'"><img style="vertical-align:top;" src="'.api_get_path(WEB_IMG_PATH).'go-jump.png" alt=""></a>';
				
			}
		echo '	</div>';
		echo '</div>';
		
	} else {
		Display::display_warning_message(get_lang('FileNotFound'));
	}
} // if ($slide_id!=="all")

// close the content div
echo '</div>';

 // bottom actions bar
echo '<div class="actions">';
echo '</div>';
// display footer
Display :: display_footer();
?>
