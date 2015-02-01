<?php
require_once('LanguageHandler.php');
require_once('DbHandler.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["customerid"])) {
	$validated = 0;
}
if (!isset($_POST["customer_type"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// check password	
	$customerid = $_POST["customerid"];
	$customerType = $_POST["customer_type"];

	$result = $db->deleteCustomer($customerid, $customerType);
	if ($result === false) {
		$lh->translateText("unable_delete_customer");
	} else print "success";
	
	return;
} else { $lh->translateText("some_fields_missing"); }

?>
