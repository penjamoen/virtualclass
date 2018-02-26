<?php
// $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $

/*
 ==============================================================================
 Dokeos - elearning and course management software

 Copyright (c) 2004-2009 Dokeos SPRL
 Copyright (c) 2003 Ghent University (UGent)
 Copyright (c) 2001 Universite catholique de Louvain (UCL)
 Copyright (c) various contributors

 For a full list of contributors, see "credits.txt".
 The full license can be read in "license.txt".

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 See the GNU General Public License for more details.

 Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
 Mail: info@dokeos.com
 ==============================================================================
 */

/**
 * 	Question Pool
 * 	This script allows administrators to manage questions and add them into their exercises.
 * 	One question can be in several exercises
 * 	@package dokeos.exercise
 * 	@author Olivier Brouckaert
 * 	@version $Id: question_pool.php 20451 2009-05-10 12:02:22Z ivantcholakov $
 */
// name of the language file that needs to be included
$language_file = 'exercice';

include_once 'exercise.class.php';
include_once 'question.class.php';
include_once 'answer.class.php';
include_once '../inc/global.inc.php';
require_once '../newscorm/learnpath.class.php';

$this_section = SECTION_COURSES;

$is_allowedToEdit = api_is_allowed_to_edit();

$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES = Database::get_course_table(TABLE_QUIZ_ANSWER);

$TBL_QUESTIONS_TEMPLATE = Database::get_main_table(TABLE_MAIN_QUIZ_QUESTION_TEMPLATES);
$TBL_REPONSES_TEMPLATE = Database::get_main_table(TABLE_MAIN_QUIZ_ANSWER_TEMPLATES);

// Variable
$learnpath_id = Security::remove_XSS($_GET['lp_id']);
// Lp object
if (isset($_SESSION['lpobject'])) {
 if ($debug > 0)
  error_log('New LP - SESSION[lpobject] is defined', 0);
 $oLP = unserialize($_SESSION['lpobject']);
 if (is_object($oLP)) {
  if ($debug > 0)
   error_log('New LP - oLP is object', 0);
  if ($myrefresh == 1 OR (empty($oLP->cc)) OR $oLP->cc != api_get_course_id()) {
   if ($debug > 0)
    error_log('New LP - Course has changed, discard lp object', 0);
   if ($myrefresh == 1) {
    $myrefresh_id = $oLP->get_id();
   }
   $oLP = null;
   api_session_unregister('oLP');
   api_session_unregister('lpobject');
  } else {
   $_SESSION['oLP'] = $oLP;
   $lp_found = true;
  }
 }
}

// we set the encoding of the lp
if (!empty($_SESSION['oLP']->encoding)) {
	$charset = $_SESSION['oLP']->encoding;
} else {
	$charset = api_get_system_encoding();
}
if (empty($charset)) {
	$charset = 'ISO-8859-1';
}

// Add the extra lp_id parameter to some links
$add_params_for_lp = '';
if (isset($_GET['lp_id'])) {
  $add_params_for_lp = "&lp_id=".$learnpath_id;
}

if(isset($_REQUEST['fromExercise']))
{
	$fromExercise = $_REQUEST['fromExercise'];
}

if (!empty($gradebook) && $gradebook == 'view') {
	$interbreadcrumb[] = array(
     'url' => '../gradebook/' . $_SESSION['gradebook_dest'],
     'name' => get_lang('Gradebook')
	);
}

$nameTools = get_lang('QuestionPool');

$interbreadcrumb[] = array("url" => "exercice.php", "name" => get_lang('Exercices'));

