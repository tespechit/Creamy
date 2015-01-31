<?php

require_once('DbHandler.php');
require_once('LanguageHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check admin privileges.
$privileges = 0;
if (isset($_SESSION["userrole"])) {
	if ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN) {
		$privileges = 1;
	}
}
if ($privileges == 0) {
	$lh->translateText("not_permission_edit_user_information");
	exit;
}


// check required fields
$validated = 1;
if (!isset($_POST["usertochangepasswordid"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_1"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_2"])) {
	$validated = 0;
}

if ($validated == 1) {

	// check password	
	$userid = $_POST["usertochangepasswordid"];
	$password1 = $_POST["new_password_1"];
	$password2 = $_POST["new_password_2"];
	if ($password1 !== $password2) {
		$lh->translateText("passwords_dont_match");
		exit;
	}
	
	$db = new DbHandler();
	$result = $db->changePasswordAdmin($userid, $password1);
	if ($result === true) { print "success"; }
	else { $lh->translateText("error_changing_password"); } 
	
} else { $lh->translateText("some_fields_missing"); }

?>
