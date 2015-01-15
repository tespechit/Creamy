<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["taskid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$taskid = $_POST["taskid"];

	$result = $db->deleteTask($taskid);
	if ($result === false) {
		print "¡Vaya! Ha sido imposible borrar la tarea. Por favor, inténtalo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible borrar tarea. No se ha especificado la tarea a eliminar."; }

?>
