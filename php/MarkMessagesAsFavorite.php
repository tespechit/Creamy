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
if (!isset($_POST["favorite"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_SESSION["userid"];
	$messageids = $_POST["messageids"];
	$folder = $_POST["folder"];
	$favorite = $_POST["favorite"];

	$result = $db->markMessagesAsFavorite($userid, $messageids, $folder, $favorite);
	if ($result === false) {
		print "Ha sido imposible establecer favoritos. Por favor, inténtelo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible establecer favoritos. No se han especificado los mensajes."; }

?>
