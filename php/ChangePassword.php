<?php

require_once('DbHandler.php');

// check required fields
$validated = 1;
if (!isset($_POST["userid"])) {
	$validated = 0;
}
if (!isset($_POST["old_password"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_1"])) {
	$validated = 0;
}
if (!isset($_POST["new_password_2"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$userid = $_POST["userid"];
	$oldpassword = $_POST["old_password"];
	$password1 = $_POST["new_password_1"];
	$password2 = $_POST["new_password_2"];
	if ($password1 !== $password2) {
		print "Las nuevas contraseñas no coinciden, inténtelo de nuevo";
		exit;
	}
	
	$result = $db->changePassword($userid, $oldpassword, $password1, $password2);
	if ($result === true) { print "success"; }
	else { print "Ha sido imposible cambiar la contraseña. Por favor, compruebe que la contraseña antigua es válida. ¿Ha olvidado su contraseña? Contacte con el administrador."; } 
	
} else { print "Por favor, introduzca todos los campos obligatorios (antiguas y nuevas contraseñas)"; }

?>
