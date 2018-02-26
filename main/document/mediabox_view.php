<?php

/* For licensing terms, see /dokeos_license.txt */

/**
============================================================================== 
*	@package dokeos.document
*	@todo the implementation of the popup is very ucky and should be refactored
============================================================================== 
*/

// Language files that should be included
$language_file = array('document');

// setting the help
$help_content = 'mediabox';

// include the global Dokeos file
include ('../inc/global.inc.php');

// include additional libraries
require_once api_get_path(LIBRARY_PATH) . 'fileDisplay.lib.php';
require_once 'slideshow.inc.php';

// section (for the tabs)
$this_section = SECTION_COURSES;


// variable initialisation
$noPHP_SELF = true;
$path = Security::remove_XSS($_GET['curdirpath']);
$pathurl = urlencode($path);

// additional javascript, css, ...
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/dhtmlwindow.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/modal.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<script src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/csspopup.js" type="text/javascript"></script>';
$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/modal.css";</style>';
$htmlHeadXtra[] = '<style type="text/css" media="all">@import "' . api_get_path(WEB_LIBRARY_PATH) . 'javascript/dhtmlwindow.css";</style>';
$htmlHeadXtra[] = '<style type="text/css">
#blanket {
    background-color:#111111;
	opacity: 0.30;
	filter:alpha(opacity=30);
	position:absolute;
	z-index: 9001;
	top:0px;
	left:0px;
	width:100%;   
}
</style>';


// adding the breadcrumbs
$interbreadcrumb[] = array ("url" => Security::remove_XSS('document.php?curdirpath='.$pathurl), 'name' => get_lang('Mediabox'));

