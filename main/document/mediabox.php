<?php

/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	@package dokeos.document
==============================================================================
 */

// Language files that should be included
$language_file = array('document');

// setting the help
$help_content = 'mediabox';

// include the global Dokeos file
include ('../inc/global.inc.php');

// section (for the tabs)
$this_section = SECTION_COURSES;

// variable initialisation
$_SESSION['whereami'] = 'document/create';
$path = Security::remove_XSS($_GET['curdirpath']);
$pathurl = urlencode($path);
$imagepath = '/images';
$photopath = '/photos';
$mindmappath = '/mindmaps';
$mascotpath = '/mascot';
$audiopath = '/audio';
$videopath = '/video';
$podcastpath = '/podcasts';
$screenpath = '/screencasts';
$animationpath = '/animations';

// setting the breadcrumbs
$interbreadcrumb[] = array ("url" => Security::remove_XSS("document.php?curdirpath=".$pathurl), "name" => get_lang('Documents'));

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
Display :: display_tool_header(get_lang('Mediabox'));

// actions
echo '	<div class="actions">
		<a href="document.php?'.api_get_cidReq().'">'.Display::return_icon('go_previous_32.png',get_lang('Documents')).' '.get_lang('Documents').'</a>
	</div>';

// start the content div
echo '<div id="content">';

// display the tool title
//api_display_tool_title(get_lang('Mediabox'));

/*echo '<table class="gallery">';
echo '	<tr>';
echo '		<td>';
echo '		<a href="slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($imagepath).'"><div class="section"><div class="sectiontitle">'.get_lang('Images').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_images.png',get_lang('Images')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($photopath).'"><div class="section"><div class="sectiontitle">'.get_lang('Photos').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_photos.png',get_lang('Photos')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($mascotpath).'"><div class="section"><div class="sectiontitle">'.get_lang('Mascot').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_mascots.png',get_lang('Mascot')).'</div></div></a>';
echo '		</td>';
echo '	</tr>';
echo '	<tr>';
echo '		<td>';
echo '		<a href="mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($audiopath).'"><div class="section"><div class="sectiontitle">'.get_lang('Audio').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('mediaaudio.png',get_lang('Audio')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($videopath).'"><div class="section"><div class="sectiontitle">'.get_lang('Video').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_video.png',get_lang('Video')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($podcastpath).'"><div class="section"><div class="sectiontitle">'.get_lang('Podcasts').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_podcasts.png',get_lang('Podcasts')).'</div></div></a>';
echo '		</td>';
echo '	</tr>';
echo '	<tr>';
echo '		<td>';
echo '		<a href="mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($screenpath).'"><div class="section"><div class="sectiontitle">'.get_lang('Screencasts').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_screencasts.png',get_lang('Screencasts')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($animationpath).'"><div class="section"><div class="sectiontitle">'.get_lang('Animations').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_flash.png',get_lang('Animations')).'</div></div></a>';
echo '		</td>';
echo '		<td>';
echo '		<a href="slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($mindmappath).'"><div class="section"><div class="sectiontitle">'.get_lang('Mindmaps').'</div><div class="sectioncontent padding_tb_10">'.Display::return_icon('media_mindmaps.png',get_lang('Mindmaps')).'</div></div></a>';
echo '		</td>';
echo '	</tr>';
echo '</table>';*/

$commonCssClasses = "big_button three_buttons rounded grey_border";
// Image page
	$href = 'slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($imagepath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_image_button">' . get_lang("Images") . '</a>';
// Photos page
	$href = 'slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($photopath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_photos_button">' . get_lang("Photos") . '</a>';
// Mascot page
	$href = 'slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($mascotpath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_mascot_button">' . get_lang("Mascot") . '</a>';
// Audio page
	$href = 'mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($audiopath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_audio_button">' . get_lang("Audio") . '</a>';
// Video page
	$href = 'mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($videopath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_video_button">' . get_lang("Video") . '</a>';
// Podcasts page
	$href = 'mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($podcastpath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_podcast_button">' . get_lang("Podcasts") . '</a>';
// Screencast page
	$href = 'mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($screenpath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_screencast_button">' . get_lang("Screencasts") . '</a>';
// Animation page
	$href = 'mediabox_view.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($animationpath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_animation_button">' . get_lang("Animations") . '</a>';
// Mindmaps page
	$href = 'slideshow.php?'.api_get_cidReq().'&slide_id=all&curdirpath='.urlencode($mindmappath);
	$return .= '<a href="'.$href . '" class="'.$commonCssClasses.' create_mindmap_button">' . get_lang("Mindmaps") . '</a>';
echo $return;

// close the content div
echo '</div>';

// bottom actions bar
echo '<div class="actions">';
echo '</div>';

// display the footer
Display::display_footer();
?>