// if admin of course
if ($is_allowedToEdit) {
	Display::display_tool_header($nameTools, 'Exercise');

	 $exercice_id = Security::remove_XSS($_REQUEST['fromExercise']);
	 
	// Main buttons
	 echo '<div class="actions" style="margin-top:5px;">';
	 if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
     
    //$lp_id = Security::remove_XSS($_GET['lp_id']);
    // The lp_id parameter will be added by javascript
     $return = "";
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '">' . Display::return_icon('go_previous_32.png', get_lang('Author')).get_lang("Author") . '</a>';
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&action=add_item&type=step">' . Display::return_icon('content.png', get_lang('Content')).get_lang("Content") . '</a>';
     echo $return;
   }
   else
	{
	 echo '<a href="exercice.php?' . api_get_cidreq() . '">' . Display :: return_icon('go_previous_32.png', get_lang('List')) . get_lang('List') . '</a>';
	}
	 echo '<a href="exercise_admin.php?' . api_get_cidreq() . '">' . Display :: return_icon('new_quiz.png', get_lang('NewEx')) . get_lang('NewEx') . '</a>';
	 echo '<a href="admin.php?' . api_get_cidreq() . '&exerciseId=' . $exercice_id . '">' . Display :: return_icon('dokeos_question.png', get_lang('Questions')) . get_lang('Questions') . '</a>';
	echo '</div>';
	?>

