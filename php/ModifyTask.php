<?php

require_once('DbHandler.php');
require('Session.php');

// check required fields
$validated = 1;
if (!isset($_POST["complete-task-taskid"])) {
	$validated = 0;
}
if (!isset($_POST["task-new-progress-slider"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$taskid = $_POST["complete-task-taskid"];
	$progress = $_POST["task-new-progress-slider"];
	$userid = $_SESSION["userid"];
	
	$result = $db->modifyTask($taskid, $progress, $userid);
	if ($result === true) {
		print "success";
	} else print "Imposible modificar la información de la tarea. Póngase en contacto con el administrador.";	
} else { print "Imposible modificar la información de la tarea. Póngase en contacto con el administrador."; }

?>
