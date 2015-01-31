<?php
	require_once('./php/LanguageHandler.php');
	require_once('./php/DbHandler.php');
	
	$error=''; // Variable To Store Error Message
	$lh = LanguageHandler::getInstance();
	
	if (isset($_POST['submit'])) {
		if (empty($_POST['email'])) {
			$error = $lh->translationFor("insert_valid_address");
		} else {
			$db = new DbHandler();

			// Define $username and $password
			$username=$_POST['email'];
			
			// To protect MySQL injection for Security purpose
			$email = stripslashes($_POST["email"]);
			
			// Check password and redirect accordingly
			$result = $db->sendPasswordRecoveryEmail($email);
			if ($result === false) { // login failed
				$error = $lh->translationFor("error_sending_recovery_email");
			} else {
				$error = $lh->translationFor("recovery_email_sent")." $email. ".$lh->translateText("please_check_email");
			}
		}
	}
?>
<html class="lockscreen">
    <head>
        <meta charset="UTF-8">
        <title><?php $lh->translateText("reset_password"); ?></title>
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
            <div class="header"><?php $lh->translateText("reset_password"); ?></div>
            <form method="post">
                <div class="body bg-gray">
				<?php $lh->translateText("have_you_lost_your_password"); ?>
                    <div class="form-group">
                        <input type="text" name="email" id="email" class="form-control" placeholder="<?php $lh->translateText("email"); ?>"/>
                    </div>
                	<div name="error-message" class="text-center" style="color: red;">
                	<?php
                		if (isset($error)) {
	                		print ($error);
                		}
                	?>
                	</div>
                </div>
                <div class="footer text-center">                                                               
                    <button type="submit" name="submit" id="sumbit" class="btn bg-light-blue btn-block"><?php $lh->translateText("send"); ?></button>  
                </div>
            </form>
            <div class="margin text-center">
                <span><?php $lh->translateText("never_heard_of_creamy"); ?></span>
                <br/>
                <button class="btn bg-red btn-circle" onclick="window.location.href='http://creamycrm.com'"><i class="fa fa-globe"></i></button>
                <button class="btn bg-light-blue btn-circle" onclick="window.location.href='https://www.facebook.com/creamycrm'"><i class="fa fa-facebook"></i></button>
                <button class="btn bg-aqua btn-circle" onclick="window.location.href='https://twitter.com/creamycrm'"><i class="fa fa-twitter"></i></button>
            </div>
    	</div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    </body>
</html>