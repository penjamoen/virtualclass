<?php
/* For licensing terms, see /dokeos_license.txt */

/**
 * Learning Path
 * @package dokeos.learnpath
 */

// Language files that should be included
//$language_file []= 'languagefile1';
//$language_file []= 'languagefile2';

// setting the help
$help_content = 'learningpath';

// including the global Dokeos file
require_once '../inc/global.inc.php';

// including additional libraries
require_once('back_compat.inc.php');
require_once('learnpathList.class.php');
require_once('learnpath.class.php');
require_once('learnpathItem.class.php');
include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// setting the tabs
$this_section=SECTION_COURSES;
// variable initialisation
$is_allowed_to_edit = api_is_allowed_to_edit(null,true);
// Add additional javascript, css
if ($is_allowed_to_edit) {
$htmlHeadXtra[] =
"<script language='javascript' type='text/javascript'>
	function confirmation(name)
	{
		if (confirm(\" ".trim(get_lang('AreYouSureToDelete'))." \"+name+\"?\"))		return true;
		else																		return false;
	}
</script>";
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery.ui.all.js" type="text/javascript" language="javascript"></script>';
}
// setting the breadcrumbs
$interbreadcrumb[] = array ("url"=>"overview.php", "name"=> get_lang('OverviewOfAllCodeTemplates'));
$interbreadcrumb[] = array ("url"=>"coursetool.php", "name"=> get_lang('CourseTool'));

// Display the header
Display::display_tool_header(get_lang('CourseTool'));


/*------------------------------*/

if(empty($lp_controller_touched) || $lp_controller_touched!=1){
	header('location: lp_controller.php?action=list');
}

$courseDir   = api_get_course_path().'/scorm';
$baseWordDir = $courseDir;
$display_progress_bar = true;

/**
 * Display initialisation and security checks
 */
$nameTools = get_lang(ucfirst(TOOL_LEARNPATH));
event_access_tool(TOOL_LEARNPATH);

if (! $is_allowed_in_course) api_not_allowed();

/**
 * Display
 */
/* Require the search widget and prepare the header with its stuff */
if (api_get_setting('search_enabled') == 'true') {
	require api_get_path(LIBRARY_PATH).'search/search_widget.php';
	search_widget_prepare($htmlHeadXtra);
}

/*
-----------------------------------------------------------
	Introduction section
	(editable by course admins)
-----------------------------------------------------------
*/
Display::display_introduction_section(TOOL_LEARNPATH, array(
		'CreateDocumentWebDir' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/document/',
		'CreateDocumentDir' => '../../courses/'.api_get_course_path().'/document/',
		'BaseHref' => api_get_path('WEB_COURSE_PATH').api_get_course_path().'/'
	)
);


$current_session = api_get_session_id();
$drag_style = "cursor:default";
if($is_allowed_to_edit) {
 $drag_style = "";
	echo '<script type="text/javascript">		
		function dragDropEnd(ev)
		{			
		readyToMove = false;
		moveTimer = -1;

		var orderString = "";
			var objects = document.getElementsByTagName(\'div\');
			
		for(var no=0;no<objects.length;no++){
			if(objects[no].className==\'imageBox\' || objects[no].className==\'imageBoxHighlighted\'){
				if(objects[no].id != "foo" && objects[no].parentNode.id != "dragDropContent"){ // Check if its not the fake image, or the drag&drop box
					if(orderString.length>0){
						orderString = orderString + \',\';
						}
					orderString = orderString + objects[no].id;
					}
				}					
			}	

		dragDropDiv.style.display=\'none\';
		insertionMarker.style.display=\'none\';
		
		if(destinationObject && destinationObject!=activeImage){
			var parentObj = destinationObject.parentNode;
			parentObj.insertBefore(activeImage,destinationObject);
			activeImage.className=\'imageBox\';
			activeImage = false;
			destinationObject=false;
			getDivCoordinates();	
		}		
		savelporder(orderString);
}

function savelporder(str)
	{
			var orderString = "";
			var objects = document.getElementsByTagName(\'div\');
			
			for(var no=0;no<objects.length;no++){
				if(objects[no].className==\'imageBox\' || objects[no].className==\'imageBoxHighlighted\'){
					if(objects[no].id != "foo" && objects[no].parentNode.id != "dragDropContent"){ // Check if its not the fake image, or the drag&drop box
						if(orderString.length>0){
							orderString = orderString + \',\';
							}
						orderString = orderString + objects[no].id;
						}
					}					
				}				
		if(str != orderString)
		{
		  window.location.href="lp_controller.php?'.api_get_cidReq().'&action=course&dispaction=sortlp&order="+orderString;
		}
}
		
				</script>';
	
  /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/

  if (!empty($dialog_box))
  {
	  switch ($_GET['dialogtype'])
	  {
	  	case 'confirmation':	Display::display_confirmation_message($dialog_box);		break;
	  	case 'error':			Display::display_error_message($dialog_box);			break;
	  	case 'warning':			Display::display_warning_message($dialog_box);			break;
	  	default:	    		Display::display_normal_message($dialog_box);			break;
	  }
  }
  
	if (api_failure::get_last_failure())	    Display::display_normal_message(api_failure::get_last_failure());

	echo '<div class="actions">';
		echo '<a class="" href="'.api_get_self().'?'.api_get_cidReq().'">'.Display::return_icon('author.png'). get_lang("Author").'</a>';
	echo '</div>';
}

/*---------------------------------------------------------------------------------------------------------------------------------*/
?>
<div id="content">

	<?php	
		
		
		$list = new LearnpathList(api_get_user_id());
		$flat_list = $list->get_flat_list();
		if (is_array($flat_list) && !empty($flat_list))
		{
			echo '<div id="GalleryContainer">';			
			foreach ($flat_list as $id => $details)
			{
				$name = Security::remove_XSS($details['lp_name']);
				$progress_bar = learnpath::get_db_progress($id,api_get_user_id());	
				
				if(strlen($name) > 75)
				{
				$display_name = substr($name,0,75).'...';
				}
				else
				{
				$display_name = $name;
				}
 				$html = "<div class=\"border\" style='width:100%;height:18px;'><div class=\"progressbar\" style='width:$progress_bar;height:20px;'></div></div>";

				echo '<div class="imageBox" id="imageBox'.$id.'">
                  <div class="imageBox_theImage" style="'.$drag_style.'"><div class="quiz_content_actions" style="width:200px;height:80%;">';
                echo '<table width="100%">';
				echo '<tr style="height:50px;"><td colspan="2" align="center">'.$display_name.'</td></tr>';
				echo '<tr><td>&nbsp;</td></tr>';
				echo '<tr><td width="80%" valign="top">'.$html.'</td><td align="center"><a href="lp_controller.php?'.api_get_cidReq().'&action=view&lp_id='.$id.'"><img src="../img/exaile_old22.png"></a></td></tr>';
				echo '</table>';				
				echo '</div></div>';
				if (api_is_allowed_to_edit()) {
				  echo '<div><a href="lp_controller.php?'.api_get_cidReq().'&action=add_item&type=step&lp_id='.$id.'"><img style="padding-left:190px;" src="../img/edit_link.png" ></a></div>';
				}
				echo '</div>';
			}
			echo '</div>
		<div id="insertionMarker">
		<img src="../img/marker_top.gif">
		<img src="../img/marker_middle.gif" id="insertionMarkerLine">
		<img src="../img/marker_bottom.gif">
		</div>
		<div id="dragDropContent">
		</div><div id="debug" style="clear:both">
		</div>';
		}
		else
		{
			echo '<div align="center"><a href="lp_controller.php?' . api_get_cidreq().'">'.get_lang('NoCourse').'</a></div>';
		}
		
	?>
	
	<!-- list of courses -->
	
	
</div><!--end of div#content-->

<?php


// bottom actions bar
echo '<div class="actions">';
echo '</div>';

// display the footer
Display::display_footer();
?>