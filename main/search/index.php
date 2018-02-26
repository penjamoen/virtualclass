<?php
/* For licensing terms, see /dokeos_license.txt */

/*****************
TODO: This is a draft for a demo. This code has to be improved before the release
******************/

$language_file = array('document');
require_once ('../inc/global.inc.php');
$this_section =  SECTION_COURSES;

$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
Display::display_tool_header(get_lang('CourseTool'));
	
?>
<script type="text/javascript" language="javascript">
	function search(){
		var form = $('#search_form');
		var input = $('#input').val();
		var loader = $('#loader');
		var contentChild = $('#content_with_secondary_actions div');
		var result = $('#result'); 
		form.hide();
		loader.html('searching '+input+'...');
		loader.show();
		
		contentChild.removeClass('bg_search_stewart');

		// ajax request to show results
		$.ajax({
			  url: '<?php echo api_get_path(WEB_CODE_PATH).'search/get_results.ajax.php' ?>',
			  cache: false,
			  data: 'input='+input,
			  success: function(html){
			    $('#result').html(html);
			    showResults();
			  }
			});
		return false;
		
	}
	
	function showResults(){
		var loader = $('#loader');
		var result = $('#result'); 
		
		loader.hide();
		result.fadeIn();
	}

	function hideResults(){
		var result = $('#result');
		var form = $('#search_form'); 
		result.hide();
		form.fadeIn();
		$('#input').focus();
	}
	
	// intercept "enter" to submit form
	$(document).ready(function(){
		$('#input').focus();
	});
	
</script>

<style type="text/css">
.form_orange					{ width:400px;  }
.form_orange input				{ width:100%;}
.form_orange h3					{
	color:#F09A43;
	margin-left:0px;
	text-transform:uppercase;
}
.form_orange #fileQueue 		{ margin-right:130px; }
.form_orange #fakeButton		{ margin:10px; }
.form_orange #uploadifyUploader	{ position:absolute; }
.form_orange a.submit			{
	background:url('../img/navigation/bg_orange_form_submit.gif') no-repeat 0 0 #F09A43;
	color:#fff;
	cursor:pointer;
	display:inline-block;
	float:right;
	font-size:90%;
	font-weight:bold;
	line-height:26px;
	height:26px;
	margin-top:10px;
	padding:0 10px 0 40px;
	text-transform:uppercase;
}
.form_orange .fileQSubmitContainer	{ margin-top:20px; }

.loader {
	background:url('../img/navigation/ajax-loader.gif') no-repeat center 0;
	color:#F09A43;
	margin:0 auto;
	padding:20px 0;
	text-align:center;
	width:150px;
}

.button_text	{
	height:15px;
	padding:70px 10px 30px;
	width:180px;
}
.button_notext	{
	height:115px !important;
	padding:0 !important;
	position: relative;

}

.megane			{ background:url('../img/navigation/renault/megane.png') no-repeat center center; }
.sandero		{ background:url('../img/navigation/renault/sandero.png') no-repeat center center; }
.kangoo			{ background:url('../img/navigation/renault/kangoo.png') no-repeat center center; }
.zero_emmission	{ background:url('../img/navigation/renault/zero_emmission.png') no-repeat center center #000; }
.alpine_renault	{ background:url('../img/navigation/renault/alpine.png') no-repeat center center #000; }
.carlos			{ background:url('../img/navigation/renault/carlos.png') no-repeat center center #000; }

.ardoise		{ background:url('../img/navigation/renault/ardoise.png') no-repeat center 20px; }
.pdf			{ background:url('../img/navigation/renault/pdf.png') no-repeat center 20px; }
.ipod			{ background:url('../img/navigation/renault/ipod.png') no-repeat center 20px; }
.xls			{ background:url('../img/navigation/renault/xls.png') no-repeat center 20px; }
.ppt			{ background:url('../img/navigation/renault/ppt.png') no-repeat center 20px; }
.doc 			{ background:url('../img/navigation/renault/doc.png') no-repeat center 20px; }

.abs	{ position:absolute; zoom:1; }
.rel	{ position:relative; }

.bg_black:hover	{ background-color:#000 !important; }

#result { padding: 20px 0; }

#submitBt {
	margin-top:20px;
}

.uploadbtn {

	margin-left:400px;

}
</style>
<?php

if(!extension_loaded('xapian'))
{
	Display::display_error_message(get_lang('SearchXapianModuleNotInstaled'));
}
else {
		
	
	//--- actions ---
	echo '<div class="actions">';
			echo '<a class="" href="index.php?'.api_get_cidreq().'" onclick="hideResults()">'.Display::return_icon('navigation/renault/loupe.png').'NEW SEARCH</a>';
			echo '<a class="" href="#">'.Display::return_icon('navigation/renault/films.png').'VIDEO ONLY</a>';
			echo '<a class="" href="#">'.Display::return_icon('navigation/renault/plus.png'). 'MORE CRITERIA</a>';
			echo '<a class="uploadbtn" href="upload.php?'.api_get_cidreq().'">'.Display::return_icon('navigation/renault/upload.png'). 'UPLOAD</a>';
	echo '</div>';
		
	
	
	
	// --- div#content ---
	echo '<div id="content_with_secondary_actions" class="rel">';
	
		
		echo '<div class="bg_search_stewart" style="height:400px;">';
	
	
		//---simple form---
			echo '<form method="POST" onsubmit="return search();">';
			echo '<div id="search_form" class="form_orange abs" style="width:290px; left:200px ;top:80px;">';
			
				if(!empty($_GET['message']) && $_GET['message']=='success')
					Display::display_confirmation_message(get_lang('UplUploadSucceeded'));
		
				echo '<h3>'.get_lang('SearchTheLibrary').'</h3>';
				echo '<input type="text" id="input" name="input">';
				echo '<button id="submitBt" class="save">'.get_lang('Validate').'</button>';
			echo '</div>';
			echo '</form>';
			
			echo '<div id="loader" class="rel loader" style="display:none; top:200px;">';
			echo '</div>';
			
			echo '<div id="result" style="display:none;">';
			
				
			
			echo '</div>';
	
	
	
	
		echo '</div>';
	echo '</div>';	//end div#content
	
	echo '<div class="actions">';
		echo '&nbsp;';
	echo '</div>';
}

Display::display_footer();
exit;

?>