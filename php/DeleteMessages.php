<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["messageids"])) {
	$validated = 0;
}
if (!isset($_POST["folder"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_SESSION["userid"];
	$messageids = $_POST["messageids"];
	$folder = $_POST["folder"];

	$result = $db->deleteMessages($userid, $messageids, $folder);
	if ($result === false) {
		$lh->translateText("unable_delete_messages");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
