<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["taskDescription"])) {
	$validated = 0;
}
if (!isset($_POST["userid"]) && (!isset($_POST["touserid"]))) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = (isset($_POST["touserid"])) ? $_POST["touserid"] : $_POST["userid"];
	$taskDescription = $_POST["taskDescription"];
	$taskDescription = stripslashes($taskDescription);
	$taskDescription = $db->escape_string($taskDescription);
	$taskInitialProgress = 0;

	$result = $db->createTask($userid, $taskDescription, $taskInitialProgress);
	if ($result === true) { print "success"; }
	else { print "Ha sido imposible crear la tarea. Por favor, inténtelo más tarde. ($result)"; } 
	
} else { print "Por favor, introduzca todos los campos obligatorios (progreso y descripción)"; }

?>
