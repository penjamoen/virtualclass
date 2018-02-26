<?php
/* For licensing terms, see /dokeos_license.txt */
/**
==============================================================================
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Dokeos Module. It can be inserted on any
 * Dokeos Module, provided a connection to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction"
 * in the course Database. Each module introduction has an Id stored on
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   intro_text :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
*
*	@package dokeos.include
==============================================================================
*/

include_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
$TBL_INTRODUCTION = Database::get_course_table(TABLE_TOOL_INTRO);
$intro_editAllowed = $is_allowed_to_edit;

global $charset;
$intro_cmdEdit = (empty($_GET['intro_cmdEdit'])?'':$_GET['intro_cmdEdit']);
$intro_cmdUpdate = isset($_POST['intro_cmdUpdate'])?true:false;
$intro_cmdDel= (empty($_GET['intro_cmdDel'])?'':$_GET['intro_cmdDel']);
$intro_cmdAdd= (empty($_GET['intro_cmdAdd'])?'':$_GET['intro_cmdAdd']);

if (!empty ($GLOBALS["_cid"])) {
	$form = new FormValidator('introduction_text', 'post', api_get_self()."?".api_get_cidreq());
} else {
	$form = new FormValidator('introduction_text');
}
$renderer =& $form->defaultRenderer();

$toolbar_set = 'Introduction';
$width = '100%';
$height = '200';

// The global variable $fck_attribute has been deprecated. It stays here for supporting old external code.
global $fck_attribute;
if (is_array($fck_attribute)) {
	if (isset($fck_attribute['ToolbarSet'])) {
		$toolbar_set = $fck_attribute['ToolbarSet'];
	}
	if (isset($fck_attribute['Width'])) {
		$toolbar_set = $fck_attribute['Width'];
	}
	if (isset($fck_attribute['Height'])) {
		$toolbar_set = $fck_attribute['Height'];
	}
}

if (is_array($editor_config)) {
	if (!isset($editor_config['ToolbarSet'])) {
		$editor_config['ToolbarSet'] = $toolbar_set;
	}
	if (!isset($editor_config['Width'])) {
		$editor_config['Width'] = $width;
	}
	if (!isset($editor_config['Height'])) {
		$editor_config['Height'] = $height;
	}
} else {
	$editor_config = array('ToolbarSet' => $toolbar_set, 'Width' => $width, 'Height' => $height);
}


if (isset($_GET['display_template']) && $_GET['display_template'] == 1 && $tool == TOOL_COURSE_HOMEPAGE) {
  // 5 buttons for display the scenarios in the course home page
  $html_buttons = '<div align="center">
        <table class="gallery"><tbody>
        <tr><td><a href="'.  api_get_self().'?scenario=activity">
        <div class="section_scenario width_scenario_button" >
        <div class="sectiontitle">'.get_lang('Activities').'</div>
			<div class="">'.Display::return_icon('quiz_64.png',get_lang('Activities')).'</div>
		</div></a></td>
        <td><a href="'.  api_get_self().'?scenario=social">
          <div class="section_scenario width_scenario_button">
			<div class="sectiontitle">'.get_lang('Social').'</div>
			<div class="">'.Display::return_icon('social64.png',get_lang('Social')).'</div>
		</div></a></td>
        <td><a href="'.  api_get_self().'?scenario=week">
          <div class="section_scenario width_scenario_button" >
			<div class="sectiontitle">'.get_lang('Weeks').'</div>
			<div class="">'.Display::return_icon('weeks64.png',get_lang('Weeks')).'</div>
		</div></a></td>
        <td><a href="'.  api_get_self().'?scenario=corporate">
          <div class="section_scenario width_scenario_button" >
			<div class="sectiontitle">'.get_lang('Corporate').'</div>
			<div class="">'.Display::return_icon('corp64.png',get_lang('Corporate')).'</div>
		</div></a></td>
        <td><a href="'.  api_get_self().'?scenario=none">
          <div class="section_scenario width_scenario_button" >
			<div class="sectiontitle">'.get_lang('NoScenario').'</div>
			<div class="">'.Display::return_icon('noscenario64.png',get_lang('NoScenario')).'</div>
		</div></a></td>
        </tr></tbody>
        </table></div>';
  $form->addElement('html',$html_buttons);
} else {
  $form->add_html_editor('intro_content', null, null, false, $editor_config);
  $form->addElement('style_submit_button', 'intro_cmdUpdate', get_lang('SaveIntroText'), 'class="save"');
}



