<?php

require_once('DbHandler.php');
require('Session.php');

// check admin privileges.
$privileges = 0;
if (isset($_SESSION["userrole"])) {
	if ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN) {
		$privileges = 1;
	}
}
if ($privileges == 0) {
	print "No dispone de los permisos suficientes para cambiar las contraseñas de un usuario.";
	exit;
}


// check required fields
$validated = 1;
if (!isset($_POST["usertochangepasswordid"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_1"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_2"])) {
	$validated = 0;
}

print "Parámetros: \n";
print_r ($_POST);

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["usertochangepasswordid"];
	$password1 = $_POST["new_password_1"];
	$password2 = $_POST["new_password_2"];
	if ($password1 !== $password2) {
		print "Las nuevas contraseñas no coinciden, inténtelo de nuevo";
		exit;
	}
	
	$result = $db->changePasswordAdmin($userid, $password1);
	if ($result === true) { print "success"; }
	else { print "Ha sido imposible cambiar la contraseña. Por favor, inténtelo de nuevo."; } 
	
} else { print "Por favor, introduzca todos los campos obligatorios (nuevas contraseñas e id del usuario a cambiar la contraseña)"; }

?>
