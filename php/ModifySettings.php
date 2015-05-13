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
require('Session.php');

$lh = \creamy\LanguageHandler::getInstance();
$user = \creamy\CreamyUser::currentUser();

// check required fields
$validated = 1;
if (!isset($_POST["timezone"])) {
	$validated = 0;
}
if (!isset($_POST["locale"])) {
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// check permissions
	if (!$user->userHasAdminPermission()) {
		$this->lh->translateText("you_dont_have_permission");
		return;
	}
	
	// check password	
	$timezone = $_POST["timezone"];
	$locale = $_POST["locale"];
	$enableModules = isset($_POST["enableModules"]) ? true : false;
	$enableStatistics = isset($_POST["enableStatistics"]) ? true : false;
	$baseURL = $_POST["base_url"];
	
	$data = array(
		CRM_SETTING_MODULE_SYSTEM_ENABLED => $enableModules, 
		CRM_SETTING_STATISTICS_SYSTEM_ENABLED => $enableStatistics, 
		CRM_SETTING_TIMEZONE => $timezone, 
		CRM_SETTING_LOCALE => $locale,
	);
	if (!empty($baseURL)) { $data[CRM_SETTING_CRM_BASE_URL] = $baseURL; }
	
	$result = $db->setSettings($data);
	if ($result === true) {
		print "success";
	} else { $lh->translateText("error_accessing_database"); };	
} else { $lh->translateText("some_fields_missing"); }

?>