/*=========================================================
  INTRODUCTION MICRO MODULE - COMMANDS SECTION (IF ALLOWED)
  ========================================================*/

if ($intro_editAllowed) {
	/* Replace command */

	if ( $intro_cmdUpdate ) {
		if ( $form->validate()) {

			$form_values = $form->exportValues();
			$intro_content = Security::remove_XSS(stripslashes(api_html_entity_decode($form_values['intro_content'])), COURSEMANAGERLOWSECURITY);
            if (empty($intro_content) ) {
              $intro_content = "&nbsp;";
            }
			if (! empty($intro_content) ) {
				$sql = "REPLACE $TBL_INTRODUCTION SET id='$moduleId',intro_text='".Database::escape_string($intro_content)."'";
				Database::query($sql,__FILE__,__LINE__);
				Display::display_confirmation_message(get_lang('IntroductionTextUpdated'),false);
			} else {
				$intro_cmdDel = true;	// got to the delete command
			}

		} else {
			$intro_cmdEdit = true;
		}
	}

	/* Delete Command */

	if ($intro_cmdDel) {
		Database::query("DELETE FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'",__FILE__,__LINE__);
		Display::display_confirmation_message(get_lang('IntroductionTextDeleted'));
	}

}


/*===========================================
  INTRODUCTION MICRO MODULE - DISPLAY SECTION
  ===========================================*/

/* Retrieves the module introduction text, if exist */

$sql = "SELECT intro_text FROM $TBL_INTRODUCTION WHERE id='".$moduleId."'";
$intro_dbQuery = Database::query($sql,__FILE__,__LINE__);
$intro_dbResult = Database::fetch_array($intro_dbQuery);
if ($intro_cmdUpdate && empty($intro_content)) {
$intro_content = "&nbsp;";
} else {
$intro_content = $intro_dbResult['intro_text'];
}


/* Determines the correct display */

if ($intro_cmdEdit || $intro_cmdAdd) {
	$intro_dispDefault = false;
	$intro_dispForm = true;
	$intro_dispCommand = false;
} else {

	$intro_dispDefault = true;
	$intro_dispForm = false;

	if ($intro_editAllowed) {
		$intro_dispCommand = true;
	} else {
		$intro_dispCommand = false;
	}

}

/* Executes the display */

