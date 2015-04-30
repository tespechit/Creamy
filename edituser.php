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
	
	require_once('./php/UIHandler.php');
	require_once('./php/LanguageHandler.php');
    require('./php/Session.php');

	$ui = \creamy\UIHandler::getInstance();    
    $lh = \creamy\LanguageHandler::getInstance();
	$user = \creamy\CreamyUser::currentUser();
     
    if (isset($_POST["userid"])) { $userid = $_POST["userid"]; }
    else { $userid = $this->getUserId(); }
    
    
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
		<?php print $ui->creamyHeader($user); ?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($user->getUserId(), $user->getUserName(), $user->getUserRole(), $user->getUserAvatar()); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php $lh->translateText("users_management"); ?>
                        <small><?php $lh->translateText("edit_user_data"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-edit"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li class="active"><?php $lh->translateText("edit_user"); ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                	<?php print $ui->getEditUserForm($userid, $user->getUserId(), $user->userHasAdminPermission()) ?>
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
        <!-- AdminLTE App -->
        <script src="js/AdminLTE/app.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>

		<script type="text/javascript">
		$(document).ready(function() {
			/** 
			 * modifies a user.
		 	 */
			$("#modifyuser").validate({
				submitHandler: function(e) {
					//submit the form
						$("#resultmessage").html();
						$("#resultmessage").hide();
						var formData = new FormData(e);
		
						$.ajax({
						  url: "./php/ModifyUser.php",
						  data: formData,
						  processData: false,
						  contentType: false,
						  type: 'POST',
						  success: function(data) {
								if (data == 'success') {
									$("#resultmessage").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check">\
									</i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
									<b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("data_successfully_modified"); ?>');
									$("#resultmessage").fadeIn(); //show confirmation message
								} else {
									$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i>\
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
									<b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("error_modifying_data"); ?>: '+ data);
									$("#resultmessage").fadeIn(); //show confirmation message
								}
						    }
						});
					return false; //don't let the form refresh the page...
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
