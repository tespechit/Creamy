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
	require_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');

	session_start(); // Starting Session
	$lh = \creamy\LanguageHandler::getInstance();
	
	$error=''; // Variable To Store Error Message
	if (isset($_POST['submit'])) {
		if (empty($_POST['username']) || empty($_POST['password'])) {
			$_SESSION["errorMessage"] = $lh->translationFor("insert_valid_login_password");
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
			$result = $db->checkLogin($username, $password);
			if ($result == NULL) { // login failed
				$_SESSION["errorMessage"] = $lh->translationFor("invalid_login_password");
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
    </head>
    <body>
        <div class="form-box form-box-login" id="login-box">
			<div class="margin text-center">
				<img src="img/logo.png" width="64" height="64">
			</div>
            <div class="header"><?php $lh->translateText("welcome_to_creamy"); ?></div>
            <form action="" method="post">
                <div class="body bg-gray">
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="<?php $lh->translateText("name"); ?>"/>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="<?php $lh->translateText("password"); ?>"/>
                    </div>          
                	<div name="error-message" style="color: red;">
                	<?php
                		if (isset($_SESSION["errorMessage"])) {
	                		print ($_SESSION["errorMessage"]);
                		}
                	?>
                	</div>
                </div>
                <div class="footer text-center">                                                               
                    <button type="submit" name="submit" id="sumbit" class="btn bg-light-blue btn-block"><?php $lh->translateText("access"); ?></button>  
                    
                    <p><?php $lh->translateText("forgotten_password"); ?> <a href="lostpassword.php"><?php $lh->translateText("click_here"); ?>.</a></p>
                </div>
                <?php unset($_SESSION['errorMessage']); ?>
            </form>
            <div class="margin text-center">
                <span><?php $lh->translateText("never_heard_of_creamy"); ?></span>
                <br/>
                <button class="btn bg-red btn-circle" onclick="window.location.href='http://creamycrm.com'"><i class="fa fa-globe"></i></button>
                <button class="btn bg-light-blue btn-circle" onclick="window.location.href='https://www.facebook.com/creamycrm'"><i class="fa fa-facebook"></i></button>
                <button class="btn bg-aqua btn-circle" onclick="window.location.href='https://twitter.com/creamythecrm'"><i class="fa fa-twitter"></i></button>
            </div>
   			<!--
            <div class="margin text-center">
                <span>Sign in using social networks</span>
                <br/>
                <button class="btn bg-light-blue btn-circle"><i class="fa fa-facebook"></i></button>
                <button class="btn bg-aqua btn-circle"><i class="fa fa-twitter"></i></button>
                <button class="btn bg-red btn-circle"><i class="fa fa-google-plus"></i></button>

            </div>
            -->
    	</div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    </body>
</html>