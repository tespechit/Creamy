<?php

require_once('DbHandler.php');
require_once('LanguageHandler.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["taskDescription"])) {
	$validated = 0;
}
if (!isset($_POST["userid"]) && (!isset($_POST["touserid"]))) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// check password	
	$userid = (isset($_POST["touserid"])) ? $_POST["touserid"] : $_POST["userid"];
	$taskDescription = $_POST["taskDescription"];
	$taskDescription = stripslashes($taskDescription);
	$taskDescription = $db->escape_string($taskDescription);
	$taskInitialProgress = 0;

	$result = $db->createTask($userid, $taskDescription, $taskInitialProgress);
	if ($result === true) { print "success"; }
	else { print $lh->translationFor("unable_create_task")." ($result)"; } 
	
} else { $lh->translateText("some_fields_missing"); }

?>
