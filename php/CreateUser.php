<?php
require_once('CRMDefaults.php');
require_once('DbHandler.php');
require_once('ImageHandler.php');

// check required fields
$validated = 1;
$reason = "Por favor, introduzca todos los campos obligatorios (nombre y contraseña)";
if (!isset($_POST["name"])) {
	$validated = 0;
}
if (!isset($_POST["password1"])) {
	$validated = 0;
}
if (!isset($_POST["password2"])) {
	$validated = 0;
}

$avatarOrigin = NULL;
$imageFileType = NULL;
if ((!empty($_FILES["avatar"])) && (!empty($_FILES["avatar"]["name"]))) {
	// check if the image is actually an image.
	$check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if($check !== false) { // check file size.
		if ($_FILES["avatar"]["size"] > 2097152) { // max file size 2Mb.
			$reason = "El tamaño del fichero de imagen es demasiado grande. El máximo son 2Mb.";
			$validated = 0;
		} else { // check file type
			$imageFileType = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
			    $reason = "Lo siento, solo se aceptan imágenes jpg, png o gif para la imagen de avatar.";
			    $validated = 0;
			} else {
				$avatarOrigin = $_FILES["avatar"]["tmp_name"];
			}
		}
    } else {
        $reason = "El fichero para la imagen de avatar suministrado no es una imagen";
        $validated = 0;
    }
	
}

if ($validated == 1) {
	$db = new DbHandler();

	// check password	
	$name = $_POST["name"];
	$name = stripslashes($name);
	$name = $db->escape_string($name);
	
	$password1 = $_POST["password1"];
	$password2 = $_POST["password2"];
	if ($password1 !== $password2) {
		print "Las contraseñas no coinciden, inténtelo de nuevo";
		exit;
	}
	
	$email = NULL; if (isset($_POST["email"])) { 
		$email = $_POST["email"]; 
		$email = stripslashes($email);
		$email = $db->escape_string($email);
	}
	$phone = NULL; if (isset($_POST["phone"])) { 
		$phone = $_POST["phone"];
		$phone = stripslashes($phone);
		$phone = $db->escape_string($phone); 
	}
	$avatar = NULL;
	if (!empty($avatarOrigin)) {
		$imageHandler = new ImageHandler();
		$avatar = $imageHandler->generateProcessedImageFileFromSourceImage($avatarOrigin, $imageFileType);
		if (empty($avatar)) {
			print "Hubo un error creando el usuario: ha sido imposible generar la imagen de avatar del usuario. Por favor, inténtelo más tarde.";
			return;
		}
	}
	
	$role = CRM_DEFAULTS_USER_ROLE_MANAGER; if (isset($_POST["isAdmin"])) { $role = CRM_DEFAULTS_USER_ROLE_ADMIN; } 	
	$result = $db->createUser($name, $password1, $email, $phone, $role, $avatar);
	if ($result === USER_CREATED_SUCCESSFULLY) { print "success"; }
	else if ($result === USER_ALREADY_EXISTED) { print "El usuario ya existe. Por favor, elija otro nombre de usuario."; } 
	else if ($result === USER_CREATE_FAILED) { print "Ha sido imposible crear el usuario. Por favor, inténtelo más tarde."; } 
	
} else {
	print $reason;
}

?>
