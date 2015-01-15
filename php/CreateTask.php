<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["taskDescription"])) {
	$validated = 0;
}
if (!isset($_POST["taskInitialProgress"])) {
	$validated = 0;
}
if (!isset($_POST["userid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["userid"];
	$taskDescription = $_POST["taskDescription"];
	$taskDescription = stripslashes($taskDescription);
	$taskDescription = $db->escape_string($taskDescription);
	$taskInitialProgress = $_POST["taskInitialProgress"];

	$result = $db->createTask($userid, $taskDescription, $taskInitialProgress);
	if ($result === true) { print "success"; }
	else { print "Ha sido imposible crear la tarea. Por favor, inténtelo más tarde. ($result)"; } 
	
} else { print "Por favor, introduzca todos los campos obligatorios (progreso y descripción)"; }

?>