<div id="content">
<style>
	.quiztpl_actions {
	background-color:#fff;
	/* gradient background: Mozilla, Chrome/Safari, MSIE */
	background:-moz-linear-gradient(center top , #eaeaea, #FFFFFF);
	background: -webkit-gradient(linear,left top, left bottom, from(#eaeaea), to(#ffffff));
	filter: progid:DXImageTransform.Microsoft.Gradient(StartColorStr="#eaeaea", EndColorStr="#ffffff", GradientType=0);
	/* rounded corners */
	border:1px solid #b8b8b6;
	border-radius:5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;	
	margin-bottom: 5px;
	margin-top: 5px;
	padding: 10px;
	overflow:hidden;
	vertical-align:middle;
}
</style>
<?php

echo '<table><tr>
<td><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=1&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/01true_false.png" alt="'.get_lang('Truefalse').'" title="'.get_lang('Truefalse').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=2&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/02multiple_choice.png" alt="'.get_lang('Multiplechoice').'" title="'.get_lang('Multiplechoice').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=3&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/03multiple_choice_sequence.png" alt="'.get_lang('Multiplechoicesequence').'" title="'.get_lang('Multiplechoicesequence').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=4&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/04true_false_justified.png" alt="'.get_lang('Justifiedmultiplechoice').'" title="'.get_lang('Justifiedmultiplechoice').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=5&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/05none_of_the_above.png" alt="'.get_lang('Noneoftheabove').'" title="'.get_lang('Noneoftheabove').'"></a></td></tr></table></div></td>
<td><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=6&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/06mc_image.png" alt="'.get_lang('Multiplechoiceimage').'" title="'.get_lang('Multiplechoiceimage').'"></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=7&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/07mc_audio.png" alt="'.get_lang('Multiplechoicesound').'" title="'.get_lang('Multiplechoicesound').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=8&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/08mc_screencast.png" alt="'.get_lang('Multiplechoicescreencast').'" title="'.get_lang('Multiplechoicescreencast').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=9&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/09mc_flash.png" alt="'.get_lang('Multiplechoiceflash').'" title="'.get_lang('Multiplechoiceflash').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=10&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/10mc_video.png" alt="'.get_lang('Multiplechoicevideo').'" title="'.get_lang('Multiplechoicevideo').'"></a></td></tr></table></div></td>
<td valign="top"><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=11&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/11ma_identify.png" alt="'.get_lang('Multipleinclusion').'" title="'.get_lang('Multipleinclusion').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=12&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/12ma_remove.png" alt="'.get_lang('Multipleexclusion').'" title="'.get_lang('Multipleexclusion').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=13&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/13ma_identify_image.png" alt="'.get_lang('Multipleanswerimage').'" title="'.get_lang('Multipleanswerimage').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=14&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/14reasoning.png" alt="'.get_lang('Allitemsneeded').'" title="'.get_lang('Allitemsneeded').'"></a></td></tr><tr><td style="height:81px;">&nbsp;</td></tr></table></div></td>
<td valign="top"><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=15&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/15fill_blank_text.png" alt="'.get_lang('Fillinaword').'" title="'.get_lang('Fillinaword').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=16&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/16fill_math.png" alt="'.get_lang('Calculatedanswer').'" title="'.get_lang('Calculatedanswer').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=17&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/17fill_table.png" alt="'.get_lang('Itemtable').'" title="'.get_lang('Itemtable').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=18&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/18listening_comprehension.png" alt="'.get_lang('Listeningcomprehension').'" title="'.get_lang('Listeningcomprehension').'"></a></td></tr><tr><td style="height:81px;"><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=19&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/crosswords.png" alt="'.get_lang('Crosswords').'" title="'.get_lang('Crosswords').'"></a></td></tr></table></div></td>
<td valign="top"><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=20&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/19open_question.png" alt="'.get_lang('Openquestion').'" title="'.get_lang('Openquestion').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=21&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/20bopen_justify_mc.png" alt="'.get_lang('Multiplechoicejustified').'" title="'.get_lang('Multiplechoicejustified').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=22&fromExercise='.$fromExercise.$add_params_for_lp.'"><img src="../img/quizgallery/20open_map.png" alt="'.get_lang('Commentmap').'" title="'.get_lang('Commentmap').'"></a></td></tr><tr><td style="height:81px;">&nbsp;</td></tr><tr><td style="height:81px;">&nbsp;</td></tr></table></div></td>
<td valign="top"><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=23&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=4"><img src="../img/quizgallery/21matching.png" alt="'.get_lang('Wordsmatching').'" title="'.get_lang('Wordsmatching').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=24&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=4"><img src="../img/quizgallery/22ordering.png" alt="'.get_lang('Makerightsequence').'" title="'.get_lang('Makerightsequence').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=25&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=4"><img src="../img/quizgallery/23bmatch_assemble_proof.png" alt="'.get_lang('Logicevidence').'" title="'.get_lang('Logicevidence').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=26&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=4"><img src="../img/quizgallery/23match_image.png" alt="'.get_lang('Imagesmatching').'" title="'.get_lang('Imagesmatching').'"></a></td></tr><tr><td style="height:81px;">&nbsp;</td></tr></table></div></td>
<td valign="top"><div class="quiztpl_actions"><table><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=27&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=6"><img src="../img/quizgallery/24hotspots.png" alt="'.get_lang('Imagezone').'" title="'.get_lang('Imagezone').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=28&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=6"><img src="../img/quizgallery/25hotspots_organigram.png" alt="'.get_lang('Sequencediagram').'" title="'.get_lang('Sequencediagram').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=29&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=6"><img src="../img/quizgallery/26hotspots_screen.png" alt="'.get_lang('Sequencescreenshot').'" title="'.get_lang('Sequencescreenshot').'"></a></td></tr><tr><td><a href="admin.php?'.api_get_cidreq().'&fromTpl=1&editQuestion=30&fromExercise='.$fromExercise.$add_params_for_lp.'&answerType=6"><img src="../img/quizgallery/27hotspots_table.png" alt="'.get_lang('Datatable').'" title="'.get_lang('Datatable').'"></a></td></tr><tr><td style="height:81px;">&nbsp;</td></tr></table></div></td>
</tr></table>';
	
         } else {
          // if not admin of course
          api_not_allowed(true);
         }
 ?>

 </div>

<?php
  if (api_is_allowed_to_edit ()) {
	  $organize_lang_var = api_convert_encoding(get_lang('Organize'), $charset, api_get_system_encoding());	  
 ?>
          <div class="actions">
		  <?php
		  if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
     $return = '';
     // The lp_id parameter will be added by Javascript
  //   $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '">' . Display::return_icon('build.png', get_lang('Build')).get_lang("Build") . '</a>';
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&gradebook=&action=admin_view">' . Display::return_icon('organize.png', $organize_lang_var).$organize_lang_var . '</a>';
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&gradebook=&action=view">' . Display::return_icon('view.png', get_lang('ViewRight')).get_lang("ViewRight") . '</a>';
     echo $return;
   } else {
	   ?>
           <a href="<?php echo 'exercice.php?show=result&' . api_get_cidreq(); ?>"><?php echo Display :: return_icon('reporting32.png', get_lang('Tracking')) . get_lang('Tracking') ?></a>
           <a href="<?php echo 'question_pool.php?fromExercise=' . Security::remove_XSS($_GET['exerciseId']) . '&' . api_get_cidreq(); ?>"><?php echo Display :: return_icon('pool.png', get_lang('QuizQuestionsPool')) . get_lang('QuizQuestionsPool') ?></a>
		   <?php
   }
	   ?>
          </div>
<?php
  }
  Display::display_footer();
?>
