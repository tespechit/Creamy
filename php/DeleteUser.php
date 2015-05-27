<?php
/**
	The MIT License (MIT)
	
	Copyright (c) 2015 Ignacio Nieto Carvajal
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

require_once('CRMDefaults.php');
require_once('LanguageHandler.php');
require_once('DbHandler.php');

$lh = \creamy\LanguageHandler::getInstance();

// check required fields
$validated = 1;
if (!isset($_POST["userid"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// sanity checks	
	$userid = $_POST["userid"];
	$currentMainAdminData = $db->getMainAdminUserData(); // check that we are not deleting the main admin user.
	if (is_array($currentMainAdminData) && (array_key_exists("id", $currentMainAdminData))) {
		if ($userid == $currentMainAdminData["id"]) {
			// can't delete the main admin user.
			print $lh->translateText("unable_delete_main_admin");
			return;
		}
	}

	// delete user
	$result = $db->deleteUser($userid);
	if ($result === false) {
		ob_clean(); 
		$lh->translateText("unable_delete_user");
	} else { ob_clean(); print CRM_DEFAULT_SUCCESS_RESPONSE; }
} else { ob_clean(); $lh->translateText("some_fields_missing"); }
?>