<?php
	require_once('./php/DbHandler.php');
	require_once('./php/CRMDefaults.php');
	require_once('./php/LanguageHandler.php');
    require('./php/Session.php');

	// DDBB & Language vars
    $db = new DbHandler();
    $lh = LanguageHandler::getInstance();
    
    // session vars
	$userid = $_SESSION["userid"];
	$userrole = $_SESSION["userrole"];
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
                        <?php $lh->translateText("tasks"); ?>
                        <small><?php $lh->translateText("manage_your_time"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-tasks"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li class="active"><?php $lh->translateText("tasks"); ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
	                
				<?php if (userHasBasicPermission($_SESSION["userrole"])) { ?>

				<!-- Unfinished tasks row -->
				<div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <i class="ion ion-clipboard"></i>
                                <h3 class="box-title"><?php $lh->translateText("unfinished_tasks"); ?></h3>
                            </div><!-- /.box-header -->
                            <div class="box-body table-responsive" id="task-table-container">
								<?php 
									print $db->getUnfinishedTasksAsTable($userid, $userrole); 
								?>
                            </div><!-- /.box-body -->

                        </div><!-- /.box -->
                    </div>
                </div>
       
                <div class="row">
                    <div class="col-xs-12">
						<div class="box collapsed-box">
                            <div class="box-header">
	                            <div class="box-tools pull-right">
                                    <button class="btn btn-sm" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                    <button class="btn btn-sm" data-widget="remove"><i class="fa fa-times"></i></button>
                                </div>
                                <i class="ion ion-clipboard"></i>
                                <h3 class="box-title"><?php $lh->translateText("completed_tasks"); ?></h3>
                            </div>
                            <div class="box-body table-responsive" id="task-table-container" style="display: none;">
								<?php 
									print $db->getCompletedTasksAsTable($userid, $userrole); 
								?>
                            </div><!-- /.box-body -->
                        </div>

                    </div>
                </div>
                    
                <!-- Only users with write permission can create new tasks -->
                <?php if (userHasWritePermission($_SESSION["userrole"])) { ?>
                
                <!-- .row -->
                <div class="row">
                    <div class="col-xs-12">

                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title"><?php $lh->translateText("new_task"); ?></h3>
                            </div><!-- /.box-header -->
                            <!-- form start -->
                            <form role="form" name="createtask" id="createtask">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="taskDescription"><?php $lh->translateText("task_description"); ?></label>
                                        <input type="text required" class="form-control" id="taskDescription" name="taskDescription" placeholder="<?php $lh->translateText("task_description"); ?>">
                                    </div>
                                    <!-- assign task to other users only if current user has manager privileges -->
									<?php if (userHasManagerPermission($userrole)) { ?>
                                    <div class="form-group">
                                        <label for="touserid"><?php $lh->translateText("assign_this_task_to"); ?></label>
										<?php print $db->generateSendToUserSelect($_SESSION["userid"], true, $lh->translationFor("assign_this_task_to")); ?>
                                    </div>
                                    <?php } ?>
                                    
                                    <input type="hidden" id="userid" name="userid" value="<?php print($_SESSION["userid"]); ?>">
                                    <br>
                                    <div  id="resultmessage" name="resultmessage" style="display:none">
                                    </div>

                                </div><!-- /.box-body -->

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary"><?php $lh->translateText("create_task"); ?></button>
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

	<!-- CHANGE TASK MODAL -->

    <div class="modal fade" id="edit-task-dialog-modal" name="edit-task-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-edit"></i> <?php $lh->translateText("modify_task_description"); ?></h4>
                    <p><?php $lh->translateText("insert_new_description"); ?></p>
                </div>
                <form action="" method="post" name="edit-task-form" id="edit-task-form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-task-description"><?php $lh->translateText("new_description"); ?></label>
                            <input type="text required" class="form-control" id="edit-task-description" name="edit-task-description" placeholder="<?php $lh->translateText("new_description"); ?>">
                        </div>
						<input type="hidden" id="edit-task-taskid" name="edit-task-taskid" value="">
						<div id="changetaskresult" name="changetaskresult"></div>
                    </div>
                    <div class="modal-footer clearfix">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="changetaskCancelButton"><i class="fa fa-times"></i> <?php $lh->translateText("cancel"); ?></button>
                        <button type="submit" class="btn btn-primary pull-left" id="changetaskOkButton"><i class="fa fa-check-circle"></i> <?php $lh->translateText("modify_task"); ?></button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->		

	<!-- /CHANGE TASK MODAL -->
	
	<!-- TASK DIALOGS -->

	<!-- END TASK DIALOGS -->

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
        <!-- Bootstrap slider -->
        <script src="js/plugins/bootstrap-slider/bootstrap-slider.js" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
			/** 
			 * Creates a new task.
		 	 */
			$("#createtask").validate({
				rules: {
					taskDescription: "required",
				},
				submitHandler: function() {
					//submit the form
						$("#resultmessage").html();
						$("#resultmessage").fadeOut();
						$.post("./php/CreateTask.php", //post
						$("#createtask").serialize(), 
							function(data){
								//if message is sent
								if (data == 'success') {
									location.reload();
								} else {
									$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_create_task"); ?>: '+ data);
									$("#resultmessage").fadeIn(); //show confirmation message
								}
								//
							});
					return false; //don't let the form refresh the page...
				}					
			});
		
			/**
			 * Delete a task
			 */
			 $(".delete-task-action").click(function(e) {
				var r = confirm("<?php $lh->translateText("are_you_sure"); ?>");
				e.preventDefault();
				if (r == true) {
					var taskid = $(this).attr('href');
					$.post("./php/DeleteTask.php", { "taskid": taskid } ,function(data){
						if (data == "success") { location.reload(); }
						else { alert ("<?php $lh->translateText("unable_delete_task"); ?>"); }
					});
				}
			 });
			 
			/**
			 * Show the edit task dialog, filling the edit fields properly.
			 */
			$(".edit-task-action").click(function(e) {
				// Set ID of the task to edit
		        var ele = $(this).parents("li").first();
				var task_id = ele[0].id; // task ID is contained in the ID element of the li object.
				$('#edit-task-taskid').val(task_id);
				
				// set the previous description of task.
				var current_text = $('.text', ele);
				$('#edit-task-description').val(current_text.text());
			});
		
			/**
			 * Edit the description of a task
			 */
			$("#edit-task-form").validate({
				submitHandler: function() {
					//submit the form
						$("#resultmessage").html();
						$("#resultmessage").fadeOut();
						$.post("./php/ModifyTask.php", //post
						$("#edit-task-form").serialize(), 
							function(data){
								//if message is sent
								if (data == 'success') {
									location.reload();
								} else {
									$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_modify_task"); ?>: '+ data);
									$("#resultmessage").fadeIn(); //show confirmation message
								}
								//
							});
					return false; //don't let the form refresh the page...
				}					
			});
		
		
			/**
			 * React to checking and unchecking of boxes -- Mark tasks as completed.
			 */
		    $('input', this).on('ifChecked', function(event) {
		        var ele = $(this).parents("li").first();
				// task ID is contained in the ID element of the li object.
				var task_id = ele[0].id;
				
				// clear current result field
				$("#changetaskresult").html();
				$("#changetaskresult").fadeOut();
				
				// mark item as "done" and call ModifyTask. 
		        ele.toggleClass("done");
				$.post("./php/CompleteTask.php", {"complete-task-taskid": task_id, "complete-task-progress": "100" }, 
				function(data){
					if (data == "success") { location.reload(); }
					else {
						$("#changetaskresult").html(data);
						$("#changetaskresult").fadeIn();
					}
				});
		    });
		
		    $('input', this).on('ifUnchecked', function(event) {
		        var ele = $(this).parents("li").first();
		        ele.toggleClass("done");
		    });
			
		});
		</script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>


    </body>
</html>
