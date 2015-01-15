<?php

require_once('DbHandler.php');

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
		print "¡Vaya! Ha sido imposible borrar al usuario. Por favor, inténtelo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible borrar usuario. No se ha especificado el usuario a eliminar."; }

?>
