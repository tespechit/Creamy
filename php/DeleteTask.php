<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["taskid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// check password	
	$taskid = $_POST["taskid"];

	$result = $db->deleteTask($taskid);
	if ($result === false) {
		$lh->translateText("unable_delete_task");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