if ($intro_dispForm || isset($_GET['scenario'])) {
    if (isset($_GET['scenario']) && $_GET['scenario'] != 'none' && $tool == TOOL_COURSE_HOMEPAGE) {
          $image_arrow = Display::return_icon('media_playback_start_32.png', get_lang('Activity'),array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
        if ($_GET['scenario'] == 'activity') {
          $lang_var1 = get_lang('ActivityOne');
          $lang_var2 = get_lang('ActivityTwo');
          $lang_var3 = get_lang('ActivityThree');
          $lang_var4 = get_lang('ActivityFour');
          $lang_var5 = get_lang('ActivityFive');

          $image1 = Display::return_icon('quiz_64.png', $lang_var1,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('applications_accessories_64.png', $lang_var2,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('mouse_64.png', $lang_var3,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('accessories-character-map.png', $lang_var4,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('miscellaneous.png', $lang_var5,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($_GET['scenario'] == 'corporate') {
          $lang_var1 = get_lang('Corporate');
          $image1 = Display::return_icon('trainerleft.png', $lang_var1,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('textright.png', $lang_var1,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($_GET['scenario'] == 'social') {
          $lang_var1 = get_lang('InteractionOne');
          $lang_var2 = get_lang('InteractionTwo');
          $lang_var3 = get_lang('InteractionThree');
          $lang_var4 = get_lang('InteractionFour');
          $lang_var5 = get_lang('InteractionFive');

          $image1 = Display::return_icon('group_blue.png', $lang_var1,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('group_orange.png', $lang_var2,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('presence_64.png', $lang_var3,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('group_red.png', $lang_var4,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('group_grey.png', $lang_var5,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));

        } elseif ($_GET['scenario'] == 'week') {
          $lang_var1 = get_lang('WeekOne');
          $lang_var2 = get_lang('WeekTwo');
          $lang_var3 = get_lang('WeekThree');
          $lang_var4 = get_lang('WeekFour');
          $lang_var5 = get_lang('WeekFive');

          $image1 = Display::return_icon('presence_64.png', $lang_var1,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image2 = Display::return_icon('media_podcasts.png', $lang_var2,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image3 = Display::return_icon('link_64.png', $lang_var3,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image4 = Display::return_icon('newpage.png', $lang_var4,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));
          $image5 = Display::return_icon('01time.png', $lang_var5,array('border' => '0', 'align' => 'absmiddle', 'vspace'=> '0','hspace'=> '0'));

        }
        if ($_GET['scenario'] != 'corporate') {
         $intro_content = '<div align="center"><table cellspacing="2" cellpadding="10" border="0" align="center" style="width: 800px; height: 130px;"><tbody>
              <tr>
                  <td style="text-align: center;">'.$image1.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image2.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image3.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image4.'</td>
                  <td style="text-align: center;">'.$image_arrow.'</td>
                  <td style="text-align: center;">'.$image5.'</td>
              </tr>
              <tr>
                  <td style="text-align: center;">&nbsp;'.$lang_var1.'</td>
                  <td style="text-align: center;">&nbsp;</td>
                  <td style="text-align: center;">&nbsp;'.$lang_var2.'</td>
                  <td style="text-align: center;">&nbsp;</td>
                  <td style="text-align: center;">&nbsp;'.$lang_var3.'</td>
                  <td style="text-align: center;">&nbsp;</td>
                  <td style="text-align: center;">&nbsp;'.$lang_var4.'</td>
                  <td style="text-align: center;">&nbsp;</td>
                  <td style="text-align: center;">&nbsp;'.$lang_var5.'</td>
              </tr>
          </tbody>
        </table>
        </div>';
        } else {
          $intro_content = '<div align="center">
          <table width="420" cellspacing="0" cellpadding="0" border="0" align="center">
          <tbody>
              <tr>
                  <td width="356">'.$image1.'</td>
                  <td width="58">'.$image2.'</td>
              </tr>
          </tbody>
        </table>
      </div>';
        }
    } elseif (isset($_GET['scenario']) && $_GET['scenario'] == 'none' && $tool == TOOL_COURSE_HOMEPAGE) {
      $intro_content = "&nbsp;";
    }

    $default['intro_content'] = $intro_content;
	$form->setDefaults($default);

    // Actions bar for display the scenario icons
    if (((isset($_GET['display_template']) && $_GET['display_template'] == 0) || (isset($_GET['scenario']))) && $tool == TOOL_COURSE_HOMEPAGE) {
        $get_intro_cmdEdit = Security::remove_XSS($_GET['intro_cmdEdit']);
        echo '<div class="actions">';
        echo '<a href="'. api_get_self().'?intro_cmdEdit='.$get_intro_cmdEdit.'&amp;display_template=0&amp;scenario=activity">'.Display::return_icon('quiz.png',get_lang('Activities')).get_lang('Activities').'</a>';
        echo '<a href="'. api_get_self().'?intro_cmdEdit='.$get_intro_cmdEdit.'&amp;display_template=0&amp;scenario=social">'.Display::return_icon('social32.png',get_lang('Social')).get_lang('Social').'</a>';
        echo '<a href="'. api_get_self().'?intro_cmdEdit='.$get_intro_cmdEdit.'&amp;display_template=0&amp;scenario=week">'.Display::return_icon('weeks32.png',get_lang('Weeks')).get_lang('Weeks').'</a>';
        echo '<a href="'. api_get_self().'?intro_cmdEdit='.$get_intro_cmdEdit.'&amp;display_template=0&amp;scenario=corporate">'.Display::return_icon('corp32.png',get_lang('Corporate')).get_lang('Corporate').'</a>';
        echo '<a href="'. api_get_self().'?intro_cmdEdit='.$get_intro_cmdEdit.'&amp;display_template=0&amp;scenario=none">'.Display::return_icon('noscenario32.png',get_lang('NoScenario')).get_lang('NoScenario').'</a>';
        echo '</div>';
    }
    // Display course intro
	echo '<div id="courseintro">';
	$form->display();
	echo '</div>';
}

if ($intro_dispDefault && !isset($_GET['scenario'])) {
	//$intro_content = make_clickable($intro_content); // make url in text clickable
	$intro_content = text_filter($intro_content); // parse [tex] codes
	if (!empty($intro_content))	{
		echo '<div id="courseintroduction">';
		echo $intro_content;
		echo '</div>';
	}
}

if ($intro_dispCommand  && !isset($_GET['scenario'])) {

	if ( empty($intro_content) ) {

		//displays "Add intro" Commands
		echo "<div id=\"courseintro\">\n";
		if (!empty ($GLOBALS["_cid"])) {
          // Add param for display the templates(4 buttons)
          $add_param_display_template = "";
          if ($tool == TOOL_COURSE_HOMEPAGE) {
            $add_param_display_template = "&amp;display_template=1";
          }
          echo "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdAdd=1".$add_param_display_template."\">\n".get_lang('AddIntro')."</a>\n";
		} else {
			echo "<a href=\"".api_get_self()."?intro_cmdAdd=1\">\n".get_lang('AddIntro')."</a>\n";
		}
		echo "\n</div>";

	} else {
        $content_without_space = str_replace('&nbsp;','',$intro_content);
        $add_param_display_template = "";
        if ($tool == TOOL_COURSE_HOMEPAGE) {
           $add_param_display_template = '&amp;display_template=0';
           if (strlen($content_without_space) == 0) {
              $add_param_display_template = '&amp;display_template=1';
           }
        }

		// displays "edit intro && delete intro" Commands
		echo "<div id=\"courseintro_icons\">\n";
		if (!empty ($GLOBALS["_cid"])) {
			echo "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdDel=1\" onclick=\"javascript:if(!confirm('".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset))."')) return false;\"><img src=\"".api_get_path(WEB_CODE_PATH)."img/delete.png\" alt=\"".get_lang('Delete')."\" border=\"0\" /></a>\n";
			echo "<a href=\"".api_get_self()."?".api_get_cidreq()."&amp;intro_cmdEdit=1".$add_param_display_template."\"><img src=\"".api_get_path(WEB_CODE_PATH)."img/edit_link.png\" alt=\"".get_lang('Modify')."\" border=\"0\" /></a>\n";
		} else {
			echo "<a href=\"".api_get_self()."?intro_cmdDel=1\" onclick=\"javascript:if(!confirm('".addslashes(api_htmlentities(get_lang('ConfirmYourChoice'),ENT_QUOTES,$charset))."')) return false;\"><img src=\"".api_get_path(WEB_CODE_PATH)."img/delete.png\" alt=\"".get_lang('Delete')."\" border=\"0\" /></a>\n";
			echo "<a href=\"".api_get_self()."?intro_cmdEdit=1\"><img src=\"".api_get_path(WEB_CODE_PATH)."img/edit_link.png\" alt=\"".get_lang('Modify')."\" border=\"0\" /></a>\n";
		}
		echo "</div>";

	}

}
?>
