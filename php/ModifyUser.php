<?php

require_once('DbHandler.php');
require('Session.php');

// check required fields
$reason = "Imposible modificar los datos de usuario. Póngase en contacto con el administrador.";
$validated = 1;
if (!isset($_POST["modifyid"])) {
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
	$modifyid = $_POST["modifyid"];
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
	$userrole = CRM_DEFAULTS_USER_ROLE_GUEST; if (isset($_POST["role"])) { $userrole = $_POST["role"]; } 	
		
	$result = $db->modifyUser($modifyid, $email, $phone, $userrole, $avatar);
	if ($result === true) {
		if ($modifyid == $_SESSION["userid"]) { // am I modifying myself?
			// if so, update avatar (if needed).
			if (!empty($avatar)) { $_SESSION["avatar"] = $avatar; }
		}
		print "success"; 
	} else print "Imposible modificar los datos de usuario. Póngase en contacto con el administrador.";	
} else { print $reason; }

?>
