<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.glossary
 * @author Christian Fasanando, initial version
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium, refactoring and tighter integration in Dokeos
 */

// name of the language file that needs to be included
$language_file = array('glossary');
define('DOKEOS_GLOSSARY', true);

// including the global dokeos file
require_once('../inc/global.inc.php');

// the section (tabs)
$this_section=SECTION_COURSES;

// notice for unauthorized people.
api_protect_course_script(true);

// including additional libraries
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

// additional javascript
$htmlHeadXtra[] = '<script type="text/javascript" src="'.api_get_path(WEB_LIBRARY_PATH).'javascript/jquery-1.4.2.min.js" language="javascript"></script>';
$htmlHeadXtra[] = javascript_glossary();
$htmlHeadXtra[] = '<script>
  $(document).ready(function (){
    $("div.label").attr("style","width: 100%;text-align:left");
    $("div.row").attr("style","width: 100%;;text-align:left");
    $("div.formw").attr("style","width: 100%;;text-align:left");
  });
</script>';
// setting the tool constants
$tool = TOOL_GLOSSARY;

// tracking
event_access_tool(TOOL_GLOSSARY);

// displaying the header

if (isset($_GET['action']) && ($_GET['action'] == 'addglossary' || $_GET['action'] == 'edit_glossary')) {
	$tool='GlossaryManagement';
	$interbreadcrumb[] = array ("url"=>"index.php", "name"=> get_lang('Glossary'));
}

Display::display_tool_header(get_lang(ucfirst($tool)));

// Tool introduction
Display::display_introduction_section(TOOL_GLOSSARY);

// This code must be replaced by JQUERY
echo '<script type="text/javascript">
function showGlossary(str,start)
{	
	if (str=="")
	  {
	  return;
	  }
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
		 var list=document.getElementById("glossarylist");
		 if(list)
		 	list.innerHTML=xmlhttp.responseText;
	    }
	  }
	xmlhttp.open("GET","showglossary.php?action=list&q="+str+"&start="+start,true);
	xmlhttp.send();
}
	
function showDefintion(name)
{	
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function()
	  {
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	    {
		 document.getElementById("whiteboard").innerHTML=xmlhttp.responseText;
	    }
	  }
	xmlhttp.open("GET","showglossary.php?action=showterm&q="+name,true);
	xmlhttp.send();
}
</script>';

/*
 * ====================
 *  top actions
 * ====================
 */
echo '<div class="actions">';

if (api_is_allowed_to_edit(null,true))
{
	echo '<a href="'.api_get_self().'?'.api_get_cidreq().'&action=addnew">'.Display::return_icon('glossary_add_32.png',get_lang('New Term'),array('style'=>'vertical-align:middle;')).'&nbsp;&nbsp;'.get_lang('NewTerm').'</a>';
}
else
{
	echo 'Glossary';
}
echo '</div>';


/*
 * ====================
 *  div#content
 * ====================
 */
 echo '<div id="content" class="rel">';

echo '<table align="left" cellpadding="0" width="100%"><tr><td colspan="2"><div class="div_glossary" style="width:100%;">';
echo '<tr><td valign="top"><div class="div_glossary" style="height:400px;width:90%;"><div id="glossarylist"></div></div></td>';

$editor_config = array('ToolbarSet' => 'Glossary', 'Width' => '100%', 'Height' => '250');

