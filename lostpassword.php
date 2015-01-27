<?php

	require_once('./php/DbHandler.php');
	
	$error=''; // Variable To Store Error Message
	if (isset($_POST['submit'])) {
		if (empty($_POST['email'])) {
			$error = "Por favor, introduce una dirección válida.";
		} else {
			$db = new DbHandler();

			// Define $username and $password
			$username=$_POST['email'];
			
			// To protect MySQL injection for Security purpose
			$email = stripslashes($_POST["email"]);
			
			// Check password and redirect accordingly
			$result = $db->sendPasswordRecoveryEmail($email);
			if ($result === false) { // login failed
				$error = "Ha habido un error enviando el email de cambio de contraseña. Es posible que la dirección email introducida no pertenezca a un usuario. Inténtalo de nuevo.";
			} else {
				$error = "Hemos enviado un email de recuperación de contraseña a $email. Por favor, comprueba que ha llegado y pulsa en el enlace.";
			}
		}
	}
?>
<html class="lockscreen">
    <head>
        <meta charset="UTF-8">
        <title>Regenerar contraseña</title>
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
            <div class="header">Recuperar contraseña</div>
            <form method="post">
                <div class="body bg-gray">
				¿Has perdido tu contraseña? Introduce el email de tu usuario y te enviaremos un enlace para que generes una nueva.
                    <div class="form-group">
                        <input type="text" name="email" id="email" class="form-control" placeholder="Dirección de email"/>
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
                    <button type="submit" name="submit" id="sumbit" class="btn bg-light-blue btn-block">Enviar</button>  
                </div>
            </form>
            <div class="margin text-center">
                <span>¿No has oído hablar de Creamy? Aprende un poco más aquí:</span>
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