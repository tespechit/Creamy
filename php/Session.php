<?php
	require_once('CRMDefaults.php');
	
	session_start();// Starting Session
	if (!isset($_SESSION["username"])) {
		header('Location: login.php'); // Redirecting To Login Page
	}
	if (!isset($_SESSION["userid"])) {
		header('Location: login.php'); // Redirecting To Login Page
	}
	if (!isset($_SESSION["userrole"])) {
		$_SESSION["userrole"] = CRM_DEFAULTS_USER_ROLE_GUEST; // no privileged account by default.
	}
	if (!isset($_SESSION["avatar"])) {
		$_SESSION["avatar"] = "./img/avatars/default/defaultAvatar.png";
	}
?>