<?php
	include_once "./php/DbHandler.php";
    $db = new DbHandler();
    include "./php/Session.php";
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
        <!-- Theme style -->
        <link href="css/creamycrm.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap slider -->
        <link href="css/bootstrap-slider/slider.css" rel="stylesheet" type="text/css" />

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
                        Tareas
                        <small>Gestiona tu tiempo</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-tasks"></i> Inicio</a></li>
                        <li class="active">Tareas</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
	                
				<?php if (userHasBasicPermission($_SESSION["userrole"])) { ?>
	                
				<div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Mis tareas</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive" id="task-table-container">
									<?php 
										$userid = $_SESSION["userid"];
										print $db->getAllMyTasksAsTable($userid); 
									?>
                                </div><!-- /.box-body -->

                            </div><!-- /.box -->
                        </div>
                    </div>
                    
                    <!-- Only users with write permission can create new tasks -->
                    <?php if (userHasWritePermission($_SESSION["userrole"])) { ?>
                    
                    <!-- .row -->
                    <div class="row">
                        <div class="col-xs-12">

                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Nueva tarea</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" name="createtask" id="createtask">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="taskDescription">Descripción de la tarea</label>
                                            <input type="text required" class="form-control" id="taskDescription" name="taskDescription" placeholder="Descripción de la tarea">
                                        </div>
                                        <div class="form-group">
                                            <div id="taskInitialProgressLabel"><label for="taskInitialProgress">Porcentaje inicial completado: (0%)</label></div>
											<input type="text required" value="" id="taskInitialProgress" name="taskInitialProgress" class="slider form-control" data-slider-min="0" data-slider-max="100" data-slider-step="5" data-slider-value="0" data-slider-orientation="horizontal">
                                        </div>
                                        <input type="hidden" id="userid" name="userid" value="<?php print($_SESSION["userid"]); ?>">
                                        <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>

                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Crear tarea</button>
                                    </div>
                                </form>
                            </div><!-- /.box -->

                        </div>
                    </div> <!-- /.row -->

                    <?php } ?>


                </section><!-- /.content -->
				
				<?php } else { print $db->getUnauthotizedAccessMessage(); } ?>
           
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

	<!-- TASK DIALOGS -->
		
	<!-- CHANGE TASK MODAL -->

    <div class="modal fade" id="complete-task-dialog-modal" name="complete-task-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-tasks"></i> Progreso de la tarea</h4>
                    <p>Inserta el nuevo progreso para la tarea. Si desplazas el indicador totalmente a la derecha (100%), significará que la tarea se ha completado completamente, y se considerará finalizada.</p>
                </div>
                <form action="" method="post" name="modify-task-form" id="modify-task-form">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group" id="task-progress-properties-content">
                            </div>
                        </div>
						<input type="hidden" id="complete-task-taskid" name="complete-task-taskid" value="">
						<div id="changetaskresult" name="changetaskresult"></div>
                    </div>
                    <div class="modal-footer clearfix">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="changetaskCancelButton"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary pull-left" id="changetaskOkButton"><i class="fa fa-check-circle"></i> Modificar tarea</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->		

	<!-- /CHANGE TASK MODAL -->

	<!-- TASK INFO MODAL -->

    <div class="modal fade" id="info-task-dialog-modal" name="info-task-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-tasks"></i> Datos sobre la tarea</h4>
                </div>
	            <div class="box">
	                <div class="box-body" id="task-info-content" name="task-info-content">
					
	                </div><!-- /.box-body -->
	            </div><!-- /.box -->


            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->		

	<!-- /TASK INFO MODAL -->


	<!-- END TASK DIALOGS -->



        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
        <!-- Bootstrap slider -->
        <script src="js/plugins/bootstrap-slider/bootstrap-slider.js" type="text/javascript"></script>
		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script src="js/messages_es.min.js" type="text/javascript"></script>
		<script src="js/tasksforms.js" type="text/javascript"></script>

        <script type="text/javascript">
            $(function() {
                /* BOOTSTRAP SLIDER */
                $('#taskInitialProgress').slider({
	                tooltip: "hide"
                }).on('slide', function(ev) {
	                var newValue = ev.value;
	                if (!newValue) newValue = 0;
	                $('#taskInitialProgress').value = newValue;
	                $('#taskInitialProgressLabel').html("<label for=\"taskInitialProgress\">Porcentaje inicial completado: ("+ newValue +"%)</label>");
                });
				
			});
        </script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>


    </body>
</html>
