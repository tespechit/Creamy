<?php
	/**
		The MIT License (MIT)
		
		Copyright (c) 2015 Ignacio Nieto Carvajal
		
		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the "Software"), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:
		
		The above copyright notice and this permission notice shall be included in
		all copies or substantial portions of the Software.
		
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
		THE SOFTWARE.
	*/
	

	require_once('./php/CRMDefaults.php');
	require_once('./php/UIHandler.php');
	require_once('./php/LanguageHandler.php');
    include('./php/Session.php');

    $ui = \creamy\UIHandler::getInstance();
    $lh = \creamy\LanguageHandler::getInstance();
    
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
        <title>Creamy</title>
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
		<?php print $ui->creamyHeader($_SESSION["userid"], $_SESSION["userrole"], $_SESSION["username"], $_SESSION["avatar"]); ?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($_SESSION["userid"], $_SESSION["username"], $_SESSION["userrole"], $_SESSION["avatar"]); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php $lh->translateText("messages"); ?>
                        <small><?php $lh->translateText("messaging_system"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-envelope"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li class="active"><?php $lh->translateText("my_messages"); ?></li>
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
                                    <h3 class="box-title"><?php $lh->translateText("users"); ?></h3>
                                </div><!-- /.box-header -->
                                <div class="box-body" id="messages-message">
	                                <?php
		                            	if (!empty($message)) {
			                            	print $ui->calloutInfoMessage($message);
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
                                                <h3 class="box-title"><?php print strtoupper($lh->translationFor("inbox")); ?></h3>
                                            </div>
                                            <!-- compose message btn -->
                                            <a class="btn btn-block btn-primary" data-toggle="modal" data-target="#compose-modal" id="new-message-link" name="new-message-link"><i class="fa fa-pencil"></i> <?php $lh->translateText("new_message"); ?></a>
                                            <!-- Navigation - folders-->
                                            <div style="margin-top: 15px;">
                                                <ul class="nav nav-pills nav-stacked">
	                                                <?php print $ui->getMessageFoldersAsList($folder); ?>
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
                                                            <li><a href="#" id="messages-mark-as-read"><?php $lh->translateText("mark_as_read"); ?></a></li>
                                                            <li><a href="#" id="messages-mark-as-unread"><?php $lh->translateText("mark_as_unread"); ?></a></li>
                                                            <li><a href="#" id="messages-mark-as-favorite"><?php $lh->translateText("mark_as_favorite"); ?></a></li>
                                                            <li class="divider"></li>
                                                            <?php if ($folder != MESSAGES_GET_DELETED_MESSAGES) { ?>
                                                            <li><a href="#" id="messages-send-to-junk"><?php $lh->translateText("send_to_trash"); ?></a></li>
                                                            <li class="divider"></li>
                                                            <?php } else { ?>
                                                            <li><a href="#" id="messages-restore-message"><?php $lh->translateText("recover_from_trash"); ?></a></li>
                                                            <li class="divider"></li>
                                                            <?php } ?>
                                                            <li><a href="#" id="messages-delete-permanently"><?php $lh->translateText("delete"); ?></a></li>
                                                        </ul>
                                                    </div>

                                                </div>
                                            </div><!-- /.row -->

                                            <div class="table table-responsive table-bordered table-striped" id="message-list">
                                                <!-- THE MESSAGES -->
												<?php
													print $ui->getMessagesFromFolderAsTable($_SESSION["userid"], $folder);
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
					<?php } else { print $ui->getUnauthotizedAccessMessage(); } ?>
					
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
        <script type="text/javascript">
			 $(document).ready(function() {
			 
			 	var selectedMessages = [];
			 
			     "use strict";
			
				 // ------------- Favorites -------------------
			
			    //iCheck for checkbox and radio inputs
			    $('input[type="checkbox"]').iCheck({
			        checkboxClass: 'icheckbox_minimal-blue',
			        radioClass: 'iradio_minimal-blue'
			    });
			    
			    // check individual message
				$('input[type=checkbox]').on("ifUnchecked", function(e) {
					var index = selectedMessages.indexOf(e.currentTarget.value);
					if (index >= 0) selectedMessages.splice(index, 1);
				});
			    
			    // uncheck individual message
				$('input[type=checkbox]').on("ifChecked", function(e) {
					if (e.currentTarget.value != 'on') selectedMessages.push(e.currentTarget.value);
				});
			
			    //When unchecking the checkbox
			    $("#check-all").on('ifUnchecked', function(event) {
			        //Uncheck all checkboxes
			        $("input[type='checkbox']", ".mailbox").iCheck("uncheck");
			    });
			    //When checking the checkbox
			    $("#check-all").on('ifChecked', function(event) {
			        //Check all checkboxes
			        $("input[type='checkbox']", ".mailbox").iCheck("check");
			    });
			    // de-star a starred video
			    $(".fa-star, .glyphicon-star, .fa-star-o, .glyphicon-star-empty").click(function(e) {
			        e.preventDefault();
			        
			        // e.currentTarget.id contiene el id del mensaje.
			        //detect type
			        var glyph = $(this).hasClass("glyphicon");
			        var fa = $(this).hasClass("fa");
					var starredByStar = $(this).hasClass("fa-star");
					var starredByGlyph = $(this).hasClass("glyphicon-star");
					var favorite = 1;
					var selectedItem = this;
					
					if (starredByGlyph || starredByStar) { // unmark message as favorite
						favorite = 0;   
					} // else mark message as favorite
					
					$.post("./php/MarkMessagesAsFavorite.php", { "favorite": favorite, "messageids": [e.currentTarget.id], "folder": folder } ,function(data){
							if (data == "success") { 
								$("#messages-message-box").hide();
								// toggle visual change.
						        if (fa) {
						            $(selectedItem).toggleClass("fa-star");
						            $(selectedItem).toggleClass("fa-star-o");
						        }		
							    if (glyph) {
							        $(selectedItem).toggleClass("glyphicon-star");
							        $(selectedItem).toggleClass("glyphicon-star-empty");
							    }
							}
							else {
								$("#messages-message-box").hide();
								$("#messages-message").html("<div class=\"callout callout-danger\"><h4><?php $lh->translateText("message"); ?></h4><p><?php $lh->translateText("unable_set_favorite"); ?></p></div>");
								$("#messages-message-box").fadeIn();
							}
						});
					
			    });
			
			    // mark messages as read.
			    $("#messages-mark-as-favorite").click(function(e) {
				    if (selectedMessages.length > 0) {
						$.post("./php/MarkMessagesAsFavorite.php", { "messageids": selectedMessages, "folder": folder, "favorite": 1 } ,function(data){
							if (data == "success") { location.reload(); }
							else {
								$("#messages-message-box").hide();
								$("#messages-message").html("<div class=\"callout callout-danger\"><h4><?php $lh->translateText("message"); ?></h4><p><?php $lh->translateText("unable_set_favorite"); ?>: "+data+"</p></div>");
								$("#messages-message-box").fadeIn();
							}
						});
				    }
			    });
			
			    //Initialize WYSIHTML5 - text editor
			    $("#email_message").wysihtml5();
			    
				// ------------- Read / Unread -------------------
			    
			    // mark messages as read.
			    $("#messages-mark-as-read").click(function(e) {
				    if (selectedMessages.length > 0) {
						$.post("./php/MarkMessagesAsRead.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
							if (data == "success") { location.reload(); }
							else {
								$("#messages-message-box").hide();
								$("#messages-message").html("<div class=\"callout callout-danger\"><h4><?php $lh->translateText("message"); ?></h4><p><?php $lh->translateText("unable_set_read"); ?>: "+data+"</p></div>");
								$("#messages-message-box").fadeIn();
							}
						});
				    }
			    });
			        
			    // mark messages as read.
			    $("#messages-mark-as-unread").click(function(e) {
				    if (selectedMessages.length > 0) {
						$.post("./php/MarkMessagesAsUnread.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
							if (data == "success") { location.reload(); }
							else {
								$("#messages-message-box").hide();
								$("#messages-message").html("<div class=\"callout callout-danger\"><h4><?php $lh->translateText("message"); ?></h4><p><?php $lh->translateText("unable_set_unread"); ?>: "+data+"</p></div>");
								$("#messages-message-box").fadeIn();
							}
						});
				    }
			    });
			    
			    // -------------------- Junk and delete messages ----------------------
			    
			    // send to junk mail
			    $("#messages-send-to-junk").click(function (e) {
			   		e.preventDefault();
						$("#messages-message-box").hide();
			   		$.post("./php/JunkMessages.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
			   			reloadWithMessage(data+" <?php $lh->translateText("out_of"); ?> "+selectedMessages.length+" <?php $lh->translateText("messages_sent_trash"); ?>");
					});
			    });
			        
			    // restore mail from junk
			    $("#messages-restore-message").click(function (e) {
			   		e.preventDefault();
			   		$.post("./php/UnjunkMessages.php", { "messageids": selectedMessages } ,function(data){
			   			reloadWithMessage(data+" <?php $lh->translateText("out_of"); ?> "+selectedMessages.length+" <?php $lh->translateText("messages_recovered_trash"); ?>");
					});
			    });
			    
			    // delete messages.
			    $("#messages-delete-permanently").click(function (e) {
				    if (selectedMessages.length < 1) return;
					var r = confirm("<?php $lh->translateText("are_you_sure"); ?>");
					e.preventDefault();
					if (r == true) {
						$.post("./php/DeleteMessages.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
							if (data == "success") { location.reload(); }
							else { alert ("<?php $lh->translateText("unable_delete_messages"); ?>"); }
						});
					}
			    });
			    
			    function reloadWithMessage(message) {
				    var url = window.location.href;
					if (url.indexOf('?') > -1){
					   url += '&message='+message;
					} else{
					   url += '?message='+message;
					}
					window.location.href = url;
			    }
			    
			});
        </script>

    </body>
</html>
