<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');
require('Session.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$reason = $lh->translationFor("unable_modify_user");
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
	$db = new \creamy\DbHandler();

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
		$imageHandler = new \creamy\ImageHandler();
		$avatar = $imageHandler->generateProcessedImageFileFromSourceImage($avatarOrigin, $imageFileType);
		if (empty($avatar)) {
			$lh->translateText("unable_generate_user_image");
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
	} else { $lh->translateText("unable_modify_user"); };	
} else { print $reason; }

?>
