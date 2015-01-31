<?php

require_once('DbHandler.php');
require_once('LanguageHandler.php');
$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["userid"])) {
	$validated = 0;
}
if (!isset($_POST["old_password"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_1"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_2"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["userid"];
	$oldpassword = $_POST["old_password"];
	$password1 = $_POST["new_password_1"];
	$password2 = $_POST["new_password_2"];
	if ($password1 !== $password2) {
		$lh->translateText("passwords_dont_match");
		exit;
	}
	
	$result = $db->changePassword($userid, $oldpassword, $password1, $password2);
	if ($result === true) { print "success"; }
	else { $lh->translateText("unable_change_password"); } 
	
} else { $lh->translateText("some_fields_missing"); }

?>
