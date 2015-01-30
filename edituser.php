<?php
	require_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');
    require('./php/Session.php');
    
    $db = new DbHandler();
    $lh = LanguageHandler::getInstance();
     
    if (isset($_POST["userid"])) { $userid = $_POST["userid"]; }
    else { $userid = $_SESSION["userid"]; }
    
    
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
                        <?php print $lh->text("users_management"); ?>
                        <small><?php print $lh->text("edit_user_data"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-edit"></i> <?php print $lh->text("home"); ?></a></li>
                        <li class="active"><?php print $lh->text("edit_user"); ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                	
                	<?php
                		$userobj = NULL;
                		$errormessage = NULL;
                		
                		if (isset($userid)) {
                			if (($_SESSION["userid"] == $userid) || ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN)) { 
	                			// if it's the same user or we have admin privileges.
	                			$userobj = $db->getDataForUser($userid);
                			} else {
	                			$errormessage = $lh->text("not_permission_edit_user_information");
                			}
                		} else {
	                		$errormessage = $lh->text("unknown_error");
                		}
                		
                		if (!empty($userobj)) {
                	?>
                	
                	<!-- tabla editar usuarios -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title"><?php print $lh->text("insert_new_data"); ?></h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" id="modifyuser" name="modifyuser" method="post" action=""  enctype="multipart/form-data">
                                	<input type="hidden" id="modifyid" name="modifyid" value="<?php print $userid ?>">
                                    <div class="box-body">
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                        <input type="text" id="name" name="name" class="form-control required" placeholder="<?php print $lh->text("name"); ?>" value="<?php print $userobj["name"]; ?>" disabled>
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                        <input type="text" id="email" name="email" class="form-control"placeholder="<?php print $lh->text("email")." (".$lh->text("optional").")"; ?>" value="<?php print $userobj["email"]; ?>">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="<?php print $lh->text("phone")." (".$lh->text("optional").")"; ?>" value="<?php print $userobj["phone"]; ?>">
	                                    </div>
	                                    <br>
                                        <div class="form-group">
                                            <label for="exampleInputFile"><?php print $lh->text("user_avatar")." (".$lh->text("optional").")"; ?></label><br>
                                            <?php
                                            	if (!empty($userobj["avatar"])) {
	                                            	print("<img src=\"".$userobj["avatar"]."\" class=\"img-circle\" width=\"100\" height=\"100\" alt=\"User Image\" /><br>");
                                            	}
                                            ?>
                                            <br>
                                            <input type="file" id="avatar" name="avatar">
                                            <p class="help-block"><?php print $lh->text("choose_image"); ?></p>
                                        </div>
                                        <?php if ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN) { ?> 
                                        <div class="form-group">
                                            <label for="role"><?php print $lh->text("user_role"); ?></label>
											<?php print $db->getUserRolesAsFormSelect($userobj["role"]); ?>
                                        </div>
                                        <?php } ?>
	                                    <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>

                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary"><?php print $lh->text("edit_user"); ?></button>
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
		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="js/messages_es.min.js" type="text/javascript"></script>
		<script src="js/modifyuserform.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>


        <script>
        	// load data.
            $(".textarea").wysihtml5({"image": false});
		</script>

    </body>
</html>
