<?php // $Id: question_list_admin.inc.php 20810 2009-05-18 21:16:22Z cfasanando $

/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
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
*	Code library for HotPotatoes integration.
*	@package dokeos.exercise
* 	@author
* 	@version $Id: question_list_admin.inc.php 20810 2009-05-18 21:16:22Z cfasanando $
*/


/**
==============================================================================
*	QUESTION LIST ADMINISTRATION
*
*	This script allows to manage the question list
*	It is included from the script admin.php
*
*	@author Olivier Brouckaert
*	@package dokeos.exercise
==============================================================================
*/
global $charset;
// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// moves a question up in the list
if(isset($_GET['moveUp']))
{
	$objExercise->moveUp(intval($_GET['moveUp']));
	$objExercise->save();
}

// moves a question down in the list
if(isset($_GET['moveDown']))
{
	$objExercise->moveDown(intval($_GET['moveDown']));
	$objExercise->save();
}

if(isset($_GET['action']) && $_GET['action'] == 'changeCategory') {
  $sql = "UPDATE $TBL_QUESTIONS SET category='" . Database::escape_string(Security::remove_XSS($_GET['category'])) . "' WHERE id = " . Database::escape_string(Security::remove_XSS($_GET['question_id']));
  $res = Database::query($sql, __FILE__, __LINE__);
}

// deletes a question from the exercise (not from the data base)
if($deleteQuestion)
{

	// if the question exists
	if($objQuestionTmp = Question::read($deleteQuestion))
	{
		$objQuestionTmp->delete($exerciseId);

		// if the question has been removed from the exercise
		if($objExercise->removeFromList($deleteQuestion))
		{
			$nbrQuestions--;
		}
	}

	// destruction of the Question object
	unset($objQuestionTmp);
}

if(isset($_REQUEST['updatelevel']))
{		
	$sql = "UPDATE $TBL_QUESTIONS SET level='" . Database::escape_string(Security::remove_XSS($_REQUEST['updatelevel'])) . "' WHERE id='" . Database::escape_string(Security::remove_XSS($_REQUEST['no'])) . "'";
    api_sql_query($sql, __FILE__, __LINE__);
}

echo '<script>function level(questionlevel,no)
	  {		 		  
		  window.location.href="'.api_get_self().'?'.api_get_cidReq().'&updatelevel="+questionlevel+"&no="+no+"&exerciseId='.$exerciseId.'";
		  
	  }</script>';

if (!isset($feedbacktype)) $feedbacktype=0;
if ($feedbacktype==1) { 
    $url = 'question_pool.php?type=1&fromExercise='.$exerciseId;
} else {
    $url = 'question_pool.php?fromExercise='.$exerciseId;
}

Question :: display_type_menu ($objExercise->feedbacktype);
$move_lang_var = api_convert_encoding(get_lang('Move'), $charset, api_get_system_encoding());
$modify_lang_var = api_convert_encoding(get_lang('Modify'), $charset, api_get_system_encoding());
$question_lang_var = api_convert_encoding(get_lang('Question'), $charset, api_get_system_encoding());
$type_lang_var = api_convert_encoding(get_lang('Type'), $charset, api_get_system_encoding());
$level_lang_var = api_convert_encoding(get_lang('Level'), $charset, api_get_system_encoding());
?>
<div id="content" class="actions">
<table class="data_table data_table_exercise" id="table_question_list" style="width:100%">
	<tr>
		<th width="8%"><?php echo $move_lang_var; ?></th>
		<th width="8%"><?php echo $modify_lang_var; ?></th>
		<th width="35%"><?php echo $question_lang_var; ?></th>
		<th width="9%"><?php echo $type_lang_var;?></th>
		<th width="9%"><?php echo $level_lang_var; ?></th>
		<?php
		if(api_get_setting('show_quizcategory') == 'true'){
		?>
		<th width="15%"><?php echo get_lang('Category'); ?></th>
		<?php
		}
		?>
		<th width="8%"><?php echo get_lang('Delete'); ?></th>
		<th width="8%"><?php echo get_lang('ViewRight'); ?></th>
	</tr></table>

<?php
//This is a temporary fix
$questionList = array();
$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST, $db_name);
$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION, $db_name);
$TBL_EXERCICE_QUESTION = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION, $db_name);

