<?php

require_once('DbHandler.php');
require_once('LanguageHandler.php');
require('Session.php');
$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["complete-task-taskid"])) {
	$validated = 0;
}
if (!isset($_POST["complete-task-progress"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$taskid = $_POST["complete-task-taskid"];
	$progress = $_POST["complete-task-progress"];
	$userid = $_SESSION["userid"];
	
	$result = $db->setTaskCompletionStatus($taskid, $progress, $userid);
	if ($result === true) {
		print "success";
	} else { $lh->translateText("unable_modify_task"); };	
} else { $lh->translateText("some_fields_missing"); }

?>
