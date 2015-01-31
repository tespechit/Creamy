<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["folder"])) {
	$validated = 0;
}
if (!isset($_POST["messageid"])) {
	$validated = 0;
}

$db = new DbHandler();
if ($validated == 1) {

	// message parameters	
	$userid = $_SESSION["userid"];
	$messageid = $_POST["messageid"];
	$folder = $_POST["folder"];

	// send message and analyze results
	$result = $db->getMessageModalDialogAsHTML($userid, $messageid, $folder);
	print($result);
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
