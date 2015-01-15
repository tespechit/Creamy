<?php

	require_once('./php/DbInstaller.php');
	require_once('./php/RandomStringGenerator.php');
	
	session_start(); // Starting Session

	$error = ""; 					// Variable To Store Error Message	
	$currentState = "step1";	// current install status variable
	
	// set initial installation step (if found).
	if (isset($_SESSION["installationStep"])) { $currentState = $_SESSION["installationStep"]; }
	
	if (file_exists("./installed.txt")) { // check if already installed 
		$currentState = "already_installed"; 
	} elseif (isset($_POST["submit_step1"]) && $currentState == "step1") { // first step: get credentials for database access.
		$dbhost = "localhost";
		$dbname = NULL;
		$dbuser = NULL;
		$dbpass = NULL;
		$timezone = NULL;
		if (isset($_POST["dbhost"])) { $dbhost = $_POST["dbhost"]; }
		if (isset($_POST["dbname"])) { $dbname = $_POST["dbname"]; }
		if (isset($_POST["dbuser"])) { $dbuser = $_POST["dbuser"]; }
		if (isset($_POST["dbpass"])) { $dbpass = $_POST["dbpass"]; }
		if (isset($_POST["userTimeZone"])) { $timezone = $_POST["userTimeZone"]; } else { $timezone = "UTC"; }
		
		// stablish connection with the database.
		$dbInstaller = new DBInstaller($dbhost, $dbname, $dbuser, $dbpass);
	
		if ($dbInstaller->getState() == CRM_INSTALL_STATE_SUCCESS) { // database access succeed. Try to set the basic tables.
			// generate a new config file for Creamy, incluying db information & timezone.
			$configContent = file_get_contents(CRM_SKEL_CONFIG_FILE);
			
			$randomStringGenerator = new RandomStringGenerator();
			$crmSecurityCode = $randomStringGenerator->generate(40);
			$customConfig = "
// database configuration
define('DB_USERNAME', '$dbuser');
define('DB_PASSWORD', '$dbpass');
define('DB_HOST', '$dbhost');
define('DB_NAME', '$dbname');
define('DB_PORT', '3306');
		
// General configuration
define('CRM_TIMEZONE', '$timezone');
define('CRM_SECURITY_TOKEN', '$crmSecurityCode');

".CRM_PHP_END_TAG;

			$configContent = str_replace(CRM_PHP_END_TAG, $customConfig, $configContent);
			file_put_contents(CRM_PHP_CONFIG_FILE, $configContent);
			
			// set session credentials and continue installation.
			$_SESSION["dbhost"] = $dbhost;
			$_SESSION["dbname"] = $dbname;
			$_SESSION["dbuser"] = $dbuser;
			$_SESSION["dbpass"] = $dbpass;
			$error = "";
			$currentState = "step2";			
			$_SESSION["installationStep"] = "step2";
			$dbInstaller->closeDatabaseConnection();
		} else {
			$error = $dbInstaller->getLastErrorMessage();
			$currentState = "step1";
			$_SESSION["installationStep"] = "step1";
		}
	} elseif (isset($_POST["submit_step2"])  && $currentState == "step2") { // second step: setup database and create admin user.
		$adminName = NULL;
		$adminPassword = NULL;
		$adminPasswordCheck = NULL;
		$adminEmail = NULL;
		if (isset($_POST["adminName"])) { $adminName = $_POST["adminName"]; }
		if (isset($_POST["adminPassword"])) { $adminPassword = $_POST["adminPassword"]; }
		if (isset($_POST["adminPasswordCheck"])) { $adminPasswordCheck = $_POST["adminPasswordCheck"]; }
		if (isset($_POST["adminEmail"])) { $adminEmail = $_POST["adminEmail"]; }
		
		if (empty($adminName) || empty($adminPassword) || empty($adminPasswordCheck) || empty($adminEmail)) { // unable get admin name or password
			$error = "Unable to get admin name and password. Please try again.";
			$currentState = "step2";
			$_SESSION["installationStep"] = "step2";
		} else { // setup basic database tables.
			if ($adminPassword == $adminPasswordCheck) {
				// create the initial database structure
				$dbhost = $_SESSION["dbhost"];
				$dbname = $_SESSION["dbname"];
				$dbuser = $_SESSION["dbuser"];
				$dbpass = $_SESSION["dbpass"];
				
				if (empty($dbhost) || empty($dbname) || empty($dbuser) || empty($dbpass)) {
					$error = "Unable to get database connection credentials. Installation will not continue.";
					$currentState = "step2";
					$_SESSION["installationStep"] = "step2";
				} else {
					$dbInstaller = new DBInstaller($dbhost, $dbname, $dbuser, $dbpass);
					if ($dbInstaller->setupBasicDatabase($adminName, $adminPassword, $adminEmail)) {
						$error = "";
						$currentState = "step3";			
						$_SESSION["installationStep"] = "step3";
					} else {
						$error = "There was an error setting up the basic database tables: ". $dbInstaller->getLastErrorMessage() or "database not set." ;
						$currentState = "step2";
						$_SESSION["installationStep"] = "step2";
					}
					$dbInstaller->closeDatabaseConnection();
					
					// store the admin email.
					$configContent = file_get_contents(CRM_PHP_CONFIG_FILE);					
					$customConfig = "define('CRM_ADMIN_EMAIL', '$adminEmail');\n".CRM_PHP_END_TAG;
		
					$configContent = str_replace(CRM_PHP_END_TAG, $customConfig, $configContent);
					file_put_contents(CRM_PHP_CONFIG_FILE, $configContent);
					
				}				
			} else {
				$error = "Passwords do not match. Please try again. ";
				$currentState = "step2";
				$_SESSION["installationStep"] = "step2";
			}
			
		}
		
	} elseif (isset($_POST["submit_step3"]) && $currentState == "step3") { // third step: define customer groups and names.
		$dbhost = $_SESSION["dbhost"];
		$dbname = $_SESSION["dbname"];
		$dbuser = $_SESSION["dbuser"];
		$dbpass = $_SESSION["dbpass"];

		if (empty($dbhost) || empty($dbname) || empty($dbuser) || empty($dbpass)) {
			$error = "Unable to get database connection credentials. Installation will not continue.";
			$currentState = "step3";
			$_SESSION["installationStep"] = "step3";
		} else {
			$customersType = "default"; if (isset($_POST["setup_customers"])) $customersType = $_POST["setup_customers"];
			$success = FALSE;
			// build the array of customers' names
			$customerNames = array();
			array_push($customerNames, "contacts");
			if ($customersType == "default") { // default customers schema
				array_push($customerNames, "customers");
			} else if ($customersType == "custom") { // custom customers schema
				$index = 1;
				while (isset($_POST["customCustomerGroup".$index])) {
					array_push($customerNames, $_POST["customCustomerGroup".$index]);
					$index++;
				}
			}
			
			$dbInstaller = new DBInstaller($dbhost, $dbname, $dbuser, $dbpass);
			// setup customers' tables
			if ($dbInstaller->setupCustomerTables($customersType, $customerNames)) {
				// enable customers statistic retrieval
				if ($dbInstaller->setupCustomersStatistics($customersType, $customerNames)) {
					$success = true;
				} else { 
					$success = false;
					$error = "Warning: I was unable to set the customers statistics process for the CRM. Please make sure that you can create and schedule events in the database and make sure the CRM user can start the event scheduler (usually requires super user privileges).";
				}
				$currentState = "final_step";
				$_SESSION["installationStep"] = "final_step";
			} else {
				$success = false;
				$currentState = "step_3";
				$_SESSION["installationStep"] = "step_3";
			}
			$dbInstaller->closeDatabaseConnection();
		}
	} elseif (isset($_POST["submit_final_step"]) && $currentState == "final_step") { // final step: congratulations!
		// create a new installed.txt file to register that we have correctly installed Creamy.
		touch("./installed.txt");
		
		// finally go to the index.
		session_unset();
		header("Location: ./index.php");
		die();
	} else {
		session_unset();
	}