if(isset($_GET['action']) && ($_GET['action'] == 'addnew' || $_GET['action'] == 'editterm'))
{
	//To show Add and Edit Definition form
	echo '<td><div class="div_glossary" ><div id="whiteboard"><table align="left" cellpadding="5" width="100%"><tr>';

	echo '<td width="100%"><div class="div_glossary" style="width:96%;height:420px;border:0px;"><table align="left" width="100%"><tr><td width="80%" valign="top" align="left"><table width="100%"><tr><td width="96%" align="left"><div style="width:96%" >';
	if($_GET['action'] == 'addnew')
	{
	echo '<h3 class="orange">'.get_lang('AddDefinition').'</h3></div></td></tr><tr><td>';
	$form = new FormValidator('glossary','post', api_get_self().'?'.api_get_cidreq().'&action='.Security::remove_XSS($_GET['action']));
	$form->addElement('text', 'glossary_title', get_lang('TermName'), array('size'=>'30','class'=>'focus'));
//	$form->addElement('textarea', 'glossary_comment', get_lang('Definition'), array('rows'=>'10','cols'=>'60'));		
	$form->addElement('html_editor', 'glossary_comment', get_lang('Definition'), 'style="vertical-align:middle"', $editor_config);
	$form->addElement('style_submit_button', 'SubmitGlossary', get_lang('Validate'), 'class="save"');
	$form->addRule('glossary_title', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');	
		// The validation or display
		if ($form->validate()) {
			$check = Security::check_token('post');	
			if ($check) {
		   		$values = $form->exportValues();
		   		save_glossary($values);		   		
			}
			Security::clear_token();			
			echo '<script>window.location.href="index.php?'.api_get_cidReq().'"</script>';
		} else {
			$token = Security::get_token();
			$form->addElement('hidden','sec_token');
			$form->setConstants(array('sec_token' => $token));	
			$defaults['glossary_comment'] = ' ';
			$form->setDefaults($defaults);
			$form->display();
		}
	}
	elseif($_GET['action'] == 'editterm')
	{
		echo '<h3 class="orange">'.get_lang('TermEdit').'</h3></div></td></tr><tr><td>';
		$form = new FormValidator('glossary','post', api_get_self().'?'.api_get_cidreq().'&action='.Security::remove_XSS($_GET['action']).'&glossary_id='.Security::remove_XSS($_GET['glossary_id']));
		$form->addElement('hidden', 'glossary_id');
		$form->addElement('text', 'glossary_title', get_lang('TermName'),array('size'=>'30'));
	//	$form->addElement('textarea', 'glossary_comment', get_lang('Definition'), array('rows'=>'10','cols'=>'60'));	
		$form->addElement('html_editor', 'glossary_comment', get_lang('Definition'), 'style="vertical-align:middle"', $editor_config);
		$form->addElement('style_submit_button', 'SubmitGlossary', get_lang('SaveDefinition'), 'class="save"');
		
		// setting the defaults
		$defaults = get_glossary_information(Security::remove_XSS($_GET['glossary_id']));
		$form->setDefaults($defaults);
		
		// setting the rules
		$form->addRule('glossary_title', '<div class="required">'.get_lang('ThisFieldIsRequired'), 'required');	
		
		// The validation or display
		if ($form->validate()) {
			$check = Security::check_token('post');	
			if ($check) {
		   		$values = $form->exportValues();
		   		update_glossary($values);		   		
			}
			Security::clear_token();
			echo '<script>window.location.href="index.php?'.api_get_cidReq().'"</script>';
		} else {
			$token = Security::get_token();
			$form->addElement('hidden','sec_token');
			$form->setConstants(array('sec_token' => $token));		
			$form->display();
		}
	}
	echo '</td></tr></table></td>';
	echo '<td valign="top" width="30%">';	
	echo '</td></tr>';	
	echo '</table></td></tr></table></div></td></tr></table>';
	echo '<a href="index.php?'.api_get_cidReq().'"><img class="abs" src="../img/imagemap90.png" style="margin:30px 30px 0 0; right:0; top:0;"></a>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'delete_glossary')
{
	//To delete glossary
	delete_glossary(Security::remove_XSS($_GET['glossary_id']));
	echo '<script>window.location.href="index.php?'.api_get_cidReq().'"</script>';	
}
else
{
	//To show the list of glossary and the whiteboard to choose the Definition.	

	echo '<td width="70%"><div align="center"><div class="div_glossary" style="width:96%;height:400px;"><div id="whiteboard"><table width="100%"><tr><td width="70%" valign="top" align="center"  style="background: url(../img/whiteboard.png) no-repeat 100px 5px;"><table cellspacing="5" cellpadding="5" width="50%"><tr><td>&nbsp;</td></tr>';
	for($i=1;$i<=26;$i++)
	{
		if($i == 1 || $i == 6 || $i == 11 || $i == 15 || $i == 19 || $i == 23)
		{
			echo '<tr>';
		}
		echo '<td style="font-size:20px;font-weight:bold;font-family:verdana;color:#557EB8;"><a href="javascript:void(0)" onclick="showGlossary(\''.chr(64+$i).'\',\'0\')">'.chr(64+$i).'</a></td>';
		if($i == 5 || $i == 10 || $i == 14 || $i == 18 || $i == 22 || $i == 26)
		{
			if($i == 26)
			{
				echo '<td style="font-size:20px;font-weight:bold;font-family:verdana;color:#557EB8;"><a href="javascript:void(0)" onclick="showGlossary(\'A - Z\',\'0\')">A - Z</a></td>';
			}
			echo '</tr>';		
		}	
	}
	echo '<tr><td>&nbsp;</td></tr></table></td></tr></table></div></div></div></td>';
}

echo '</tr></table>';

echo '<img class="abs" src="../img/instructor_analysis.png" style="margin:0 0px 50px 0; right:0; bottom:0;">';

// ending div#content
echo '</div>';

//execute showGlossary('A - Z','0');
if(!isset($_GET['action'])){
    echo '<script type="text/javascript">';
    echo 'showGlossary(\'A - Z\',\'0\');';
    echo '</script>';
}



// bottom actions bar
echo '<div class="actions">';
echo '</div>';

// footer
Display::display_footer();

/*
 * ==========================================
 * end of html document - only php functions after there
 * ==========================================
 */

/**
 * This functions stores the glossary in the database
 *
 * @param unknown_type $values
 *
 * @author Christian Fasanando <christian.fasanando@dokeos.com>
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function save_glossary($values)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	// get the maximum display order of all the glossary items
	$max_glossary_item = get_max_glossary_item();

	// session_id
	$session_id = api_get_session_id();

	// check if the glossary term already exists
	if (glossary_exists($values['glossary_title']))
	{
		// display the feedback message
		Display::display_error_message('GlossaryTermAlreadyExistsYouShouldEditIt');
	} else {
		$sql = "INSERT INTO $t_glossary (name, description, display_order, session_id)
				VALUES(
					'".Database::escape_string(Security::remove_XSS($values['glossary_title']))."',
					'".Database::escape_string(Security::remove_XSS(stripslashes(api_html_entity_decode($values['glossary_comment'])),COURSEMANAGERLOWSECURITY))."',
					'".(int)($max_glossary_item + 1)."',
					'".Database::escape_string($session_id)."'
					)";
		$result = Database::query($sql, __FILE__, __LINE__);
		$id = Database::insert_id();
		if ($id>0) {
			//insert into item_property
			api_item_property_update(api_get_course_info(), TOOL_GLOSSARY, $id, 'GlossaryAdded', api_get_user_id());
		}
		$_SESSION['max_glossary_display'] = get_max_glossary_item();
		// display the feedback message
		//Display::display_confirmation_message(get_lang('TermAdded'));
	}
}

/**
 * update the information of a glossary term in the database
 *
 * @param array $values an array containing all the form elements
 *
 * @author Christian Fasanando <christian.fasanando@dokeos.com>
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function update_glossary($values)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);


	// check if the glossary term already exists
	if (glossary_exists($values['glossary_title'],$values['glossary_id']))
	{
		// display the feedback message
		Display::display_error_message('GlossaryTermAlreadyExistsYouShouldEditIt');
	}
	else
	{
		$sql = "UPDATE $t_glossary SET
						name 		= '".Database::escape_string(Security::remove_XSS($values['glossary_title']))."',
						description	= '".Database::escape_string(Security::remove_XSS(stripslashes(api_html_entity_decode($values['glossary_comment'])),COURSEMANAGERLOWSECURITY))."'
				WHERE glossary_id = ".Database::escape_string($values['glossary_id']);
		$result = Database::query($sql, __FILE__, __LINE__);
		//update glossary into item_property
		api_item_property_update(api_get_course_info(), TOOL_GLOSSARY, Database::escape_string($values['glossary_id']), 'GlossaryUpdated', api_get_user_id());
		// display the feedback message
		//Display::display_confirmation_message(get_lang('TermUpdated'));
	}
}

/**
 * Get the maximum display order of the glossary item
 *
 * @author Christian Fasanando <christian.fasanando@dokeos.com>
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function get_max_glossary_item()
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$get_max = "SELECT MAX(display_order) FROM $t_glossary";
	$res_max = Database::query($get_max, __FILE__, __LINE__);
	$dsp=0;
	$row = Database::fetch_array($res_max);
	return $row[0];
}

/**
 * check if the glossary term exists or not
 *
 * @param unknown_type $term
 * @param unknown_type $not_id
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function glossary_exists($term,$not_id='')
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$sql = "SELECT name FROM $t_glossary WHERE name = '".Database::escape_string($term)."'";
	if ($not_id<>'')
	{
		$sql .= " AND glossary_id <> '".Database::escape_string($not_id)."'";
	}
	$result = Database::query($sql,__FILE__,__LINE__);
	$count = Database::num_rows($result);
	if ($count > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}
/**
 * get all the information about one specific glossary term
 *
 * @param unknown_type $glossary_id
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function get_glossary_information($glossary_id)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);
	$t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);

	$sql = "SELECT 	g.glossary_id 		AS glossary_id,
					g.name 				AS glossary_title,
					g.description 		AS glossary_comment,
					g.display_order		AS glossary_display_order
			   FROM $t_glossary g, $t_item_propery ip
			   WHERE g.glossary_id = ip.ref
			   AND tool = '".TOOL_GLOSSARY."'
			   AND g.glossary_id = '".Database::escape_string($glossary_id)."' ";
	$result = Database::query($sql, __FILE__, __LINE__);
	return Database::fetch_array($result);
}

/**
 * Delete a glossary term (and re-order all the others)
 *
 * @param integer $glossary_id the id of the glossary
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function delete_glossary($glossary_id)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$sql = "DELETE FROM $t_glossary WHERE glossary_id='".Database::escape_string($glossary_id)."'";
	$result = Database::query($sql, __FILE__, __LINE__);

	//update item_property (delete)
	api_item_property_update(api_get_course_info(), TOOL_GLOSSARY, Database::escape_string($glossary_id), 'delete', api_get_user_id());

	// reorder the remaining terms
	reorder_glossary();
	$_SESSION['max_glossary_display'] = get_max_glossary_item();
	//Display::display_confirmation_message(get_lang('TermDeleted'));
}

/**
 * This is the main function that display the list or the table with all the glossary terms
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function display_glossary()
{
	// action links
	echo '<div class="actions" style="margin-bottom:10px">';
	if (api_is_allowed_to_edit(null,true))
	{
		echo '<a href="index.php?'.api_get_cidreq().'&action=addglossary&msg=add">'.Display::return_icon('filenew.gif',get_lang('TermAddNew')).get_lang('TermAddNew').'</a>';
	}

	if ((isset($_SESSION['glossary_view']) && $_SESSION['glossary_view'] == 'table') or (!isset($_SESSION['glossary_view']))){
		echo '<a href="index.php?'.api_get_cidreq().'&action=changeview&view=list">'.Display::return_icon('view_list.gif',get_lang('ListView')).get_lang('ListView').'</a>';
	} else {
		echo '<a href="index.php?'.api_get_cidreq().'&action=changeview&view=table">'.Display::return_icon('view_table.gif',get_lang('TableView')).get_lang('TableView').'</a>';
	}
	echo '</div>';
	if (!$_SESSION['glossary_view'] OR $_SESSION['glossary_view'] == 'table')
	{
		$table = new SortableTable('glossary', 'get_number_glossary_terms', 'get_glossary_data',0);
		$table->set_header(0, get_lang('DisplayOrder'), true);
		$table->set_header(1, get_lang('TermName'), true);
		$table->set_header(2, get_lang('TermDefinition'), true);
		$table->set_header(3, get_lang('CreationDate'), false);
		$table->set_header(4, get_lang('UpdateDate'), false);
		if (api_is_allowed_to_edit(null,true)) {
			$table->set_header(5, get_lang('Actions'), false);
			$table->set_column_filter(5, 'actions_filter');
		}
		$table->display();
	}
	if ($_SESSION['glossary_view'] == 'list')
	{
		display_glossary_list();
	}
}

/**
 * display the glossary terms in a list
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function display_glossary_list()
{
	$glossary_data = get_glossary_data(0,1000,0,ASC);
	foreach($glossary_data as $key=>$glossary_item)
	{
		echo '<div class="sectiontitle">'.$glossary_item[1].'</div>';
		echo '<div class="sectioncomment">'.$glossary_item[2].'</div>';
		if (api_is_allowed_to_edit(null,true)) {
			echo '<div>'.actions_filter($glossary_item[5], '',$glossary_item).'</div>';
		}
	}
}

/**
 * Get the number of glossary terms
 *
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function get_number_glossary_terms()
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$sql = "SELECT count(glossary_id) as total FROM $t_glossary";
	$res = Database::query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total;
}

/**
 * get all the data of the glossary
 *
 * @param unknown_type $from
 * @param unknown_type $number_of_items
 * @param unknown_type $column
 * @param unknown_type $direction
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function get_glossary_data($from, $number_of_items, $column, $direction)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);
	$t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);

	if (api_is_allowed_to_edit(null,true)) {
		$col5 = ", glossary.glossary_id	as col5";
	} else {
		$col5 = " ";
	}

	//condition for the session
	$session_id = api_get_session_id();
	$condition_session = api_get_session_condition($session_id);

	$sql = "SELECT
				glossary.display_order 	as col0,
				glossary.name 			as col1,
				glossary.description 	as col2,
				ip.insert_date			as col3,
				ip.lastedit_date		as col4
				$col5,
				glossary.session_id as session_id
			FROM $t_glossary glossary, $t_item_propery ip
			WHERE glossary.glossary_id = ip.ref
			AND tool = '".TOOL_GLOSSARY."' $condition_session";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";

	$res = Database::query($sql, __FILE__, __LINE__);

	$return = array();
	$array = array();
	while ($data = Database::fetch_array($res)) {

		$array[0] = $data[0];

		//validacion when belongs to a session
		$session_img = api_get_session_image($data['session_id'], $_user['status']);
		$array[1] = $data[1] . $session_img;

		if (!$_SESSION['glossary_view'] || $_SESSION['glossary_view'] == 'table') {
			$array[2] = str_replace(array('<p>','</p>'),array('','<br />'),$data[2]);
		} else {
			$array[2] = $data[2];
		}

		$array[3] = $data[3];
		$array[4] = $data[4];

		if (api_is_allowed_to_edit(null,true)) {
			$array[5] = $data[5];
		}

		$return[] = $array;
	}

	return $return;
}

/**
 * Enter description here...
 *
 * @param unknown_type $glossary_id
 * @param unknown_type $url_params
 * @param unknown_type $row
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function actions_filter($glossary_id,$url_params,$row)
{
	if (!$_SESSION['max_glossary_display'] OR $_SESSION['max_glossary_display'] == '')
	{
		$_SESSION['max_glossary_display'] = get_max_glossary_item();
	}

	if (empty($_GET['glossary_column'])) {
		if ($row[0] > 1)
		{
			$return .= '<a href="'.api_get_self().'?action=moveup&amp;glossary_id='.$row[5].'">'.Display::return_icon('up.gif', get_lang('Up')).'</a>';
		}
		else
		{
			$return .= Display::return_icon('up_na.gif','&nbsp;');

		}
		if ($row[0] < $_SESSION['max_glossary_display'])
		{
			$return .= '<a href="'.api_get_self().'?action=movedown&amp;glossary_id='.$row[5].'">'.Display::return_icon('down.gif',get_lang('Down')).'</a>';
		}
		else
		{
			$return .= Display::return_icon('down_na.gif','&nbsp;');

		}
	}
	$return .= '<a href="'.api_get_self().'?action=edit_glossary&amp;glossary_id='.$row[5].'&msg=edit">'.Display::return_icon('edit_link.png',get_lang('Edit')).'</a>';

	$glossary_data = get_glossary_information($row[5]);
	$glossary_term = $glossary_data['glossary_title'];

	$return .= '<a href="'.api_get_self().'?action=delete_glossary&amp;glossary_id='.$row[5].'" onclick="return confirmation(\''.$glossary_term.'\');">'.Display::return_icon('delete.png', get_lang('Delete')).'</a>';
	return $return;
}

/**
 * a little bit of javascript to display a prettier warning when deleting a term
 *
 * @return unknown
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function javascript_glossary()
{
	return "<script type=\"text/javascript\">
			function confirmation (name)
			{
				if (confirm(\" ". get_lang("TermConfirmDelete") ." \"+ name + \" ?\"))
					{return true;}
				else
					{return false;}
			}
			</script>";
}

/**
 * Enter description here...
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function reorder_glossary()
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	$sql = "SELECT * FROM $t_glossary ORDER by display_order ASC";
	$res = Database::query($sql, __FILE__, __LINE__);

	$i = 1;
	while ($data = Database::fetch_array($res))
	{
		$sql_reorder = "UPDATE $t_glossary SET display_order = $i WHERE glossary_id = '".Database::escape_string($data['glossary_id'])."'";
		Database::query($sql_reorder, __FILE__, __LINE__);
		$i++;
	}
}

/**
 * Enter description here...
 *
 * @param unknown_type $direction
 * @param unknown_type $glossary_id
 *
 * @author Patrick Cool <patrick.cool@ugent.be>, Ghent University, Belgium
 * @version januari 2009, dokeos 1.8.6
 */
function move_glossary($direction, $glossary_id)
{
	// Database table definition
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);

	// sort direction
	if ($direction == 'up')
	{
		$sortorder = 'DESC';
	}
	else
	{
		$sortorder = 'ASC';
	}

	$sql = "SELECT * FROM $t_glossary ORDER BY display_order $sortorder";
	$res = Database::query($sql, __FILE__, __LINE__);
	while ($row = Database::fetch_array($res))
	{
		if ($found == true and empty($next_id))
		{
			$next_id = $row['glossary_id'];
			$next_display_order = $row['display_order'];
		}

		if ($row['glossary_id'] == $glossary_id)
		{
			$current_id = $glossary_id;
			$current_display_order = $row['display_order'];
			$found = true;
		}

	}

	$sql1 = "UPDATE $t_glossary SET display_order = '".Database::escape_string($next_display_order)."' WHERE glossary_id = '".Database::escape_string($current_id)."'";
	$sql2 = "UPDATE $t_glossary SET display_order = '".Database::escape_string($current_display_order)."' WHERE glossary_id = '".Database::escape_string($next_id)."'";
	$res = Database::query($sql1, __FILE__, __LINE__);
	$res = Database::query($sql2, __FILE__, __LINE__);

	Display::display_confirmation_message(get_lang('TermMoved'));
}
