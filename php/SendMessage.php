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
	
	require_once('LanguageHandler.php');
	require_once('DbHandler.php');
	
	$lh = \creamy\LanguageHandler::getInstance();
	
	// check required fields
	$validated = 1;
	if (!isset($_POST["touserid"])) {
		$validated = 0;
	}
	if (!isset($_POST["fromuserid"])) {
		$validated = 0;
	}

	if ($validated == 1) {
		$db = new \creamy\DbHandler();
	
		// message parameters	
		$touserid = $_POST["touserid"];
		$fromuserid = $_POST["fromuserid"];
		if (isset($_POST["subject"])) $subject = $_POST["subject"]; else $subject = NULL;
		if (isset($_POST["message"])) $message = $_POST["message"]; else $message = NULL;
	
		// send message and analyze results
		$result = $db->sendMessage($fromuserid, $touserid, $subject, $message);
		if ($result === false) {
			$lh->translateText("unable_send_message");
		} else print "success";
		
		return;
	} else { $lh->translateText("some_fields_missing"); }
	
?>