?>
<html class="lockscreen">
    <head>
        <meta charset="UTF-8">
        <title>Creamy</title>
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
        <div class="form-box form-box-install" id="login-box">
			<div class="margin text-center">
				<img src="img/logo.png" width="64" height="64">
			</div>
	<?php if ($currentState == "already_installed") { ?>
            <div class="header"><strong>Creamy</strong> ya está instalado</div>
            <div class="body bg-gray">
	            <h3>¡Vaya!</h3>
	            Parece que ya hay una instalación de <strong>Creamy</strong> en marcha en este directorio. Si quieres borrar la instalación y comenzar una nueva, elimina primero la base de datos asociada y todos los datos, y luego borra el fichero installed.txt. 
			</div>
            <div class="footer text-center">                                                               
                <button type="submit" onclick="window.location.href='index.php';" class="btn bg-light-blue btn-block">Volver a Creamy</button>  
            </div>
	<?php } elseif ($currentState == "step1") { ?>
            <div class="header">Paso 1. Acceso a la base de datos.</div>
            <div class="body bg-gray">
	            <h3>¡Bienvenido!</h3>
	            El proceso de instalación te guiará a través de unos sencillos pasos para completar la puesta en marcha y personalización de tu CRM. <strong>¡Comencemos!</strong>
	            <h3>Base de datos</h3>
	            <p>El CRM necesita una conexión con una base de datos. Para ello, primero crea una base de datos MySQL y un usuario que tenga acceso a dicha base de datos, y después rellena los siguientes campos:</p>
	            <p style="color: red; margin-bottom: -20px;">ATENCIÓN: El proceso de instalación borrará cualquier instalación previa del CRM. Asegúrate de guardar cualquier información previa que pudiera existir en la base de datos.</p>
            </div>
            <form method="post">				
                <div class="body bg-gray">
                    <div class="form-group">
                        <input type="text" name="dbhost" class="form-control" placeholder="Host de la base de datos (o localhost)"/>
                        <input type="text" name="dbname" class="form-control" placeholder="Nombre de la base de datos"/>
                        <input type="text" name="dbuser" class="form-control" placeholder="Usuario de la base de datos"/>
                        <input type="password" name="dbpass" class="form-control" placeholder="Contraseña del usuario en la base de datos"/>
                   	</div>
					<div class="form-group">
						<p>Hemos detectado tu zona horaria como:</p>
                        <?php
		                    // Timezones
		                    $utc = new DateTimeZone('UTC');
							$dt = new DateTime('now', $utc);
							
							print '<select name="userTimeZone" id="userTimeZone" class="form-control">';
							foreach(DateTimeZone::listIdentifiers() as $tz) {
							    $current_tz = new DateTimeZone($tz);
							    $offset =  $current_tz->getOffset($dt);
							    $transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
							    $abbr = $transition[0]['abbr'];
							
								$formatted = sprintf('%+03d:%02u', floor($offset / 3600), floor(abs($offset) % 3600 / 60));
							    print '<option value="' .$tz. '">' .$tz. ' [' .$abbr. ' '. $formatted. ']</option>';
							}
							print '</select>';
	                    ?>
                    </div>          
                	<div name="error-message" style="color: red;">
                	<?php 
	                	if (isset($error)) print ($error); 
                	?>
                	</div>
                </div>
                <div class="footer">                                                               
                    <button type="submit" name="submit_step1" id="submit_step1" class="btn bg-light-blue btn-block">Empezar</button>  
                </div>
            </form>
        
	<?php } elseif ($currentState == "step2") { ?>
            <div class="header">Paso 2. Cuenta de administrador.</div>
            <div class="body bg-gray">
	            <h3>¡Correcto!</h3>
	            Hemos comprobado el acceso a la base de datos. Ahora necesitamos que nos especifiques los datos para la cuenta del administrador principal del CRM. Podrás crear más usuarios más tarde y cambiar los del usuario que introduzcas aquí.
            </div>
            <form method="post">				
                <div class="body bg-gray">
                    <div class="form-group">
                        <input type="text" name="adminName" class="form-control" placeholder="Nombre de usuario del administrador"/>
                        <input type="password" name="adminPassword" class="form-control" placeholder="Contraseña del administrador"/>
                        <input type="password" name="adminPasswordCheck" class="form-control" placeholder="Contraseña del administrador otra vez"/>
                        <input type="text" name="adminEmail" class="form-control" placeholder="Email del administrador"/>
                    </div>          
                	<div name="error-message" style="color: red;">
                	<?php 
	                	if (isset($error)) print ($error); 
                	?>
                	</div>
                </div>
                <div class="footer">                                                               
                    <button type="submit" name="submit_step2" id="submit_step2" class="btn bg-light-blue btn-block">Crear</button>  
                </div>
            </form>

          
    <?php } elseif ($currentState == "step3") { ?>  
            <div class="header">Paso 3. Definir clientes.</div>
            <div class="body bg-gray">
	            <h3>¡Perfecto!</h3>
	            A continuación vamos a definir los diferentes grupos de personas que gestionará el CRM. Hemos hecho una estructura por defecto para ti: <strong>contactos</strong> y <strong>clientes</strong>. Los contactos son los clientes potenciales, aquellos que podrían en el futuro convertirse en clientes, mientras que clientes son aquellos que ya son clientes tuyos. Si necesitas establecer tus clientes en grupos, puedes hacerlo marcando la casilla correspondiente e introduciendo el nombre de cada grupo.
            </div>
            <form method="post">				
                <div class="body bg-gray">
	                <input type="hidden" name="count" value="1" />
                    <div class="form-group">
                        <input type="radio" name="setup_customers" value="default" checked/> Contactos y clientes está bien para mi.
                    </div>
                    <div class="form-group">
                        <input type="radio" name="setup_customers" value="custom" /> Quiero elegir mis grupos de clientes yo mismo.
                    </div>
	                
	                <div name="custom-customers-selection" id="custom-customers-selection" style="display: none;">
	                    <div class="form-group">
	                        <input type="text" name="customCustomerGroupContacts" class="form-control" value="contactos (predeterminado)" disabled/>
	                    </div>
	                    <div class="form-group" id="customCustomerGroup">
	                        <input type="text" autocomplete="off" name="customCustomerGroup1" id="customCustomerGroup1" class="form-control" placeholder="Grupo de clientes 1"/><button id="b1" class="btn add-more" type="button">+</button>
	                    </div>
	                </div>
                	<div name="error-message" style="color: red;">
                	<?php 
	                	if (isset($error)) print ($error); 
                	?>
                	</div>
                </div>
                <div class="footer">                                                               
                    <button type="submit" name="submit_step3" id="submit_step3" class="btn bg-light-blue btn-block">Crear</button>  
                </div>
            </form>

    <?php } elseif ($currentState == "final_step") { ?>  
            <div class="header">Finalizado</div>
            <div class="body bg-gray">
	            <h3>¡Todo está listo!</h3>
	            <p>Todo está listo para que empieces a usar tu CRM. Al pulsar sobre "Comenzar" entrarás a la página de acceso donde debes introducir las credenciales que diste de alta antes para empezar a usar tu CRM. ¡Esperamos que lo disfrutes!.</p>
	        	<div name="error-message" style="color: red;">
		        	<?php 
		            	if (isset($error)) print ($error); 
		        	?>
	        	</div>
				<form method="post">				
	                <div class="body bg-gray">
	                    <button type="submit" name="submit_final_step" id="submit_final_step" class="btn bg-light-blue btn-block">Empezar a usar Creamy</button>  
	                </div>
	            </form>
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
        <script src="js/jstz-1.0.4.min.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(document).ready(function(){
			// detect timezone
			var tz = jstz.determine(); // Determines the time zone of the browser client
			var timezone = tz.name(); //'Asia/Kolhata' for Indian Time.
			$('#userTimeZone').val(timezone);
			
			// show custom customers selection.
		    $('input:radio[name="setup_customers"]').change(function(e){
			    if (this.value == 'custom') {
					$('#custom-customers-selection').fadeIn();
			    } else if (this.value == 'default') {
			        $('#custom-customers-selection').fadeOut();
			    }
		    });
		    
		    // add more custom clients
		    var next = 1;
		    $(".add-more").click(function(e){
		        e.preventDefault();
		        var addto = "#customCustomerGroup" + next;
		        var addRemove = "#customCustomerGroup" + (next);
		        next = next + 1;
		        var newIn = '<input autocomplete="off" type="text" name="customCustomerGroup'+next+'" id="customCustomerGroup'+next+'" class="input form-control" placeholder="Grupo de clientes '+next+'"/>';
		        var newInput = $(newIn);
		        var removeBtn = '<button id="remove' + (next - 1) + '" class="btn btn-danger remove-me" >-</button></div><div id="field">';
		        var removeButton = $(removeBtn);
		        $(addto).after(newInput);
		        $(addRemove).after(removeButton);
		        $("#customCustomerGroup" + next).attr('data-source',$(addto).attr('data-source'));
		        $("#count").val(next);  
		        
	            $('.remove-me').click(function(e){
	                e.preventDefault();
	                var fieldNum = this.id.charAt(this.id.length-1);
	                var fieldID = "#customCustomerGroup" + fieldNum;
	                $(this).remove();
	                $(fieldID).remove();
	            });
		    });
		    
		});
		</script>
    </body>
</html>