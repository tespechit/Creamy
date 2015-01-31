<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["userid"])) {
	$validated = 0;
}
if (!isset($_POST["status"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["userid"];
	$status = $_POST["status"];

	$result = $db->setStatusOfUser($userid, $status);
	if ($result === true) {
		print "success"; 
	} else { $lh->translateText("unable_set_user_status"); };	
} else { $lh->translateText("some_fields_missing"); }

?>
