<?php
	require_once('./php/CRMDefaults.php');
	require_once('./php/DbInstaller.php');
	require_once('./php/LanguageHandler.php');
	require_once('./php/RandomStringGenerator.php');
	
	// language handler
	$lh = \creamy\LanguageHandler::getInstance();
	
	session_start(); // Starting Session

	$error = ""; 					// Variable To Store Error Message	
	$currentState = "step1";	// current install status variable
	
	// set initial installation step (if found).
	if (isset($_SESSION["installationStep"])) { $currentState = $_SESSION["installationStep"]; }
	
	if (file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
		$currentState = "already_installed"; 
	} elseif (isset($_POST["submit_step1"]) && $currentState == "step1") { // first step: get credentials for database access.
		$dbhost = "localhost";
		$dbname = NULL;
		$dbuser = NULL;
		$dbpass = NULL;
		$timezone = NULL;
		$locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']); if ($locale == NULL) $locale = "en_US";
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
define('CRM_LOCALE', '$locale');
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
			$error = $lh->translationFor("unable_get_admin_credentials");
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
					$error = $lh->translationFor("unable_get_database_credentials");
					$currentState = "step2";
					$_SESSION["installationStep"] = "step2";
				} else {
					$dbInstaller = new DBInstaller($dbhost, $dbname, $dbuser, $dbpass);
					if ($dbInstaller->setupBasicDatabase($adminName, $adminPassword, $adminEmail)) {
						$error = "";
						$currentState = "step3";			
						$_SESSION["installationStep"] = "step3";
					} else {
						$error = $lh->translationFor("error_setting_db_tables")." ". $dbInstaller->getLastErrorMessage() or $lh->translationFor("database_not_set");
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
			$error = $lh->translationFor("unable_get_database_credentials");
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
					// create the notifications triggers.
					if ($dbInstaller->setupCommonTriggers($customersType, $customerNames)) {
						$success = true;
					} else {
						$success = false;
						$error = $lh->translationFor("unable_create_triggers");
					}
				} else { 
					$success = false;
					$error = $lh->translationFor("unable_set_statistics");
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
        <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="./css/creamycrm.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="form-box form-box-install" id="login-box">
			<div class="margin text-center">
				<img src="img/logo.png" width="64" height="64">
			</div>
	<?php if ($currentState == "already_installed") { ?>
            <div class="header"><strong>Creamy</strong> <?php $lh->translateText("is_already_installed"); ?></div>
            <div class="body bg-gray">
	            <h3><?php $lh->translateText("oups"); ?></h3>
	            <?php $lh->translateText("another_installation_creamy"); ?>
			</div>
            <div class="footer text-center">                                                               
                <button type="submit" onclick="window.location.href='index.php';" class="btn bg-light-blue btn-block"><?php $lh->translateText("back_to_creamy"); ?></button>  
            </div>
	<?php } elseif ($currentState == "step1") { ?>
            <div class="header"><?php $lh->translateText("installation_step_1_title"); ?></div>
            <div class="body bg-gray">
	            <h3><?php $lh->translateText("welcome"); ?></h3>
	            <p><?php $lh->translateText("installation_process_steps"); ?></p>
	            <h3><?php $lh->translateText("database"); ?></h3>
	            <p><?php $lh->translateText("your_crm_needs_a_database"); ?></p>
	            <p style="color: red; margin-bottom: -20px;"><?php $lh->translateText("installation_warning"); ?></p>
            </div>
            <form method="post">				
                <div class="body bg-gray">
                    <div class="form-group">
                        <input type="text" name="dbhost" class="form-control" placeholder="<?php $lh->translateText("database_host"); ?>"/>
                        <input type="text" name="dbname" class="form-control" placeholder="<?php $lh->translateText("database_name"); ?>"/>
                        <input type="text" name="dbuser" class="form-control" placeholder="<?php $lh->translateText("database_user"); ?>"/>
                        <input type="password" name="dbpass" class="form-control" placeholder="<?php $lh->translateText("database_password"); ?>"/>
                   	</div>
					<div class="form-group">
						<p><?php $lh->translateText("detected_timezone"); ?></p>
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
            <div class="header"><?php $lh->translateText("installation_step_2_title") ?></div>
            <div class="body bg-gray">
	            <h3><?php $lh->translateText("awesome"); ?></h3>
	            <?php $lh->translateText("database_access_checked") ?>
            </div>
            <form method="post">				
                <div class="body bg-gray">
                    <div class="form-group">
                        <input type="text" name="adminName" class="form-control" placeholder="<?php $lh->translateText("admin_user_name") ?>"/>
                        <input type="password" name="adminPassword" class="form-control" placeholder="<?php $lh->translateText("admin_user_password") ?>"/>
                        <input type="password" name="adminPasswordCheck" class="form-control" placeholder="<?php $lh->translateText("admin_user_password_again") ?>"/>
                        <input type="text" name="adminEmail" class="form-control" placeholder="<?php $lh->translateText("admin_user_email") ?>"/>
                    </div>          
                	<div name="error-message" style="color: red;">
                	<?php 
	                	if (isset($error)) print ($error); 
                	?>
                	</div>
                </div>
                <div class="footer">                                                               
                    <button type="submit" name="submit_step2" id="submit_step2" class="btn bg-light-blue btn-block"><?php $lh->translateText("create_user") ?></button>  
                </div>
            </form>

          
    <?php } elseif ($currentState == "step3") { ?>  
            <div class="header"><?php $lh->translateText("installation_step_3_title"); ?></div>
            <div class="body bg-gray">
	            <h3><?php $lh->translateText("perfect"); ?></h3>
	            <?php $lh->translateText("lets_define_customers"); ?>
            </div>
            <form method="post">				
                <div class="body bg-gray">
	                <input type="hidden" name="count" value="1" />
                    <div class="form-group">
                        <input type="radio" name="setup_customers" value="default" checked/> <?php $lh->translateText("contacts_and_clients_ok"); ?>
                    </div>
                    <div class="form-group">
                        <input type="radio" name="setup_customers" value="custom" /> <?php $lh->translateText("i_want_to_define_groups"); ?>
                    </div>
	                
	                <div name="custom-customers-selection" id="custom-customers-selection" style="display: none;">
	                    <div class="form-group">
	                        <input type="text" name="customCustomerGroupContacts" class="form-control" value="<?php $lh->translateText("contacts_predefined"); ?>" disabled/>
	                    </div>
	                    <div class="form-group" id="customCustomerGroup">
	                        <input type="text" autocomplete="off" name="customCustomerGroup1" id="customCustomerGroup1" class="form-control" placeholder="<?php $lh->translateText("customer_group"); ?> 1"/><button id="b1" class="btn add-more" type="button">+</button>
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
	            <h3><?php $lh->translateText("everythings_ready"); ?></h3>
	            <p><?php $lh->translateText("ready_to_start_creamy"); ?></p>
	        	<div name="error-message" style="color: red;">
		        	<?php 
		            	if (isset($error)) print ($error); 
		        	?>
	        	</div>
				<form method="post">				
	                <div class="body bg-gray">
	                    <button type="submit" name="submit_final_step" id="submit_final_step" class="btn bg-light-blue btn-block"><?php $lh->translateText("start_using_creamy"); ?></button>  
	                </div>
	            </form>
            </div>
        	
	<?php } ?>
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
		        var newIn = '<input autocomplete="off" type="text" name="customCustomerGroup'+next+'" id="customCustomerGroup'+next+'" class="input form-control" placeholder="<?php $lh->translateText("customer_group"); ?> '+next+'"/>';
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