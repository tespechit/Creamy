<?php

require_once('Config.php');
require_once('DbHandler.php');

date_default_timezone_set(CRM_TIMEZONE);
$date = date('d-m-Y');
$adminMail = 'nacho@woloweb.com';

$db = new DbHandler();
$result = $db->generateStatisticsForToday();
if ($result == false) {
	mail($adminMail, 'Error incluyendo estadisticas para tumejorseguromedico.com, '.$date, 'Hubo un error que impidio que se incluyeran las estadisticas para tumejorseguromedico.com en la fecha '.$date);
}
	
?>