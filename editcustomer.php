<?php
	require_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');
    require('./php/Session.php');

	// initialize structures
    $db = new DbHandler();
    $lh = LanguageHandler::getInstance();

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
                    		print $db->getMessageNotifications($_SESSION["userid"], $_SESSION["userrole"]);   
	                    	print $db->getAlertNotifications($_SESSION["userid"], $_SESSION["userrole"]);
	                    	print $db->getTaskNotifications($_SESSION["userid"], $_SESSION["userrole"]);
	                    	print $db->getUserMenu($_SESSION["userid"], $_SESSION["username"], $_SESSION["avatar"], $_SESSION["userrole"]);
                    	?>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $db->getSidebar($_SESSION["userid"], $_SESSION["username"], $_SESSION["userrole"], $_SESSION["avatar"]); ?>

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
                	
                	<?php
                		$customerobj = NULL;
                		$errormessage = NULL;
                		
                		if (isset($customerid) && isset($customerType)) {
                			$customerobj = $db->getDataForCustomer($customerid, $customerType);
                		} else {
	                		$errormessage = "¡Vaya! Se ha producido un error inesperado. No tenemos los datos necesarios para modificar el usuario. Inténtalo de nuevo o contacta con el administrador.";
                		}
                		
                		if (!empty($customerobj)) {
                	?>
                	
                	<!-- tabla editar usuarios -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Introduzca los nuevos datos</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
				                <form action="" method="post" name="modifycustomerform" id="modifycustomerform">
				                    <div class="modal-body">
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
				                                <input name="name" id="name" type="text" class="form-control" value="<?php print $customerobj["name"]; ?>" placeholder="<?php print $lh->translationFor("name")." (".$lh->translationFor("mandatory").")"; ?>">
				                            </div>
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-medkit"></i></span>
				                                <input name="productType" id="productType" value="<?php print $customerobj["type"]; ?>" type="text" class="form-control" placeholder="<?php $lh->translateText("customer_or_service_type"); ?>">
				                            </div>
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
				                                <input name="id_number" id="id_number" type="text" class="form-control" placeholder="<?php $lh->translateText("id_number"); ?>" value="<?php print $customerobj["id_number"]; ?>">
				                            </div>
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
				                                <input name="email" id="email" type="text" class="form-control" placeholder="<?php $lh->translateText("email"); ?>" value="<?php print $customerobj["email"]; ?>">
				                            </div>                  
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
				                                <input name="phone" id="phone" type="text" class="form-control" placeholder="<?php $lh->translateText("home_phone"); ?>" value="<?php print $customerobj["phone"]; ?>">
				                            </div>                  
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
				                                <input name="mobile" id="mobile" type="text" class="form-control" placeholder="<?php $lh->translateText("mobile_phone"); ?>" value="<?php print $customerobj["mobile"]; ?>">
				                            </div>                  
				                        </div>
				                        <div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
				                                <input name="address" id="address" type="text" class="form-control" placeholder="<?php $lh->translateText("address"); ?>" value="<?php print $customerobj["address"]; ?>">
				                            </div>                  
				                        </div>
				                        <div class="form-group">
				                            <div class="row">
											<div class="col-lg-6">
					                            <div class="input-group">
					                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
					                                <input name="city" id="city" type="text" class="form-control" placeholder="<?php $lh->translateText("city"); ?>" value="<?php print $customerobj["city"]; ?>">
					                            </div>
					                        </div><!-- /.col-lg-6 -->
					                        <div class="col-lg-6">
					                            <div class="input-group">
					                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
					                                <input name="state" id="state" type="text" class="form-control" placeholder="<?php $lh->translateText("estate"); ?>" value="<?php print $customerobj["state"]; ?>">
					                            </div>                        
					                        </div><!-- /.col-lg-6 -->
				                            </div>
				                        </div>
				                        <div class="form-group">
				                            <div class="row">
											<div class="col-lg-6">
					                            <div class="input-group">
					                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
					                                <input name="zipcode" id="zipcode" type="text" class="form-control" placeholder="<?php $lh->translateText("zip_code"); ?>" value="<?php print $customerobj["zip_code"]; ?>">
					                            </div>
					                        </div><!-- /.col-lg-6 -->
					                        <div class="col-lg-6">
					                            <div class="input-group">
					                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
					                                <input name="country" id="country" type="text" class="form-control" placeholder="<?php $lh->translateText("country"); ?>" value="<?php print $customerobj["country"]; ?>">
					                            </div>                        
					                        </div><!-- /.col-lg-6 -->
				                            </div>
				                        </div>
										<div class="form-group">
				                            <div class="input-group">
				                                <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
				                                <textarea id="notes" name="notes" placeholder="<?php $lh->translateText("notes"); ?>" class="form-control"><?php print $customerobj["notes"]; ?></textarea>
				                            </div>                  
				                        </div>
				                        <div class="form-group">
				                            <label><?php $lh->translateText("marital_status"); ?></label>
				                            <select class="form-control" id="maritalstatus" name="maritalstatus">
					                        <?php 
						                        $currentMS = 0;
						                        if (!empty($customerobj["marital_status"])) {
							                        $currentMS = $customerobj["marital_status"];
							                        if ($currentMS < 1) $currentMS = 0;
							                        if ($currentMS > 5) $currentMS = 0;
						                        }
						                        
					                        ?>
												<option value="0" <?php if ($currentMS == 0) print "selected"; ?>><?php $lh->translateText("choose_an_option"); ?></option>
				                                <option value="1" <?php if ($currentMS == 1) print "selected"; ?>><?php $lh->translateText("single"); ?></option>
				                                <option value="2" <?php if ($currentMS == 2) print "selected"; ?>><?php $lh->translateText("married"); ?></option>
				                                <option value="3" <?php if ($currentMS == 3) print "selected"; ?>><?php $lh->translateText("divorced"); ?></option>
				                                <option value="4" <?php if ($currentMS == 4) print "selected"; ?>><?php $lh->translateText("separated"); ?></option>
				                                <option value="5" <?php if ($currentMS == 5) print "selected"; ?>><?php $lh->translateText("widow"); ?></option>
				                            </select>
				                        </div>
										<div class="form-group">
				                            <label>Sexo</label>
				                            <select class="form-control" id="gender" name="gender">
					                        <?php 
						                        $currentGender = -1;
						                        if (!empty($customerobj["gender"])) {
							                        $currentGender = $customerobj["gender"];
							                        if ($currentMS < 0) $currentGender = -1;
							                        if ($currentMS > 1) $currentGender = -1;
						                        }
						                        
					                        ?>
												<option value="-1" <?php if ($currentGender == -1) print "selected"; ?>><?php $lh->translateText("choose_an_option"); ?></option>
				                                <option value="0" <?php if ($currentGender == 0) print "selected"; ?>><?php $lh->translateText("female"); ?></option>
				                                <option value="1" <?php if ($currentGender == 1) print "selected"; ?>><?php $lh->translateText("male"); ?></option>
				                            </select>
				                        </div>
				                        <div class="form-group">
				                            <label><?php $lh->translateText("birthdate"); ?>:</label>
				                            <div class="input-group">
				                                <div class="input-group-addon">
				                                    <i class="fa fa-calendar"></i>
				                                </div>
				                                <?php
					                                $dateAsDMY = NULL;
					                                if (!empty($customerobj["birthdate"])) { 
						                                $time = strtotime($customerobj["birthdate"]);
						                                $dateAsDMY = date('d/m/Y', $time); 
						                            }
					                            ?>
				                                <input name="birthdate" id="birthdate" type="text" class="form-control" data-inputmask="'alias': 'dd/mm/yyyy'" data-mask value="<?php print $dateAsDMY ?>" placeholder="dd/mm/yyyy"/>
				                            </div><!-- /.input group -->
				                        </div><!-- /.form group -->                        
				                        <div class="form-group">
				                            <div class="checkbox">
				                                <label><input name="donotsendemail" id="donotsendemail" type="checkbox" <?php if (!empty($customerobj["do_not_send_email"])) print "checked"; ?>/> <?php $lh->translateText("do_not_send_email"); ?></label>
				                            </div>
				                        </div>
										<input type="hidden" id="customer_type" name="customer_type" value="<?php print $customerType; ?>">
										<input type="hidden" id="customerid" name="customerid" value="<?php print $customerid; ?>">
										<div id="modifycustomerresult" name="modifycustomerresult"></div>
				                    </div>
				                    <div class="modal-footer clearfix">
				                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="modifyCustomerDeleteButton" href="<?php print $customerid ?>"><i class="fa fa-times"></i> <?php $lh->translateText("delete"); ?></button>
				                        <button type="submit" class="btn btn-primary pull-left" id="modifyCustomerOkButton"><i class="fa fa-check-circle"></i> <?php $lh->translateText("modify"); ?></button>
				                    </div>
				                </form>


                            </div><!-- /.box -->
                	<!-- /tabla editar usuarios -->
					<?php
						} else {
							print $db->getErrorMessage($errormessage);
						}
					?>
				<!-- /fila con acciones, formularios y demás -->

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