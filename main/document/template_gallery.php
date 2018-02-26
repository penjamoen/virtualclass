<?php
/* For licensing terms, see /dokeos_license.txt */

/**
==============================================================================
*	This file allows creating new html documents with an online WYSIWYG html
*	editor.
*	@package dokeos.document
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array('document');

// setting the help
$help_content = 'documenttemplategallery';

// include the global Dokeos file
include ('../inc/global.inc.php');

// section (for the tabs)
$this_section = SECTION_COURSES;

// Database table definition
$table_sys_template 	= Database::get_main_table('system_template');	
$table_template 	= Database::get_main_table(TABLE_MAIN_TEMPLATES);	
$table_document 	= Database::get_course_table(TABLE_DOCUMENT, $_course['dbName']);

// variable initialisation
$_SESSION['whereami'] = 'document/create';
if(isset($_GET['curdirpath']) && !empty($_GET['curdirpath']))
{
$get_cur_path=Security::remove_XSS($_GET['curdirpath']);
}
else
{
$get_cur_path=Security::remove_XSS($_GET['dir']);
}
$get_file=Security::remove_XSS($_GET['file']);
$user_id = api_get_user_id();

// Display header
Display :: display_tool_header(get_lang('TemplateGallery'));

if(isset($_REQUEST['filename'])){
	$title = $_REQUEST['filename'];
} else {
	$title = '';
}

$certificate_link = "";
if (isset($_GET['certificate'])) {
 $certificate_link = "certificate= true";
}
// ACTIONS
echo '<div class="actions">';
if($_REQUEST['doc'] == 'N') {
	echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.urlencode($get_cur_path).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">'.Display::return_icon('go_previous_32.png',get_lang('Documents')).' '.get_lang('Documents').'</a>';
} else {
	echo '<a href="document.php?'.api_get_cidreq().'&curdirpath='.urlencode($get_cur_path).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">'.Display::return_icon('go_previous_32.png',get_lang('Documents')).' '.get_lang('Documents').'</a>';
}
echo '</div>';


// start the content div
echo '<div id="content">';

// display the tool title
//api_display_tool_title(get_lang('TemplateGallery'));

// Platform templates
$i=0;
$j=1;

echo '<table class="gallery">';

$sql = "SELECT id, title, image, comment, content FROM $table_sys_template";
$result = api_sql_query($sql, __FILE__, __LINE__);
while ($row = Database::fetch_array($result)) {
	if (!empty($row['image'])) {
		$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/'.$row['image'];
	} else {
		$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif';
	}
	if(!$i%4){
		echo '<tr>';
	}
	// a special template: the empty page
	if($i==0){
		echo '<td>';
		echo '	<div class="section">';
		if($_REQUEST['doc'] == 'N') {
			echo '<a href="create_document.php?'.api_get_cidReq().'&filename='.$title.'&dir='.urlencode($get_cur_path).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
		} else {
			echo '<a href="edit_document.php?'.api_get_cidReq().'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
		}
		  echo '<div class="sectiontitle">'.get_lang('Empty').'</div>
				<div class="sectioncontent"><img border="0" src="'.api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/empty.gif"/></div></a>
			</div>';
		echo '</td>';
		$j++;	
	}

	echo '<td>';	
	echo '	<div class="section">';
   if($_REQUEST['doc'] == 'N') {
		echo '<a href="create_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&filename='.$title.'&dir='.urlencode($get_cur_path).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
	} else {
		echo '<a href="edit_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
	}
	echo '<div class="sectiontitle">'.get_lang($row['title']).'</div>
			<div class="sectioncontent"><img border="0" src="'.$image.'" /></div></a>
		</div>';
	//echo '</a>';
	echo '</td>';
	if($j==4)
	{
		echo '</tr>';
		$j=0;
	}
	$i++;
	$j++;
}
echo '</table>';



// COURSE TEMPLATES
$sql = "SELECT template.id, template.title, template.description, template.image, template.ref_doc, document.path 
			FROM ".$table_template." template, ".$table_document." document 
			WHERE user_id='".Database::escape_string($user_id)."'
			AND course_code='".Database::escape_string(api_get_course_id())."'
			AND document.id = template.ref_doc"; 
$result = api_sql_query($sql, __FILE__, __LINE__);
$numrows = Database::num_rows($result);
if($numrows <> 0)
{
	$i=0;
	$j=1;

	echo '<table class="gallery">';


	while ($row = Database::fetch_array($result)) {
		if (!empty($row['image']))
			{
				$image = api_get_path(WEB_CODE_PATH).'upload/template_thumbnails/'.$row['image'];
			} else {			
				$image = api_get_path(WEB_PATH).'home/default_platform_document/template_thumb/noimage.gif';
			}
		if(!$i%4)
		{
			echo '<tr>';
		}
	
		echo '<td>';	
		echo '	<div class="section">';
        if($_REQUEST['doc'] == 'N') {
          echo '<a href="create_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&tmpltype=Personal&dir='.urlencode($get_cur_path).'&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
        } else {
          echo '<a href="edit_document.php?'.api_get_cidReq().'&tplid='.$row['id'].'&curdirpath='.urlencode($get_cur_path).'&file='.urlencode($get_file).'&tmpltype=Personal&amp;selectcat=' . Security::remove_XSS($_GET['selectcat']).'&'.$certificate_link.'">';
        }
          // User templates are not translatable
		  echo '<div class="sectiontitle">'.$row['title'].'</div>
				<div class="sectioncontent"><img border="0" src="'.$image.'"></div></a>
			</div>';
		echo '</td>';
		if($j==4)
		{
			echo '</tr>';
			$j=0;
		}
		$i++;
		$j++;
	}
	echo '</table>';
}
// close the content div
echo '</div>';

// bottom actions bar
echo '<div class="actions">';
echo '</div>';

// display footer
Display::display_footer();
?>
