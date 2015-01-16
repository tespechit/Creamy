<?php
	include_once "./php/DbHandler.php";
    $db = new DbHandler();
    include "./php/Session.php";
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Creamy - Inicio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Morris chart -->
        <link href="css/morris/morris.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap wysihtml5 - text editor -->
        <link href="css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
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
                    		print $db->getMessageNotifications($_SESSION["userid"]);   
	                    	print $db->getAlertNotifications($_SESSION["userid"]);
	                    	print $db->getTaskNotifications($_SESSION["userid"]);
	                    	print $db->getUserMenu($_SESSION["userid"], $_SESSION["username"], $_SESSION["avatar"]);
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
                        Administración
                        <small>Gestión del sistema</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
                        <li class="active">Administración</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <?php if ($_SESSION["userrole"] == CRM_DEFAULTS_USER_ROLE_ADMIN) { ?>
                	<!-- tabla muestra los usuarios -->
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Usuarios</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive" id="users_table">
									<?php print $db->getAllUsersAsTable(); ?>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>
                	<!-- /tabla muestra los usuarios -->

					<!-- Filas con acciones, formularios y demás -->

                    <div class="row">
                        <!-- left column -->
                        <div class="col-md-6">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Nuevo usuario</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" id="createuser" name="createuser" method="post" action="" enctype="multipart/form-data">
                                    <div class="box-body">
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                        <input type="text" id="name" name="name" class="form-control required" placeholder="Nombre">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                        <input type="text" id="email" name="email" class="form-control"placeholder="Email (opcional)">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="Teléfono (opcional)">
	                                    </div>
	                                    <br>
										<div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                        <input type="password" id="password1" name="password1" class="form-control required" placeholder="Contraseña">
	                                    </div>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                        <input type="password" id="password2" name="password2" class="form-control required" placeholder="Repetir Contraseña">
	                                    </div>
	                                    <br>
                                        <div class="form-group">
                                            <label for="exampleInputFile">Avatar del usuario (opcional)</label>
                                            <input type="file" id="avatar" name="avatar">
                                            <p class="help-block">Inserta un fichero de imagen .jpg, .gif o .png. Máximo 2MB.</p>
                                        </div>
                                        <div class="checkbox">Dar permisos de administrador a este usuario
                                            <label>
                                                <input id="isAdmin" name="isAdmin" type="checkbox">
                                            </label>
                                        </div>
	                                    <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>

                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Crear usuario</button>
                                    </div>

                                </form>
                            </div><!-- /.box -->

                        </div><!--/.col (left) -->
                        <!-- right column -->
                        <div class="col-md-6">
                            <!-- quick email widget -->
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-envelope"></i>
                                    <h3 class="box-title">Correo Interno</h3>
                                </div>
                                <div class="box-body">
                                    <form action="#" method="post" id="send-message-form" name="send-message-form">
                                        <div class="form-group">
											<?php print $db->generateMailToUserSelect($_SESSION["userid"]); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject"/>
                                        </div>
                                        <div>
                                            <textarea class="textarea" placeholder="Mensaje" id="message" name="message" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                        <input type="hidden" name="fromuserid" id="fromuserid" value="<?php print $_SESSION["userid"] ?>">
                                        <div id="messagesendingresult" name="messagesendingresult"></div>
								</div>
                                <div class="box-footer clearfix">
                                    <button type="submit" class="pull-right btn btn-default" id="sendEmail">Send <i class="fa fa-arrow-circle-right"></i></button>
                                </div>
                            </form>
                            </div>
                        </div><!--/.col (right) -->
                    </div>   <!-- /.row -->

				<!-- /fila con acciones, formularios y demás -->
				<?php
					} else {
						print $db->getErrorMessage("Lo siento, no tiene los permisos necesarios para en esta sección.");
					}
				?>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

		<!-- change password of user from admin -->
	    <div class="modal fade" id="change-password-admin-dialog-modal" name="change-password-admin-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                    <h4 class="modal-title"><i class="fa fa-lock"></i> Cambiar contraseña</h4>
	                </div>
	                <form action="" method="post" name="adminpasswordform" id="adminpasswordform">
	                    <div class="modal-body">
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                <input name="new_password_1" id="new_password_1" type="password" class="form-control" placeholder="Introduce la nueva contraseña que quieres">
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                <input name="new_password_2" id="new_password_2" type="password" class="form-control" placeholder="Vuelve a introducir la nueva contraseña">
	                            </div>
	                        </div>
							<input type="hidden" id="usertochangepasswordid" name="usertochangepasswordid">
							<div id="changepasswordadminresult" name="changepasswordadminresult"></div>
	                    </div>
	                    <div class="modal-footer clearfix">
	                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="change-password-admin-cancel-button"><i class="fa fa-times"></i> Cancelar</button>
	                    <button type="submit" class="btn btn-primary pull-left" id="change-password-admin-ok-button"><i class="fa fa-check-circle"></i> Cambiar contraseña</button>
	                    </div>
	                </form>
	            </div><!-- /.modal-content -->
	        </div><!-- /.modal-dialog -->
	    </div><!-- /.modal -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>

        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<script src="js/sendmessageform.js" type="text/javascript"></script>

		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="js/messages_es.min.js" type="text/javascript"></script>
		<script src="js/adminforms.js" type="text/javascript"></script>

        <script>
        	// load data.
            $(".textarea").wysihtml5({"image": false});
		</script>

    </body>
</html>
