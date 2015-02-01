<?php
require_once('LanguageHandler.php');
require_once('Config.php');
require_once('DbHandler.php');
$lh = \creamy\LanguageHandler::getInstance();

date_default_timezone_set(CRM_TIMEZONE);
$date = date('d-m-Y');
$adminMail = 'nacho@woloweb.com';

$db = new \creamy\DbHandler();
$result = $db->generateStatisticsForToday();
if ($result == false) {
	mail($adminMail, $lh->translationFor("error_storing_statistics").$date, $lh->translationFor("error_storing_statistics").$date);
}
	
?>