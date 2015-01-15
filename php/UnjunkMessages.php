<?php

require_once('DbHandler.php');
require('Session.php');

// check required fields
$validated = 1;
if (!isset($_POST["messageids"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_SESSION["userid"];
	$messageids = $_POST["messageids"];

	$result = $db->unjunkMessages($userid, $messageids);
	print $result;
	return;
} else { print "0"; }

?>
