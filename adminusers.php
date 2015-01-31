<?php
	require_once('./php/DbHandler.php');
    require_once('./php/LanguageHandler.php');
    include('./php/Session.php');

    $db = new DbHandler();
	$lh = LanguageHandler::getInstance();
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
                        <?php $lh->translateText("administration"); ?>
                        <small><?php $lh->translateText("users_management"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-dashboard"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li class="active"><?php $lh->translateText("administration"); ?></li>
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
                        <section class="col-lg-6 connectedSortable">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title"><?php $lh->translateText("new_user"); ?></h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" id="createuser" name="createuser" method="post" action="" enctype="multipart/form-data">
                                    <div class="box-body">
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                        <input type="text" id="name" name="name" class="form-control required" placeholder="<?php $lh->translateText("name"); ?>">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                        <input type="text" id="email" name="email" class="form-control"placeholder="<?php $lh->translateText("email")." (".$lh->translationFor("optional").")"; ?>">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="<?php $lh->translateText("phone")." (".$lh->translationFor("optional").")"; ?>">
	                                    </div>
	                                    <br>
										<div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                        <input type="password" id="password1" name="password1" class="form-control required" placeholder="<?php $lh->translateText("password"); ?>">
	                                    </div>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                        <input type="password" id="password2" name="password2" class="form-control required" placeholder="<?php $lh->translateText("repeat_password"); ?>">
	                                    </div>
	                                    <br>
                                        <div class="form-group">
                                            <label for="exampleInputFile"><?php $lh->translateText("user_avatar")." (".$lh->translationFor("optional").")"; ?></label>
                                            <input type="file" id="avatar" name="avatar">
                                            <p class="help-block"><?php $lh->translateText("choose_image"); ?></p>
                                        </div>
                                        <div class="form-group">
                                            <label for="role"><?php $lh->translateText("user_role"); ?></label>
											<?php print $db->getUserRolesAsFormSelect(); ?>
                                        </div>
	                                    <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>

                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary"><?php $lh->translateText("create_user"); ?></button>
                                    </div>

                                </form>
                            </div><!-- /.box -->

                        </section><!--/.col (left) -->
                        <!-- right column -->
                        <section class="col-lg-6 connectedSortable">
                            <!-- quick email widget -->
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-envelope"></i>
                                    <h3 class="box-title"><?php $lh->translateText("messaging_system"); ?></h3>
                                </div>
                                <div class="box-body">
                                    <form action="#" method="post" id="send-message-form" name="send-message-form">
                                        <div class="form-group">
											<?php print $db->generateSendToUserSelect($_SESSION["userid"]); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="<?php $lh->translateText("subject"); ?>"/>
                                        </div>
                                        <div>
                                            <textarea class="textarea" placeholder="<?php $lh->translateText("message"); ?>" id="message" name="message" style="width: 100%; height: 150px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                        <input type="hidden" name="fromuserid" id="fromuserid" value="<?php print $_SESSION["userid"] ?>">
                                        <div id="messagesendingresult" name="messagesendingresult"></div>
								</div>
                                <div class="box-footer clearfix">
                                    <button type="submit" class="pull-right btn btn-default" id="sendEmail"><?php $lh->translateText("send"); ?> <i class="fa fa-arrow-circle-right"></i></button>
                                </div>
                            </form>
                            </div>
                        </section><!--/.col (right) -->
                    </div>   <!-- /.row -->

				<!-- /fila con acciones, formularios y demás -->
				<?php
					} else {
						print $db->getErrorMessage($lh->translationFor("you_dont_have_permission"));
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
	                    <h4 class="modal-title"><i class="fa fa-lock"></i> <?php $lh->translateText("change_password"); ?></h4>
	                </div>
	                <form action="" method="post" name="adminpasswordform" id="adminpasswordform">
	                    <div class="modal-body">
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                <input name="new_password_1" id="new_password_1" type="password" class="form-control" placeholder="<?php $lh->translateText("insert_new_password"); ?>">
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
	                                <input name="new_password_2" id="new_password_2" type="password" class="form-control" placeholder="<?php $lh->translateText("insert_new_password_again"); ?>">
	                            </div>
	                        </div>
							<input type="hidden" id="usertochangepasswordid" name="usertochangepasswordid">
							<div id="changepasswordadminresult" name="changepasswordadminresult"></div>
	                    </div>
	                    <div class="modal-footer clearfix">
	                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="change-password-admin-cancel-button"><i class="fa fa-times"></i> <?php $lh->translateText("cancel"); ?></button>
	                    <button type="submit" class="btn btn-primary pull-left" id="change-password-admin-ok-button"><i class="fa fa-check-circle"></i> <?php $lh->translateText("change_password"); ?></button>
	                    </div>
	                </form>
	            </div><!-- /.modal-content -->
	        </div><!-- /.modal-dialog -->
	    </div><!-- /.modal -->

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>

        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<script type="text/javascript">
			$(document).ready(function() {

			/** 
			 * Sends a message
		 	 */
			$("#send-message-form").validate({
				rules: {
					subject: "required",
					message: "required",
					touserid: {
					  	required: true,
					  	min: 1,
		        		number: true
					}
				},
			    messages: {
			        touserid: "You must choose a user to send the message to",
				},
				submitHandler: function() {
					//submit the form
						$("#messagesendingresult").html();
						$("#messagesendingresult").hide();
						$.post("./php/SendMessage.php", //post
						$("#send-message-form").serialize(), 
							function(data){
								//if message is sent
								if (data == 'success') {
									$("#messagesendingresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("message_successfully_sent"); ?>');
									$("#messagesendingresult").fadeIn(); //show confirmation message
									$("#send-message-form")[0].reset();
			
								} else {
									$("#messagesendingresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_send_message"); ?>: '+ data);
									$("#messagesendingresult").fadeIn(); //show confirmation message
								}
								//
							});
					return false; //don't let the form refresh the page...
				}					
			});
			 
		});
		</script>

		<!-- Forms and actions -->
		<script src="js/jquery.validate.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			 $(document).ready(function() {
 			 	/** 
				 * Creates a new user.
			 	 */
				$("#createuser").validate({
					rules: {
						name: "required",
						password1: "required",
					    password2: {
					      minlength: 8,
					      equalTo: "#password1"
					    }
			   		},
					submitHandler: function(e) {
						//submit the form
							$("#resultmessage").html();
							$("#resultmessage").hide();
							var formData = new FormData(e);
			
							$.ajax({
							  url: "./php/CreateUser.php",
							  data: formData,
							  processData: false,
							  contentType: false,
							  type: 'POST',
							  success: function(data) {
									if (data == 'success') {
										$("#resultmessage").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check">\
										</i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
										<b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("user_successfully_created"); ?>');
										$("#resultmessage").fadeIn(); //show confirmation message
									} else {
										$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i>\
										<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
										<b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_create_user"); ?>: '+ data);
										$("#resultmessage").fadeIn(); //show confirmation message
									}
							    }
							});
						return false; //don't let the form refresh the page...
					}					
				});
				
				/**
				 * Delete user.
				 */
				 $(".delete-action").click(function(e) {
					var r = confirm("<?php $lh->translateText("are_you_sure"); ?>");
					e.preventDefault();
					if (r == true) {
						var user_id = $(this).attr('href');
						$.post("./php/DeleteUser.php", { userid: user_id } ,function(data){
							if (data == "success") { location.reload(); }
							else { alert ("<?php $lh->translateText("unable_delete_user"); ?>"); }
						});
					}
				 });
			
				 /**
				  * Edit user details
				  */
				 $(".edit-action").click(function(e) {
					e.preventDefault();
					var url = './edituser.php';
					var form = $('<form action="' + url + '" method="post"><input type="hidden" name="userid" value="' + $(this).attr('href') + '" /></form>');
					//$('body').append(form);  // This line is not necessary
					$(form).submit();
				 });
			
				 /**
				  * Deactivate user
				  */
				 $(".deactivate-user-action").click(function(e) {
					e.preventDefault();
					var user_id = $(this).attr('href');
					$.post("./php/SetUserStatus.php", { "userid": user_id, "status": 0 } ,function(data){
						if (data == "success") { location.reload(); }
						else { alert ("<?php $lh->translateText("unable_set_user_status"); ?>"); }
					});
				 });
			
				 /**
				  * Activate user
				  */
				 $(".activate-user-action").click(function(e) {
					e.preventDefault();
					var user_id = $(this).attr('href');
					$.post("./php/SetUserStatus.php", { "userid": user_id, "status": 1 } ,function(data){
						if (data == "success") { location.reload(); }
						else { alert ("<?php $lh->translateText("unable_set_user_status"); ?>"); }
					});
				 });
			
				 /**
				  * Show change user password.
				  */
				 $(".change-password-action").click(function(e) {
					e.preventDefault();
					var usertochangepasswordid = $(this).attr('href');
					$("#usertochangepasswordid").val(usertochangepasswordid);
					$("change-password-admin-ok-button").show();
					$("#changepasswordadminresult").html();
					$("#changepasswordadminresult").hide();
			
					$("#change-password-admin-dialog-modal").modal('show');
				 });
			
				 /**
				  * Change user password from admin.
				  */
				 $("#adminpasswordform").validate({
					rules: {
						new_password1: "required",
					    new_password2: {
						  required: true,
					      minlength: 8,
					      equalTo: "#password1"
					    }
			   		},
					submitHandler: function(e) {
						$.post("./php/ChangePasswordAdmin.php", //post
						$("#adminpasswordform").serialize(), 
							function(data){
								//if message is sent
								if (data == 'success') {
									$("#changepasswordadminresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("password_successfully_changed"); ?>');
									$("#changepasswordadminresult").fadeIn(); //show confirmation message
									$("change-password-admin-ok-button").fadeOut();
									$("#adminpasswordform")[0].reset();
			
								} else {
									$("#changepasswordadminresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_change_password"); ?>: '+ data);
									$("#changepasswordadminresult").fadeIn(); //show confirmation message
								}
								//
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
