<?php
	$result = NULL;
	$error = NULL;
	
	if (isset($_POST["submit"])) {
		require_once('./php/DbHandler.php');
		$email = NULL;
		$code = NULL;
		$nonce = NULL;
		$date = NULL;
		$password1 = NULL;
		$password2 = NULL;
		if (isset($_POST["email"])) $email = $_POST["email"];
		if (isset($_POST["code"])) $code = $_POST["code"];
		if (isset($_POST["nonce"])) $nonce = $_POST["nonce"];
		if (isset($_POST["date"])) $date = $_POST["date"];
		if (isset($_POST["password1"])) $password1 = $_POST["password1"];
		if (isset($_POST["password2"])) $password2 = $_POST["password2"];
		
		if ($password1 == $password2 && !empty($password1) && !empty($password2)) {
			$db = new DbHandler();
			if ($db->checkPasswordResetValidity($email, $date, $nonce, $code)) {
				if ($db->changePasswordForUserIdentifiedByEmail($email, $password1)) {
					$result = "Tu contraseña ha sido restablecida con éxito. Ahora puedes entrar en Creamy con tu nueva contraseña.";
				} else $result = "¡Vaya! Parece que hubo un error que impidió cambiar tu contraseña. Por favor, ponte en contacto con el administrador.";
			}
		} else {
			$error = "Las contraseñas no coinciden, o alguna de ellas está vacía. Por favor, inténtalo de nuevo.";
		}
	}
?>
<html class="lockscreen">
    <head>
        <meta charset="UTF-8">
        <title>Regenerar contraseña</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="./css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="form-box form-box-login" id="login-box">
			<div class="margin text-center">
				<img src="img/logo.png" width="64" height="64">
			</div>
            <div class="header">Restablecer contraseña</div>
   			<?php if ($result == NULL) { ?>

            <form method="post">
                <div class="body bg-gray">
				Introduce tu nueva contraseña y pulsa el botón de 'Restablecer'.
                    <div class="form-group">
						<input class="form-control" type="password" placeholder="Nueva contraseña" id="password1" name="password1"><br>
						<input class="form-control" type="password" placeholder="Nueva contraseña (de nuevo)" id="password2" name="password2"><br>
                    </div>
                    <input type="hidden" name="code" id="code" value="<?php echo $_GET['code']; ?>">
					<input type="hidden" name="date" id="date" value="<?php echo $_GET['date']; ?>">
					<input type="hidden" name="nonce" id="nonce" value="<?php echo $_GET['nonce']; ?>">
					<input type="hidden" name="email" id="email" value="<?php echo $_GET['email']; ?>">
                	<div name="error-message" class="text-center" style="color: red;">
                	<?php
                		if (isset($error)) {
	                		print ($error);
                		}
                	?>
                	</div>
                </div>
                <div class="footer text-center">                                                               
                    <button type="submit" name="submit" id="sumbit" class="btn bg-light-blue btn-block">Restablecer</button>  
                </div>
            </form>
			
			<?php } else { ?>
                <div class="body bg-gray">
					<p><?php echo $result; ?></p>
                	</div>
			<?php } ?>
			
            
            <div class="margin text-center">
                <span>¿No has oído hablar de Creamy? Aprende un poco más aquí:</span>
                <br/>
                <button class="btn bg-red btn-circle" onclick="window.location.href='http://creamycrm.com'"><i class="fa fa-globe"></i></button>
                <button class="btn bg-light-blue btn-circle" onclick="window.location.href='https://www.facebook.com/creamycrm'"><i class="fa fa-facebook"></i></button>
                <button class="btn bg-aqua btn-circle" onclick="window.location.href='https://twitter.com/creamycrm'"><i class="fa fa-twitter"></i></button>
            </div>
    	</div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
    </body>
</html>