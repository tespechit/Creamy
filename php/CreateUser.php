<?php
require_once('CRMDefaults.php');
require_once('DbHandler.php');
require_once('ImageHandler.php');
require_once('LanguageHandler.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
$reason = $lh->translationFor("some_fields_missing");
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
			$reason = $lh->translationFor("image_file_too_large");
			$validated = 0;
		} else { // check file type
			$imageFileType = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
			    $reason = $lh->translationFor("image_file_wrong_type");
			    $validated = 0;
			} else {
				$avatarOrigin = $_FILES["avatar"]["tmp_name"];
			}
		}
    } else {
        $reason = $lh->translationFor("image_file_is_not_image");
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
		$lh->translateText("passwords_dont_match");
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
			$lh->translateText("unable_generate_user_image");
			exit;
		}
	}
	
	$role = CRM_DEFAULTS_USER_ROLE_GUEST; if (isset($_POST["role"])) { $role = $_POST["role"]; } 	
	$result = $db->createUser($name, $password1, $email, $phone, $role, $avatar);
	if ($result === USER_CREATED_SUCCESSFULLY) { print "success"; }
	else if ($result === USER_ALREADY_EXISTED) { $lh->translateText("user_already_exists"); } 
	else if ($result === USER_CREATE_FAILED) { $lh->translateText("unable_create_user"); } 
	exit;
} else {
	print $reason;
}

?>
