<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["customerid"])) {
	$validated = 0;
}
if (!isset($_POST["customer_type"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$customerid = $_POST["customerid"];
	$customerType = $_POST["customer_type"];

	$result = $db->deleteCustomer($customerid, $customerType);
	if ($result === false) {
		print "¡Vaya! Ha sido imposible borrar al cliente. Por favor, inténtelo de nuevo más tarde.";
	} else print "success";
	
	return;
} else { print "Imposible borrar cliente. No se ha especificado el usuario a eliminar o el tipo de cliente."; }

?>
