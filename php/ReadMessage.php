<?php
require_once('LanguageHandler.php');
require_once('UIHandler.php');
require('Session.php');

$lh = \creamy\LanguageHandler::getInstance();
$ui = \creamy\UIHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["folder"])) {
	$validated = 0;
}
if (!isset($_POST["messageid"])) {
	$validated = 0;
}

if ($validated == 1) {

	// message parameters	
	$userid = $_SESSION["userid"];
	$messageid = $_POST["messageid"];
	$folder = $_POST["folder"];

	// send message and analyze results
	$result = $ui->getMessageModalDialogAsHTML($userid, $messageid, $folder);
	print($result);
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
