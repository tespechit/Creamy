<?php
require_once('LanguageHandler.php');
require_once('CRMDefaults.php');
require_once('DbHandler.php');
require('Session.php');

$db = new DbHandler();
$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["taskid"])) {
	$validated = 0;
}
if (!isset($_SESSION["userid"])) {
	$validated = 0;
}

if ($validated == 1) {

	// check password	
	$taskid = $_POST["taskid"];
	$userid = $_SESSION["userid"];
	$format = TASK_GENERAL_INFO_FORMAT;
	if (isset($_POST["format"])) { $format = $_POST["format"]; }
	
	$result = $db->getTaskInfoAsTable($taskid, $userid, $format);
	print $result;
	
} else { print $db->getErrorMessage($lh->translationFor("error_accessing_task_data")); }

?>
