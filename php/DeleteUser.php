<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["userid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["userid"];

	$result = $db->deleteUser($userid);
	if ($result === false) {
		$lh->translateText("unable_delete_user");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
