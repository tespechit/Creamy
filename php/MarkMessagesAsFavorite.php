<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["messageids"])) {
	$validated = 0;
}
if (!isset($_POST["folder"])) {
	$validated = 0;
}
if (!isset($_POST["favorite"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// check password	
	$userid = $_SESSION["userid"];
	$messageids = $_POST["messageids"];
	$folder = $_POST["folder"];
	$favorite = $_POST["favorite"];

	$result = $db->markMessagesAsFavorite($userid, $messageids, $folder, $favorite);
	if ($result === false) {
		$lh->translateText("unable_set_favorites");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