$htmlHeadXtra[] =
"<script type=\"text/javascript\">
function confirmation (name) {
	if (confirm(\" ". get_lang("AreYouSureToDelete") ." \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>";

// display the header
Display :: display_tool_header(get_lang('Mediabox'), "Doc");

// Database table initialisation
$tbl_documents = Database::get_course_table(TABLE_DOCUMENT);
$propTable = Database::get_course_table(TABLE_ITEM_PROPERTY);

// feedback messages
if (isset($_GET['msg']) )
{		
	switch ($_GET['msg']){
		case 'DEL':
			Display::display_confirmation_message(get_lang('DocDeleted'));
			break;
		case 'ERR':
			Display::display_error_message(get_lang('DocDeleteError'));
			break;
		case 'ViMod':
			Display::display_confirmation_message(get_lang('ViMod'));
			break;
		case 'ViModProb':
			Display::display_error_message(get_lang('ViModProb'));
			break;
	}
}

$query = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'folder' AND doc.path ='".urldecode($pathurl)."'";
$result = api_sql_query($query,__FILE__,__LINE__);
$row = Database::fetch_array($result);
		
$visibility_icon = ($row['visibility']==0)?'closedeye_tr':'dokeoseyeopen22';
$visibility_command = ($row['visibility']==0)?'set_visible':'set_invisible';
$visibility_title = ($row['visibility']==0)?'UnPublished':'Published';

// actions
echo '<div class="actions">';
echo '<a href="mediabox.php?'.api_get_cidReq().'">'.Display::return_icon('go_previous_32.png',get_lang('Documents')).' '.get_lang('Documents').'</a>';
if(api_is_allowed_to_edit()) {
	echo '<a href="mediabox.php?'.api_get_cidReq().'&curdirpath='.$pathurl.'">'.Display::return_icon('mediaplayer.png',get_lang('Mediabox')).' '.get_lang('Mediabox').'</a>';
	echo '<a href="document.php?'.api_get_cidReq().'&action=exit_slideshow&curdirpath='.$pathurl.'">'.Display::return_icon('listview.png',get_lang('ListView')).' '.get_lang('ListView').'</a>';
}

if(api_is_allowed_to_edit()) {
	echo '<a href="upload.php?'.api_get_cidReq().'&path='.$pathurl.'">'.Display::return_icon('up.png',get_lang('UplUpload')).' '.get_lang('UplUpload').'</a>';
//	echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&'.$visibility_command.'='.$row['id'].'">'.Display::return_icon($visibility_icon.'.png',$visibility_title).' '.$visibility_title.'</a>';
}	
echo '</div>';

// start the content div
echo '<div id="content">';


if($path == '/audio'){
	$sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '/audio/%' AND prop.lastedit_type !='DocumentDeleted'";
	$imgsrc=Display::return_icon('mediaaudio.png');
} elseif($path == '/video') {
	$sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '/video/%' AND prop.lastedit_type !='DocumentDeleted'";
	$imgsrc=Display::return_icon('media_video.png');
} elseif($path == '/podcasts') {
	$sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '/podcasts/%' AND prop.lastedit_type !='DocumentDeleted'";
	$imgsrc=Display::return_icon('media_podcasts.png');
} elseif($path == '/screencasts') {
	$sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '/screencasts/%' AND prop.lastedit_type !='DocumentDeleted'";
	$imgsrc=Display::return_icon('media_screencasts.png');
} elseif($path == '/animations') {
	$sql = "SELECT * FROM $tbl_documents doc,$propTable prop WHERE doc.id = prop.ref AND prop.tool = '".TOOL_DOCUMENT."' AND doc.filetype = 'file' AND doc.path LIKE '/animations/%' AND prop.lastedit_type !='DocumentDeleted'";
}
$result = api_sql_query($sql,__FILE__,__LINE__);

echo '<div id="blanket" style="display:none;"></div>';
echo '<table class="gallery">';
$i=0;
$j=1;
while($row = Database::fetch_array($result))
{	
	$visibility_icon = ($row['visibility']==0)?'closedeye_tr':'dokeoseyeopen22';
	$visibility_command = ($row['visibility']==0)?'set_visible':'set_invisible';

	echo '<style type="text/css">#popUpDiv'.$i.' {
	position:fixed;
	background-color:#F7F7F7;
	width:auto;
	height:auto;
	z-index: 9002; /*ooveeerrrr nine thoussaaaannnd*/	
	top:150px;
	border:1px solid #ccc;
	 -moz-border-radius: 10px;
    -webkit-border-radius: 10px;	
	zoom: 1;
	background: #EFEFEF;
    /* Mozilla: */
    background: -moz-linear-gradient(top, #EFEFEF, #ffffff);
    /* Chrome, Safari:*/
    background: -webkit-gradient(linear,
                left top, left bottom, from(#EFEFEF), to(#ffffff));
    /* MSIE */
    filter: progid:DXImageTransform.Microsoft.Gradient(
                StartColorStr=\'#EFEFEF\', EndColorStr=\'#ffffff\', GradientType=0);
	}</style>';

	$title = $row['title'];
	$audvid_path = $row['path'];
	$size = format_file_size($row['size']);	
	$req_gid = '/'.$title;
	$forcedownload_link='document.php?'.api_get_cidreq().'&action=download&id='.$path.$req_gid;

	if(!$i%3) {
		echo '<tr>';
	}
	if($path == '/audio' || $path == '/podcasts') {
		if(pathinfo($row['title'],PATHINFO_EXTENSION)=='mp3') {
			echo '<td>';

			echo '<div class="mediabig_button three_buttons rounded grey_border">';
			echo '<div class="sectiontitle"><a href="#" onclick="popup(\'popUpDiv'.$i.'\')" >'.$title.'</a></div><br/>';
			echo getAudioVideo($title,$i,$audvid_path,$path);
			echo '<a href="#" onclick="popup(\'popUpDiv'.$i.'\')">';
			echo '<div>'.$imgsrc.'</div><br/>';
   echo '</a>';
			if(api_is_allowed_to_edit()) {
				echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&type=media&delete='.urlencode($row['path']).'" onclick="return confirmation(\''.basename($path).'\');"><img src="../img/delete.png"><a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&amp;'.$visibility_command.'='.$row['id'].'&type=media"><img src="../img/'.$visibility_icon.'.png" border="0" title="'.get_lang('Visible').'" alt="" /></a>';
			}
			echo '<a href="'.$forcedownload_link.'"><img style="vertical-align:top;" src="'.api_get_path(WEB_IMG_PATH).'go-jump.png" alt=""></a>';
			echo '</div>';
			echo '</td>';
		}
	} elseif($path == '/video' || $path == '/screencasts') {
		echo '<td>';
		echo '	<div class="mediabig_button three_buttons rounded grey_border">';
		echo '		<div class="sectiontitle"><a href="#" onclick="popup(\'popUpDiv'.$i.'\')" >'.$title.'</a></div>';
  echo getAudioVideo($title,$i,$audvid_path,$path);
		echo '<a href="#" onclick="popup(\'popUpDiv'.$i.'\')">';
		echo '		<div>'.$imgsrc.'</div><br/>';
  echo '</a>';
		echo '		<div>';
		echo $size;
		if(api_is_allowed_to_edit())
		{
			echo '	<a href="document.php?'.api_get_cidreq().'&type=media&curdirpath='.$pathurl.'&delete='.urlencode($row['path']).'" onclick="return confirmation(\''.basename($path).'\');">'.Display::return_icon('delete.png',get_lang('Delete')).'</a>';
			echo '	<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&amp;'.$visibility_command.'='.$row['id'].'&type=media">'.Display::return_icon($visibility_icon.'.png',get_lang('Visible')).'</a>';			
		}
		echo '	<a href="'.$forcedownload_link.'">'.Display::return_icon('go-jump.png',get_lang('Download')).'</a>';
		echo '	</div>';
		echo '</td>';
	} elseif($path == '/animations') {
		$course_name = explode("=",api_get_cidReq());	
		$medialink = api_get_path(WEB_COURSE_PATH).$course_name[1].'/document/animations/'.$title;
		$imgsrc = '<img src="../img/media_flash.png">';
		echo '<td>';
	//	echo '<a href="#" onclick="popup(\'popUpDiv'.$i.'\')">';
		$navigator_info = api_get_navigator();		
		echo '	<div class="mediabig_button three_buttons rounded grey_border">';
		if ($navigator_info['name'] == 'Internet Explorer' && ($navigator_info['version'] == '6' || $navigator_info['version'] == '7' || $navigator_info['version'] == '8')) {
			echo "<a href=\"#\" onClick=\"Media=window.open('".$medialink."', 'mediawindow', 'width=720px,height=500px')\">";
		}
		else
		{
		echo "<a href=\"#\" onClick=\"Media=dhtmlmodal.open('Media', 'iframe', '".$medialink."', 'Media', 'width=720px,height=540px,center=1,resize=1,scrolling=1')\">";
		}
		echo '		<div class="sectiontitle">'.$title.'</div>';
		echo '		<div>'.$imgsrc.'</div><br/></a>';
		echo '		<div>';
		echo $size;
		if(api_is_allowed_to_edit())
		{
			echo '	<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&type=media&delete='.urlencode($row['path']).'" onclick="return confirmation(\''.basename($path).'\');"><img src="../img/delete.png">&nbsp;&nbsp;
				<a href="document.php?'.api_get_cidreq().'&curdirpath='.$pathurl.'&amp;'.$visibility_command.'='.$row['id'].'&type=media"><img src="../img/'.$visibility_icon.'.png" border="0" title="'.get_lang('Visible').'" alt="" /></a>&nbsp;&nbsp;';
		}
		echo '<a href="'.$forcedownload_link.'"><img style="vertical-align:top;" src="'.api_get_path(WEB_IMG_PATH).'go-jump.png" alt=""></a>';
		echo '		</div>';
		echo '	</div>';
		echo '</td>';
	}
	if($j == 3) {
		echo '</tr>';
		$j=0;
	}
	$i++;
	$j++;
}
echo '</table>';


function getAudioVideo($title,$i,$audvid_path,$path) {
		$ext = explode(".",$title);	
		$course_name = explode("=",api_get_cidReq());		
		$src_path = api_get_path(WEB_COURSE_PATH).$course_name[1].'/document'.$audvid_path;

		// the popup
		$return .= '<div id="blanket" style="display:none;"></div><div id="popUpDiv'.$i.'" style="display:none;">
		<div>
		<br><span style="padding-left:5px;color:maroon;">'.get_lang('Media').'</span><a href="#" onclick="popup(\'popUpDiv'.$i.'\')"><span style="align:right;color:red;padding-left:65%;">'.get_lang('Close').'</span></a><br>';


		if($ext[1] == 'flv') {

		$return .= '<div class="lp_mediaplayer" id="container'.$i.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</div>';
		$return .= '<script type="text/javascript" src="../inc/lib/mediaplayer/swfobject.js"></script>';
		$return .= '<script type="text/javascript">
										var s1 = new SWFObject("../inc/lib/mediaplayer/player.swf","ply","450","350","9","#FFFFFF");
										s1.addParam("allowscriptaccess","always");
										s1.addParam("allowfullscreen","true");
										s1.addParam("flashvars","file=' . $src_path . '&autostart=true");
										s1.write("container'.$i.'");
									</script>';
		} elseif($ext[1] == 'mp3') {
		$return .= '<div class="lp_mediaplayer" id="container'.$i.'"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</div>';
			$return .= '<script type="text/javascript" src="../inc/lib/mediaplayer/swfobject.js"></script>';
			$return .= '<script type="text/javascript">
										var s1 = new SWFObject("../inc/lib/mediaplayer/player.swf","ply","260","100","9","#FFFFFF");
										s1.addParam("allowscriptaccess","always");
										s1.addParam("allowfullscreen","true");
										s1.addParam("flashvars","file=' . $src_path . '&autostart=true");
										s1.write("container'.$i.'");
									</script>';
		} elseif($ext[1] == 'mpg' || $ext[1] == 'wmv' || $ext[1] == 'wma' || $ext[1] == 'avi') {
		$return .= '
		<OBJECT ID="MediaPlayer" WIDTH="450" HEIGHT="350" CLASSID="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"
		STANDBY="Loading Windows Media Player components..." TYPE="application/x-oleobject">
		<PARAM NAME="FileName" VALUE="'.$title.'">
		<PARAM name="autostart" VALUE="true">
		<PARAM name="ShowControls" VALUE="true">
		<param name="ShowStatusBar" value="true">
		<PARAM name="ShowDisplay" VALUE="true">
		<EMBED TYPE="application/x-mplayer2" SRC="'.$src_path.'" NAME="MediaPlayer"
		WIDTH="400" HEIGHT="300" ShowControls="1" ShowStatusBar="1" ShowDisplay="1" autostart="1"> </EMBED>
		</OBJECT>';
		} elseif($ext[1] == 'swf') {
		 $return .= '<div>'.$title.'</div>';
		}

		$return .= '</div></div>';

		return $return;
	}

// close the content div
echo '</div>';

 // bottom actions bar
echo '<div class="actions">';
echo '</div>';
// display the footer
Display :: display_footer();
?>
