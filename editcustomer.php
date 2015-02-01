<?php
	require_once('./php/UIHandler.php');
	require_once('./php/LanguageHandler.php');
    require('./php/Session.php');

	// initialize structures
	$ui = \creamy\UIHandler::getInstance();
    $lh = \creamy\LanguageHandler::getInstance();

    $customerType = NULL;
    $customerid = NULL;
    if (isset($_GET["customer_type"])) { $customerType = $_GET["customer_type"]; }
	if (isset($_GET["customerid"])) { $customerid = $_GET["customerid"]; }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Creamy</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Morris chart -->
        <link href="css/morris/morris.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap wysihtml5 - text editor -->
        <link href="css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="css/creamycrm.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="./index.php" class="logo">
	            <img src="img/logoWhite.png" width="32" height="32">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                Creamy
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                    	<?php 
                    		print $ui->getMessageNotifications($_SESSION["userid"], $_SESSION["userrole"]);   
	                    	print $ui->getAlertNotifications($_SESSION["userid"], $_SESSION["userrole"]);
	                    	print $ui->getTaskNotifications($_SESSION["userid"], $_SESSION["userrole"]);
	                    	print $ui->getTopbarItems($_SESSION["userid"], $_SESSION["username"], $_SESSION["avatar"], $_SESSION["userrole"]);
                    	?>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($_SESSION["userid"], $_SESSION["username"], $_SESSION["userrole"], $_SESSION["avatar"]); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php $lh->translateText("customers_and_contacts_management"); ?>
                        <small><?php $lh->translateText("personal_data_edition"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-edit"></i> <?php $lh->translateText("home"); ?></a></li>
                        <?php 
	                        if (isset($customerType)) {
	                        	print ('<li><a href="customerslist.php?customer_type='.$customerType.'"> '.$lh->translationFor("customer_list").'</a></li>');
	                        }
                        ?>
                        <li class="active"><?php $lh->translateText("modify"); ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
					<?php print $ui->generateCustomerEditionForm($customerid, $customerType); ?>                	
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>

        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>

		<!-- InputMask -->
	    <script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
	    <script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
	    <script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {	
				//Datemask dd/mm/yyyy
			    $("#birthdate").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
			
				/** 
				 * Modifies a customer
			 	 */
				$("#modifycustomerform").validate({
					submitHandler: function() {
						//submit the form
							$("#resultmessage").html();
							$("#resultmessage").fadeOut();
							$.post("./php/ModifyCustomer.php", //post
							$("#modifycustomerform").serialize(), 
								function(data){
									//if message is sent
									if (data == 'success') {
										$("#modifycustomerresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("data_successfully_modified"); ?>');
										$("#modifycustomerresult").fadeIn(); //show confirmation message
				
									} else {
										$("#modifycustomerresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("error_modifying_data"); ?>: '+ data);
										$("#modifycustomerresult").fadeIn(); //show confirmation message
									}
									//
								});
						return false; //don't let the form refresh the page...
					}					
				});
				
				/**
				 * Deletes a customer
				 */
				 $("#modifyCustomerDeleteButton").click(function (e) {
					var r = confirm("<?php $lh->translateText("are_you_sure"); ?>");
					e.preventDefault();
					if (r == true) {
						var customerid = $(this).attr('href');
						$.post("./php/DeleteCustomer.php", $("#modifycustomerform").serialize() ,function(data){
							if (data == "success") { 
								alert("<?php $lh->translateText("customer_successfully_deleted"); ?>");
								window.location = "index.php";
							}
							else { alert ("<?php $lh->translateText("unable_delete_customer"); ?>: "+data); }
						});
					}
				 });
				 
			});
		</script>


        <script>
        	// load data.
            $(".textarea").wysihtml5({"image": false});
		</script>

    </body>
</html>
