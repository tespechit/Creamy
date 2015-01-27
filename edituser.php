<?php
    require('./php/Session.php');
	include_once('./php/DbHandler.php');
    $db = new DbHandler();
     
    if (isset($_POST["userid"])) { $userid = $_POST["userid"]; }
    else { $userid = $_SESSION["userid"]; }
    
    
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Creamy - Inicio</title>
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
                        Gestión de Usuario
                        <small>Edición de los datos de usuario</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-edit"></i> Inicio</a></li>
                        <li class="active">Editar usuario</li>
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
	                			$errormessage = "¡Vaya! Parece que no tienes permisos para editar la información de este usuario. Solo puedes editar tu propia información de usuario a menos que seas administrador.";
                			}
                		} else {
	                		$errormessage = "¡Vaya! Se ha producido un error inesperado. Por favor, vuelve a la pantalla anterior e inténtalo de nuevo.";
                		}
                		
                		if (!empty($userobj)) {
                	?>
                	
                	<!-- tabla editar usuarios -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Introduzca los nuevos datos</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" id="modifyuser" name="modifyuser" method="post" action=""  enctype="multipart/form-data">
                                	<input type="hidden" id="modifyid" name="modifyid" value="<?php print $userid ?>">
                                    <div class="box-body">
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                        <input type="text" id="name" name="name" class="form-control required" placeholder="Nombre" value="<?php print $userobj["name"]; ?>" disabled>
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                        <input type="text" id="email" name="email" class="form-control"placeholder="Email (opcional)" value="<?php print $userobj["email"]; ?>">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Teléfono (opcional)" value="<?php print $userobj["phone"]; ?>">
	                                    </div>
	                                    <br>
                                        <div class="form-group">
                                            <label for="exampleInputFile">Avatar del usuario (opcional)</label><br>
                                            <?php
                                            	if (!empty($userobj["avatar"])) {
	                                            	print("<img src=\"".$userobj["avatar"]."\" class=\"img-circle\" width=\"100\" height=\"100\" alt=\"User Image\" /><br>");
                                            	}
                                            ?>
                                            <br>
                                            <input type="file" id="avatar" name="avatar">
                                            <p class="help-block">Inserta un fichero de imagen .jpg, .gif o .png. Máximo 2MB.</p>
                                        </div>
                                        <?php if ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN) { ?> 
                                        <div class="form-group">
                                            <label for="role">Rol del usuario</label>
											<?php print $db->getUserRolesAsFormSelect($userobj["role"]); ?>
                                        </div>
                                        <?php } ?>
	                                    <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>

                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Modificar usuario</button>
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
