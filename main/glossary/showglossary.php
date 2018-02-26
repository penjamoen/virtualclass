<?php //$id: $
/* For licensing terms, see /dokeos_license.txt */
/**
 * @package dokeos.glossary
 */

$language_file = array('glossary');
define('DOKEOS_GLOSSARY', true);

// including the global dokeos file
require_once('../inc/global.inc.php');
require_once('../inc/lib/events.lib.inc.php');

// notice for unauthorized people.
api_protect_course_script(true);

//This is to show the list of glossaries when you click the letters in the whiteboard.
/**
 * Fix encoding problems on display
 * 
 * @param $str - string to check
 * @since 2010.08.26 - K.Vincendeau
 */
function convert_encoding($str = ""){
 global $charset;
	return api_convert_encoding($str, 'utf-8', $charset);
}

if(isset($_GET['action']) && $_GET['action'] == 'list')
{	
	$current_glossary = $_GET['q'];	
	echo '<div style="height:370px;"><table align="left" width="100%"><tr><td align="left"><div class="squarebox_white">'.Security::remove_XSS($_GET['q']).'</div></td></tr><tr><td>&nbsp;</td></tr>';
	
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);
	$t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);	
	$from = $_GET['start'];
	$inc_cnt = 10;
	$number_of_items = $from + $inc_cnt;	

	if($_GET['q'] == 'A - Z')
	{
	$query = "SELECT 
				glossary.glossary_id as id, glossary.name as name FROM $t_glossary glossary, $t_item_propery ip 
			WHERE glossary.glossary_id = ip.ref 
			AND tool = '".TOOL_GLOSSARY."' ";	
	$query .= " ORDER BY glossary.name ASC";

	$sql = "SELECT 
				glossary.glossary_id as id, glossary.name as name FROM $t_glossary glossary, $t_item_propery ip 
			WHERE glossary.glossary_id = ip.ref 
			AND tool = '".TOOL_GLOSSARY."' ";	
	$sql .= " ORDER BY glossary.name ASC";
	$sql .= " LIMIT $from,$inc_cnt";
	}
	else
	{
	$query = "SELECT 
				glossary.glossary_id as id, glossary.name as name FROM $t_glossary glossary, $t_item_propery ip 
			WHERE glossary.glossary_id = ip.ref 
			AND tool = '".TOOL_GLOSSARY."' AND glossary.name LIKE '".Database::escape_string(Security::remove_XSS($_GET['q']))."%'";
	$query .= " ORDER BY glossary.name ASC";

	$sql = "SELECT 
				glossary.glossary_id as id, glossary.name as name FROM $t_glossary glossary, $t_item_propery ip 
			WHERE glossary.glossary_id = ip.ref 
			AND tool = '".TOOL_GLOSSARY."' AND glossary.name LIKE '".Database::escape_string(Security::remove_XSS($_GET['q']))."%'";
	$sql .= " ORDER BY glossary.name ASC";
	$sql .= " LIMIT $from,$number_of_items";
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$numresult = api_sql_query($query, __FILE__, __LINE__);
	$numrows = Database::num_rows($numresult);

	if($from <> 0)
	{
		$previous = $from - $inc_cnt;
	}
	if($number_of_items < $numrows)
	{
		$next = $from + $inc_cnt;
	}	
	if($numrows <> 0)
	{		
		while ($data = Database::fetch_object($res))
		{	
			$id = $data->id;
			$name = convert_encoding($data->name);
			echo '<tr><td><span style="font-size:14px;font-weight:normal;font-family:verdana;color:#557EB8;padding-left:0px;"><a class="glossary" href="javascript:void(0)" onclick="showDefintion(\''.$id.'\')">'.$name.'</a></span></td></tr>';
		}	
	}
	else
	{
		echo '<tr><td><p style="font-size:14px;font-weight:normal;font-family:verdana;color:#000;padding-left:0px;">'.get_lang('ThereAreNoDefinitionsHere').'</p></td></tr>';
	}
	
	echo '</table></div>';
	echo '<div><table align="center" width="80%" style="vertical-align:bottom;"><tr><td align="left">';
	if($from == 0)
	{
//	echo '<img src="../img/media_playback_next.png">';
	}
	else
	{
	echo '<a href="javascript:void(0)" onclick="showGlossary(\''.$current_glossary.'\',\''.$previous.'\')"><img src="../img/media_playback_next.png"></a>';
	}
	echo '</td><td align="right">';
	if($numrows > $number_of_items)
	{		
	echo '<a href="javascript:void(0)" onclick="showGlossary(\''.$current_glossary.'\',\''.$next.'\')"><img src="../img/media_playback_start.png"></a>';
	}
	else
	{
//	echo '<img src="../img/media_playback_start.png">';
	}
	echo '</div>';
}
// AJAX to show the term and definition of a particular glossary.
elseif(isset($_GET['action']) && $_GET['action'] == 'showterm')
{	
	$id = $_GET['q'];
	$t_glossary = Database :: get_course_table(TABLE_GLOSSARY);
	$t_item_propery = Database :: get_course_table(TABLE_ITEM_PROPERTY);
	$query = "SELECT 	g.glossary_id 		AS glossary_id,
					g.name 				AS glossary_title,
					g.description 		AS glossary_comment,
					g.display_order		AS glossary_display_order
			   FROM $t_glossary g, $t_item_propery ip
			   WHERE g.glossary_id = ip.ref
			   AND tool = '".TOOL_GLOSSARY."'
			   AND g.glossary_id = '".Database::escape_string($id)."' ";

	$res = api_sql_query($query, __FILE__, __LINE__);
	while ($data = Database::fetch_object($res))
	{
		$glossary_name = convert_encoding($data->glossary_title);
		$glossary_description = convert_encoding($data->glossary_comment);
	}

	echo '<table width="100%" border="0"><tr><td width="70%" align="center" valign="top"><div style="border: 1px solid #ccc;text-align:center;background-color:#FFF;padding:10px;font-size:18px;font-weight:bold;color:#A7A4AD;">'.$glossary_name.'</div><br/><div style="border: 1px solid #ccc;text-align:left;background-color:#FFF;width:90%;height:290px;overflow:auto;padding:5px 20px 20px 20px;font-size:14px;font-weight:normal;color:#000;">'.$glossary_description.'</div><br/>';
	if (api_is_allowed_to_edit(null,true))
	{	
	echo '<div style="padding-left:86%;"><a href="index.php?'.api_get_cidReq().'&action=editterm&glossary_id='.$id.'"><img src="../img/edit_link.png">&nbsp;&nbsp;<a href="index.php?action=delete_glossary&glossary_id='.$id.'"><img src="../img/delete.png"></a></div>';
	}	
	echo '</td><td width="30%"><table width="100%"><tr><td align="center"></td></tr></table></td></tr></table>';
	echo '<a href="index.php?'.api_get_cidReq().'"><img class="abs" src="../img/imagemap90.png" style="margin:30px 30px 0 0; right:0; top:0;"></a>';
}
?>
