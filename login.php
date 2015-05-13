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

	require_once('./php/CRMDefaults.php');
	// check installation before login.
	if (!file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
		header("location: ./install.php");
	}

	// load DB handler and Language Handler.	
	require_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');

	session_start(); // Starting Session
	$lh = \creamy\LanguageHandler::getInstance();
	
	$error = ''; // Variable To Store Error Message
	if (isset($_POST['submit'])) {
		if (empty($_POST['username']) || empty($_POST['password'])) {
			$error = $lh->translationFor("insert_valid_login_password");
		} else {
			$db = new \creamy\DbHandler();

			// Define $username and $password
			$username=$_POST['username'];
			$password=$_POST['password'];
			
			// To protect MySQL injection for Security purpose
			$username = stripslashes($username);
			$password = stripslashes($password);
			$username = $db->escape_string($username);
			$password = $db->escape_string($password);
			
			// Check password and redirect accordingly
			$result = null;
			if(filter_var($username, FILTER_VALIDATE_EMAIL)) {
		        // valid email address
				$result = $db->checkLoginByEmail($username, $password);
		    }
		    else {
		        // not an email. User name?
				$result = $db->checkLoginByName($username, $password);
		    }
			if ($result == NULL) { // login failed
				$error = $lh->translationFor("invalid_login_password");
			} else {
				$_SESSION["userid"] = $result["id"]; 
				$_SESSION["username"] = $result["name"]; 
				$_SESSION["userrole"] = $result["role"]; 
				if (!empty($result["avatar"])) {
					$_SESSION['avatar'] = $result["avatar"];
				} else { // random avatar.
					$_SESSION["avatar"] = CRM_DEFAULTS_USER_AVATAR;
				}
				header("location: index.php"); // Redirecting To Main Page
			}
		}
	}
?>
<html class="lockscreen">
    <head>
        <meta charset="UTF-8">
        <title><?php $lh->translateText("system_access"); ?> </title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="./css/creamycrm.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    </head>
  <body class="login-page">
    <div class="login-box" id="login-box">
	  <div class="margin text-center">
		<img src="img/logo.png" width="64" height="64">
	  </div>
      <div class="login-logo">
        <?php $lh->translateText("welcome_to_creamy"); ?>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg"><?php $lh->translateText("sign_in"); ?></p>
        <form action="" method="post">
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="username" placeholder="<?php $lh->translateText("username_or_email"); ?>"/>
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" name="password" class="form-control" placeholder="<?php $lh->translateText("password"); ?>"/>
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
	    	<div name="error-message" style="color: red;">
	    	<?php
	    		if (isset($error)) { print ("<p>".$error."</p>"); }
	    	?>
	    	</div>
          <div class="row">
            <div class="col-xs-4"></div>
            <div class="col-xs-4">
              <button type="submit" name="submit" class="btn btn-primary btn-block btn-flat"><?php $lh->translateText("access"); ?></button>
            </div><!-- /.col -->
            <div class="col-xs-4"></div>
          </div>
        </form>
		<p class="text-center"><?php $lh->translateText("forgotten_password"); ?> <a href="lostpassword.php"><?php $lh->translateText("click_here"); ?>.</a></p>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
    <div class="margin text-center">
        <span><?php $lh->translateText("never_heard_of_creamy"); ?></span>
        <br/>
        <button class="btn bg-red btn-flat" onclick="window.location.href='http://creamycrm.com'"><i class="fa fa-globe"></i></button>
        <button class="btn bg-light-blue btn-flat" onclick="window.location.href='https://github.com/DigitalLeaves/Creamy'"><i class="fa fa-github"></i></button>
        <button class="btn bg-aqua btn-flat" onclick="window.location.href='https://twitter.com/creamythecrm'"><i class="fa fa-twitter"></i></button>
    </div>
	<?php unset($error); ?>
  </body>
</html>