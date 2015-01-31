<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["edit-task-taskid"])) {
	$validated = 0;
}
if (!isset($_POST["edit-task-description"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$taskid = $_POST["edit-task-taskid"];
	$description = $_POST["edit-task-description"];
	$userid = $_SESSION["userid"];
	
	$result = $db->editTaskDescription($taskid, $description, $userid);
	if ($result === true) {
		print "success";
	} else { $lh->translateText("unable_modify_task"); };	
} else { $lh->translateText("some_fields_missing"); }

?>
