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

require_once('DbHandler.php');
require_once('CRMDefaults.php');
require_once('LanguageHandler.php');
require_once('Session.php');

// utility functions
function get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

$lh = \creamy\LanguageHandler::getInstance();
$user = \creamy\CreamyUser::currentUser();

// check required fields
$validated = 1;
if (!isset($_POST["event_id"])) { // do we have a title?
	$validated = 0;
}

if ($validated == 1) {
	$db = new \creamy\DbHandler();

	// retrieve data for the event.
	$eventid = $_POST["event_id"];
	// calculate proper start and end date, including timezone offset
	$offset = get_timezone_offset($db->getTimezoneSetting(), "UTC");
	$startDate = null; $endDate = null; $allDay = null;
	if (isset($_POST["start_date"])) $startDate = intval($_POST["start_date"])/1000 + intval($offset);
	if (isset($_POST["end_date"])) $endDate = intval($_POST["end_date"]/1000) + intval($offset);
	if (isset($_POST["all_day"])) $allDay = filter_var($_POST["all_day"], FILTER_VALIDATE_BOOLEAN);
	
	// modify event
	$result = $db->modifyEvent($user->getUserId(), $eventid, $startDate, $endDate, $allDay);
	
	// return result
	if ($result === true) {
		ob_clean();
		print CRM_DEFAULT_SUCCESS_RESPONSE; 
	} else { ob_clean(); $lh->translateText("unable_modify_event"); }
} else { ob_clean(); $lh->translateText("some_fields_missing"); }
?>