$sql = "SELECT question.id FROM $TBL_EXERCICES quiz, $TBL_QUESTIONS question, $TBL_EXERCICE_QUESTION rel_question WHERE quiz.id=rel_question.exercice_id AND rel_question.question_id = question.id AND quiz.id=".Database::escape_string(Security::remove_XSS($_REQUEST['exerciseId']))." ORDER BY rel_question.question_order";
$result = Database::query($sql, __FILE__, __LINE__);
$nbrQuestions = Database::num_rows($result);
while ($row = Database::fetch_array($result)) {
	$questionList[] = $row[0];
}
if($nbrQuestions) {
echo '<div id="contentWrap"><div id="contentLeft"><ul class="dragdrop nobullets " id="categories">';

	//$questionList = $objExercise->selectQuestionList();   

	$i=1;
	if (is_array($questionList)) {
		foreach($questionList as $id) {
			echo "<script type='text/javascript'>
			$(function() {
				$('#quiz_category_".$id."').change(function() {
				var category = $(this).val();	
				var question_id = $('input[name=\'question_id_".$id."\']').attr('value');	
				window.location.href='".api_get_self().'?'.api_get_cidReq()."&action=changeCategory&category='+category+'&question_id='+question_id+'&exerciseId=".$exerciseId."';
				});
			});
			</script>";
			$objQuestionTmp = Question :: read($id);
			echo '<tr><td>';
		    echo '<li id="recordsArray_' . $id . '" class="category">';
		    echo '<div>';
		    echo '<table class="data_table" width="100%">';

			//showQuestion($id);
		?>	
		<!--	<tr id="quiz_row_<?php echo $id ?>_<?php echo $objExercise->id ?>" <?php if($i%2==0) echo 'class="row_odd"'; else echo 'class="row_even"'; ?>>-->
				<tr <?php if($i%2==0) echo 'class="row_odd"'; else echo 'class="row_even"'; ?>>
				<td align="center" width="8%" style="cursor:pointer">
				<?php
               //echo Display::display_icon('dokeos_updown.png', get_lang('Move'));
               echo Display::display_icon('drag-and-drop.png', get_lang('Move'));
				/*if($i != 1) { ?>
						<a href="<?php echo api_get_self(); ?>?moveUp=<?php echo $id; ?>"><img src="../img/up.gif" border="0" alt="<?php echo get_lang('MoveUp'); ?>"></a>
				<?php if($i == $nbrQuestions) {
			    		echo '<img src="../img/down_na.gif">';
					}
				}*/
				/*if($i != $nbrQuestions) {
					if($i == 1){
						echo '<img src="../img/up_na.gif">';
					}
				?>
						<a href="<?php echo api_get_self(); ?>?moveDown=<?php echo $id; ?>"><img src="../img/down.gif" border="0" alt="<?php echo get_lang('MoveDown'); ?>"></a>
				<?php }*/ ?>
                </td>
				<td align="center" width="8%">
				<?php
				if(!isset($_SESSION['fromlp'])) {
                    $question_type = $objQuestionTmp->selectType();
					?>
					<a href="<?php echo api_get_self(); ?>?myid=1&type=<?php echo $question_type;?>&editQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq();?>&exerciseId=<?php echo $objExercise->id; ?>">
					<?php
				} else {
					?>
					<a href="<?php echo api_get_self(); ?>?myid=1&fromTpl=1&editQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq()?>">
					<?php
				}
                    echo Display::display_icon('edit_32.png', get_lang('Modify'));
				?>
              </a>
              </td>
				<td width="35%"><?php
                $question_title = trim($objQuestionTmp->selectTitle());
                if (!empty($question_title)) {
                  echo $question_title;
                } else {
                  echo '&nbsp;';
                }
                ?></td>
				<td align="center" width="8%"><?php
     eval('$explanation=get_lang('.get_class($objQuestionTmp).'::$explanationLangVar);');
    switch (get_class($objQuestionTmp)) {
      case 'UniqueAnswer':
        echo Display::return_icon('multiple_choice_medium.png', $explanation);
      break;
      case 'MultipleAnswer':
        echo Display::return_icon('multiple_answer_medium.png', $explanation);
      break;
      case 'FillBlanks':
        echo Display::return_icon('fill_in_the_blank_medium.png', $explanation);
      break;
      case 'Matching':
        echo Display::return_icon('drag_drop_medium.png', $explanation);
      break;
      case 'FreeAnswer':
        echo Display::return_icon('open_question_medium.png', $explanation);
      break;
      case 'Reasoning':
        echo Display::return_icon('reasoning_medium.png', $explanation);
      break;
      case 'HotSpot':
        echo Display::return_icon('hotspots_medium.png', $explanation);
      break;
    }
    ?>
    </td>
			 <td align="center" width="8%">
				<?php 
				$level = $objQuestionTmp->selectLevel(); 
				$category = $objQuestionTmp->selectCategory(); 
	
				if($level == '1') {
					$level = '<div  class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_general" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_general" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '2') {
					$level = '<div class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_general" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '3') {
					$level = '<div class="level_style_general" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_intermediate" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				if($level == '4') {
					$level = '<div class="level_style_advanced" onclick="level(\'4\',\''.$id.'\');" title="Advanced"></div><div class="level_style_intermediate" onclick="level(\'3\',\''.$id.'\');" title="Intermediate"></div><div class="level_style_beginner" onclick="level(\'2\',\''.$id.'\');" title="Beginner"></div><div class="level_style_prerequestie" onclick="level(\'1\',\''.$id.'\');" title="Prerequestie"></div>';
				}
				echo $level;
				?>
				</td>	
				<?php
				if(api_get_setting('show_quizcategory') == 'true'){
				?>
				<td align="center" width="15%">
					
					<select name="quiz_category_<?php echo $id; ?>" id="quiz_category_<?php echo $id; ?>">
					<option <?php if($category == '0') echo 'selected'; ?>>Select</option>
					<?php
					$TBL_QUIZ_CATEGORY = Database::get_course_table(TABLE_QUIZ_CATEGORY);
					$sql = "SELECT * FROM $TBL_QUIZ_CATEGORY";
					$result = api_sql_query($sql, __FILE__, __LINE__);
					while($row = Database::fetch_array($result))
					{		
						$category_title = $row['category_title'];
						if($category == $category_title) {
						  echo '<option selected>'.$row['category_title'].'</option>';
						} else {
                          echo '<option>'.$row['category_title'].'</option>';
						}
					}
					?>
					</select>
					<input type="hidden" name="question_id_<?php echo $id; ?>" value="<?php echo $id; ?>" />					
			    </td>
				<?php
				}
				?>
			 <td align="center"  width="8%">
					<a href="<?php echo api_get_self(); ?>?deleteQuestion=<?php echo $id; ?>&<?php echo api_get_cidreq()?>&exerciseId=<?php  echo $objExercise->id;?>" onclick="javascript:if(!confirm('<?php echo addslashes(api_htmlentities(get_lang('ConfirmYourChoice'))); ?>')) return false;"><img src="../img/delete.png" border="0" title="<?php echo get_lang('Delete'); ?>" alt="<?php echo get_lang('Delete'); ?>" /></a>
			    </td>
				 <td align="center" width="8%">
     <a href="admin.php?<?php echo api_get_cidreq().'&viewQuestion='.$id.'&exerciseId='.$objExercise->id; ?>">
      <?php
     echo Display::display_icon('dokeos_find.png',get_lang('ViewRight'));
     ?>
     </a></td>
			  <?php
				$i++;
				unset($objQuestionTmp);
			    ?>
			</tr></table></div></li></td></tr>
				<?php 
		}
	}
	echo '</ul></div></div>';
}
?>


