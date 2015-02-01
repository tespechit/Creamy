<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["touserid"])) {
	$validated = 0;
}
if (!isset($_POST["fromuserid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// message parameters	
	$touserid = $_POST["touserid"];
	$fromuserid = $_POST["fromuserid"];
	if (isset($_POST["subject"])) $subject = $_POST["subject"]; else $subject = NULL;
	if (isset($_POST["message"])) $message = $_POST["message"]; else $message = NULL;

	// send message and analyze results
	$result = $db->sendMessage($fromuserid, $touserid, $subject, $message);
	if ($result === false) {
		$lh->translateText("unable_send_message");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
