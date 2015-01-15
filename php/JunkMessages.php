<?php

require_once('DbHandler.php');
require('Session.php');

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

	$result = $db->junkMessages($userid, $messageids, $folder);
	print $result;
	return;
} else { print "0"; }

?>
