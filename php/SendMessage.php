<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["touserid"])) {
	$validated = 0;
}
if (!isset($_POST["fromuserid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// message parameters	
	$touserid = $_POST["touserid"];
	$fromuserid = $_POST["fromuserid"];
	if (isset($_POST["subject"])) $subject = $_POST["subject"]; else $subject = NULL;
	if (isset($_POST["message"])) $message = $_POST["message"]; else $message = NULL;

	// send message and analyze results
	$result = $db->sendMessage($fromuserid, $touserid, $subject, $message);
	if ($result === false) {
		print "¡Vaya! Ha sido imposible enviar el mensaje. Por favor, inténtelo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible enviar mensaje. No se ha especificado el usuario de destino o de origen."; }

?>
