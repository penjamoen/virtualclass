<?php
// Language files that should be included
$language_file = array ('courses', 'index', 'widget');

// forcing the 'current course' reset, as we're not inside a course anymore
$cidReset = true; 

// global Dokeos file
require_once './main/inc/global.inc.php';

// the section (for the tabs)
$this_section = SECTION_COURSES;


if (api_get_setting('portal_view') == 'widget'){
	require_once 'user_portal_widget.php';
} else {
	require_once 'user_portal_classic.php';	
}
?>
