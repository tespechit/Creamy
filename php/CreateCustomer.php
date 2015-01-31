<?php

require_once('DbHandler.php');
require_once('LanguageHandler.php');
require('Session.php');

$lh = LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["name"])) {
	$validated = 0;
}
if (!isset($_POST["customer_type"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new DbHandler();

	// get name (mandatory)
	$name = $_POST["name"];
	$name = stripslashes($name);
	$name = $db->escape_string($name);
	$customerType = $_POST["customer_type"];
	$customerType = stripslashes($customerType);
	$customerType = $db->escape_string($customerType);
	$createdByUser = $_SESSION["userid"];
		
	// get optional values
	
	// email
	$email = NULL; if (isset($_POST["email"])) { 
		$email = $_POST["email"]; 
		$email = stripslashes($email);
		$email = $db->escape_string($email);
	}
	// phone
	$phone = NULL; if (isset($_POST["phone"])) { 
		$phone = $_POST["phone"];
		$phone = stripslashes($phone);
		$phone = $db->escape_string($phone); 
	}
	// mobile phone
	$mobile = NULL; if (isset($_POST["mobile"])) { 
		$mobile = $_POST["mobile"];
		$mobile = stripslashes($mobile);
		$mobile = $db->escape_string($mobile); 
	}
	// ID number (passport, DNI, NIF, identification number, VAT number, etc...).
	$id_number = NULL; if (isset($_POST["id_number"])) { 
		$id_number = $_POST["id_number"]; 
		$id_number = stripslashes($id_number);
		$id_number = $db->escape_string($id_number);
	} 
	// address
	$address = NULL; if (isset($_POST["address"])) { 
		$address = $_POST["address"]; 
		$address = stripslashes($address);
		$address = $db->escape_string($address);
	}
	
	// city
	$city = NULL; if (isset($_POST["city"])) { 
		$city = $_POST["city"]; 
		$city = stripslashes($city);
		$city = $db->escape_string($city);
	}
	
	// state
	$state = NULL; if (isset($_POST["state"])) { 
		$state = $_POST["state"]; 
		$state = stripslashes($state);
		$state = $db->escape_string($state);
	}
	
	// ZIP code
	$zipcode = NULL; if (isset($_POST["zipcode"])) { 
		$zipcode = $_POST["zipcode"]; 
		$zipcode = stripslashes($zipcode);
		$zipcode = $db->escape_string($zipcode);
	}
	
	// country
	$country = NULL; if (isset($_POST["country"])) { 
		$country = $_POST["country"]; 
		$country = stripslashes($country);
		$country = $db->escape_string($country);
	}
	
	// fecha de nacimiento
	$birthdate = NULL; if (isset($_POST["birthdate"])) { 
		$birthdate = $_POST["birthdate"]; 
		$birthdate = stripslashes($birthdate);
		$birthdate = $db->escape_string($birthdate);
	}

	// marital status
	$maritalstatus = 0; if (isset($_POST["maritalstatus"])) { 
		$maritalstatus = $_POST["maritalstatus"]; 
		$maritalstatus = stripslashes($maritalstatus);
		$maritalstatus = $db->escape_string($maritalstatus);
	}
	if ($maritalstatus < 1 || $maritalstatus > 5) $maritalstatus = 0;
		
	// gender
	$gender = NULL; if (isset($_POST["gender"])) { 
		$gender = $_POST["gender"]; 
		$gender = stripslashes($gender);
		$gender = $db->escape_string($gender);
	}
	if ($gender < 0 || $gender > 1) $gender = NULL;
	
	// product type
	$productType = NULL; if (isset($_POST["productType"])) { 
		$productType = $_POST["productType"]; 
		$productType = stripslashes($productType);
		$productType = $db->escape_string($productType);
	}
	
	// do not send email
	$donotsendemail = 0; if (isset($_POST["donotsendemail"])) { 
		$donotsendemail = 1;
	}

	$result = $db->createCustomer($customerType, $name, $email, $phone, $mobile, $id_number, $address, $city, $state, $zipcode, $country, $birthdate, $maritalstatus, $productType, $donotsendemail, $createdByUser, $gender);
	if ($result === true) { print "success"; }
	else { $lh->translateText("unable_create_customer"); } 
	
} else { $lh->translateText("some_fields_missing"); }

?>