<?php
if(!$i) {
	?>
	<table class="data_table" border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
	<tr>
  	<td><?php echo get_lang('NoQuestion'); ?></td>
	</tr></table>
<?php
}
?>

</div>
<div class="actions">
<?php
if (isset($_GET['lp_id']) && $_GET['lp_id'] > 0) {
	$scenario_lang_var = api_convert_encoding(get_lang('Scenario'), $charset, api_get_system_encoding());
     $return = '';
     // The lp_id parameter will be added by Javascript
     //$return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '">' . Display::return_icon('build.png', get_lang('Build')).get_lang("Build") . '</a>';
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&gradebook=&action=admin_view">' . Display::return_icon('organize.png', $scenario_lang_var).$scenario_lang_var . '</a>';
     $return.= '<a href="../newscorm/lp_controller.php?' . api_get_cidreq() . '&gradebook=&action=view">' . Display::return_icon('view.png', get_lang('ViewRight')).get_lang("ViewRight") . '</a>';
     echo $return;
   } else {
	   if(api_get_setting('show_quizcategory') == 'true'){
          echo '<a href="exercise_category.php?' . api_get_cidreq() . '">' . Display :: return_icon('category_22.png', get_lang('Categories')) . get_lang('Categories') . '</a>';
	   }
	   ?>		  
	<a href="<?php echo 'exercice.php?show=result&'.  api_get_cidreq(); ?>"><?php echo Display :: return_icon('reporting22.png', get_lang('Tracking')) . get_lang('Tracking')?></a>
	<a href="<?php echo 'question_pool.php?fromExercise='.Security::remove_XSS($_GET['exerciseId']).'&'.  api_get_cidreq(); ?>"><?php echo Display :: return_icon('pool.png', get_lang('QuizQuestionsPool')) . get_lang('QuizQuestionsPool')?></a>
	<?php
   }
	   ?>
</div>