<?php
	require_once('./php/CRMDefaults.php');
	require_once('./php/DbHandler.php');
    include('./php/Session.php');

    $db = new DbHandler();
    
	if (isset($_GET["folder"])) {
		$folder = $_GET["folder"];
	} else $folder = MESSAGES_GET_INBOX_MESSAGES;
	if ($folder < 0 || $folder > MESSAGES_MAX_FOLDER) { $folder = MESSAGES_GET_INBOX_MESSAGES; }

	if (isset($_GET["message"])) {
		$message = $_GET["message"];
	} else $message = NULL;

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Creamy - Mensajes</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap wysihtml5 - text editor -->
        <link href="css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <!-- DATA TABLES -->
        <link href="./css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- iCheck for checkboxes and radio inputs -->
        <link href="css/iCheck/minimal/blue.css" rel="stylesheet" type="text/css" />
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
                        Mensajes
                        <small>Correo interno</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-envelope"></i> Inicio</a></li>
                        <li class="active">Mis mensajes</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
				
	                <!-- check permissions -->
	                <?php if (userHasBasicPermission($_SESSION["userrole"])) { ?>
					<!-- hidden message box -->
					
					<div class="row" id="messages-message-box" <?php if (empty($message)) { print 'style="display: none;"'; } ?>>
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Usuarios</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body" id="messages-message">
	                                <?php
		                            	if (!empty($message)) {
			                            	print $db->getInfoMessage($message);
		                            	}
		                            ?>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>
					
					<!-- end hidden message box -->

                    <!-- MAILBOX BEGIN -->
                    <div class="mailbox row">
                        <div class="col-xs-12">
                            <div class="box box-solid">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-4">
                                            <!-- BOXES are complex enough to move the .box-header around.
                                                 This is an example of having the box header within the box body -->
                                            <div class="box-header">
                                                <i class="fa fa-inbox"></i>
                                                <h3 class="box-title">ENTRADA</h3>
                                            </div>
                                            <!-- compose message btn -->
                                            <a class="btn btn-block btn-primary" data-toggle="modal" data-target="#compose-modal" id="new-message-link" name="new-message-link"><i class="fa fa-pencil"></i> Nuevo mensaje</a>
                                            <!-- Navigation - folders-->
                                            <div style="margin-top: 15px;">
                                                <ul class="nav nav-pills nav-stacked">
	                                                <?php 
		                                                $unreadMessages = $db->getUnreadMessagesNumber($_SESSION["userid"]);
	                                                ?>
                                                    <li class="header">Carpetas</li>
                                                    <li <?php if ($folder == MESSAGES_GET_INBOX_MESSAGES) print ' class="active"'; ?>><a href="messages.php?folder=0"><i class="fa fa-inbox"></i> Entrada (<?php print $unreadMessages; ?>)</a></li>
                                                    <!--<li><a href="#"><i class="fa fa-pencil-square-o"></i> Borradores</a></li>-->
                                                    <li <?php if ($folder == MESSAGES_GET_SENT_MESSAGES) print ' class="active"'; ?>><a href="messages.php?folder=3"><i class="fa fa-mail-forward"></i> Enviado</a></li>
                                                    <li <?php if ($folder == MESSAGES_GET_FAVORITE_MESSAGES) print ' class="active"'; ?>><a href="messages.php?folder=4"><i class="fa fa-star"></i> Favoritos</a></li>
                                                    <li <?php if ($folder == MESSAGES_GET_DELETED_MESSAGES) print ' class="active"'; ?>><a href="messages.php?folder=2"><i class="fa fa-folder"></i> Papelera</a></li>
                                                </ul>
                                            </div>
                                        </div><!-- /.col (LEFT) -->
                                        <div class="col-md-9 col-sm-8">
                                            <div class="row pad">
                                                <div class="col-sm-6">
                                                    <label style="margin-right: 10px;">
                                                        <input type="checkbox" id="check-all"/>
                                                    </label>
                                                    <!-- Action button -->
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default btn-sm btn-flat dropdown-toggle" data-toggle="dropdown">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li><a href="#" id="messages-mark-as-read">Marcar como leido</a></li>
                                                            <li><a href="#" id="messages-mark-as-unread">Marcar como no leido</a></li>
                                                            <li><a href="#" id="messages-mark-as-favorite">Marcar como favorito</a></li>
                                                            <li class="divider"></li>
                                                            <?php if ($folder != MESSAGES_GET_DELETED_MESSAGES) { ?>
                                                            <li><a href="#" id="messages-send-to-junk">Enviar a la papelera</a></li>
                                                            <li class="divider"></li>
                                                            <?php } else { ?>
                                                            <li><a href="#" id="messages-restore-message">Sacar de la papelera</a></li>
                                                            <li class="divider"></li>
                                                            <?php } ?>
                                                            <li><a href="#" id="messages-delete-permanently">Borrar</a></li>
                                                        </ul>
                                                    </div>

                                                </div>
                                            </div><!-- /.row -->

                                            <div class="table table-responsive table-bordered table-striped" id="message-list">
                                                <!-- THE MESSAGES -->
												<?php
													print $db->getMessagesFromFolderAsTable($_SESSION["userid"], $folder);
												?>

                                            </div><!-- /.table-responsive -->
                                        </div><!-- /.col (RIGHT) -->
                                    </div><!-- /.row -->
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div><!-- /.col (MAIN) -->
                    </div>
                    <!-- MAILBOX END -->
					<!-- user not authorized -->
					<?php } else { print $db->getUnauthotizedAccessMessage(); } ?>
					
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
		<!-- DATA TABES SCRIPT -->
        <script src="./js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="./js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- Morris.js charts -->
        <script src="js/raphael-min.js"></script>
        <script src="js/plugins/morris/morris.min.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<?php include_once "./php/ModalMessageDialogs.php" ?>

        <!-- Page script -->
        <script type="text/javascript">
	    	var folder = <?php print $folder; ?>;

			$(document).ready(function() {
                $("#messagestable").dataTable( {
					"bFilter": false, //Disable search function
					"bJQueryUI": true, //Enable smooth theme
					"sDom": 'tp'
                } );
            });
	    </script>
        <script src="js/messagesform.js" type="text/javascript"></script>

    </body>
</html>
