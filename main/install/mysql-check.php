<?php

/**
 * @todo get_error_message wordpress/wp-db.php 
 */

require '../inc/lib/main_api.lib.php';
require '../lang/english/trad4all.inc.php';
require '../lang/english/install.inc.php';

$link = @mysql_connect($_POST['database_host'], $_POST['database_user'], $_POST['database_pass'], true);

if(!$link)
{
	echo '
			<div style="float:left;" class="quiz_content_actions">
				<!--<div  style="float:left; margin-right:10px;">
				<img src="../img/message_error.png" alt="Error" />
				</div>-->
				<div style="float:left;">
				<strong>MySQL error: '.mysql_errno().'</strong><br />
				'.mysql_error().'<br/>
				<strong>'.get_lang('Details').': '. get_lang('FailedConectionDatabase').'</strong><br />
				'.get_lang('IfStillTypingPleaseContinue').'
				</div>
			</div>';
	exit;
}

if(!@mysql_query('CREATE DATABASE '.addslashes($_POST['database_prefix']).'dokeos_database_connection_test', $link))
{
	echo '
			<div style="float:left;" class="quiz_content_actions">
				<!--<div  style="float:left; margin-right:10px;">
				<img src="../img/message_error.png" alt="Error" />
				</div>-->
				<div style="float:left;">
				<strong>MySQL error: '.mysql_errno().'</strong><br />
				'.mysql_error().'<br/>
				<strong>'.get_lang('Details').': '. get_lang('FailedConectionDatabase').'</strong><br />
				</div>
			</div>';
	exit;
}

@mysql_query('DROP DATABASE '.addslashes($_POST['database_prefix']).'dokeos_database_connection_test', $link);
echo '
		<div class="confirmation-message">
			<!--<div  style="float:left; margin-right:10px;">
				<img src="../img/message_confirmation.png" alt="Confirmation" />
			</div>-->
			<!--<div style="float:left;">-->
			<strong>'.get_lang('MysqlConnectionOk').'</strong><br />
			MySQL host info: '.mysql_get_host_info().'<br />
			MySQL server version: '.mysql_get_server_info().'<br />
			MySQL protocol version: '.mysql_get_proto_info().'
			<!--</div>-->
			<div style="clear:both;"></div>
		</div>
';
?>
