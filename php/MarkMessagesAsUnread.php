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

	$result = $db->markMessagesAsUnread($userid, $messageids, $folder);
	if ($result === false) {
		print "Ha sido imposible marcar los mensajes como no leídos. Por favor, inténtelo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible marcar los mensajes como no leídos. No se han especificado los mensajes."; }

?>
