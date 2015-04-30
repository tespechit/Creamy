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

// check if Creamy has been installed.
require_once('./php/CRMDefaults.php');
if (!file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
	header("location: ./install.php");
}

// initialize session and DDBB handler
require_once('./php/Session.php');
include_once('./php/UIHandler.php');
require_once('./php/LanguageHandler.php');
$ui = \creamy\UIHandler::getInstance();
$lh = \creamy\LanguageHandler::getInstance();
$user = \creamy\CreamyUser::currentUser();
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
                        <?php $lh->translateText("home"); ?>
                        <small><?php $lh->translateText("your_creamy_dashboard"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-bar-chart-o"></i> <?php $lh->translateText("home"); ?></a></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-xs-4">
                            <!-- small box -->
                            <div class="small-box bg-orange">
                                <div class="inner">
                                    <h3>
                                        <?php print $ui->generateLabelForTodayNotifications($user->getUserId());  ?>
                                    </h3>
                                    <p>
                                        <?php $lh->translateText("notifications"); ?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-clock"></i>
                                </div>
                                <a href="notifications.php" class="small-box-footer">
                                    <?php $lh->translateText("see_more"); ?>  <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-4">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3>
                                        <?php print $ui->generateLabelForNewCustomers(); ?>
                                    </h3>
                                    <p>
                                        <?php $lh->translateText("customers"); ?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-stalker"></i>
                                </div>
                                <a href="./customerslist.php?customer_type=clients_2" class="small-box-footer">
                                    <?php $lh->translateText("see_more"); ?>  <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-4">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>
                                        <?php print $ui->generateLabelForNewContacts(); ?>
                                    </h3>
                                    <p>
                                        <?php $lh->translateText("contacts"); ?>
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="./customerslist.php?customer_type=clients_1&customer_name=Contactos" class="small-box-footer">
                                    <?php $lh->translateText("see_all"); ?> <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->

                    </div><!-- /.row -->

                    <!-- Main row -->
                    <div class="row">
                        <!-- Left col -->
                        <section class="col-lg-7 connectedSortable"> 
	                    	<!-- Gráfica de clientes -->   
	                        <div class="box box-info">
	                            <div class="box-header">
	                                <i class="fa fa-bar-chart-o"></i>
	                                <h3 class="box-title"><?php $lh->translateText("customer_statistics"); ?></h3>
	                            </div>
                                <div class="box-body" id="graph-box">
                                    <div class="chart" id="revenue-chart" style="position: relative; height: 375px;"></div>
	                            </div>
	                        </div>
                        </section><!-- /.Left col -->
                        
                        <!-- right col (We are only adding the ID to make the widgets sortable)-->
                        <section class="col-lg-5 connectedSortable"> 

                            <!-- quick message widget -->
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-envelope"></i>
                                    <h3 class="box-title"><?php $lh->translateText("messaging_system"); ?></h3>
                                </div>
                                <div class="box-body">
                                    <form action="#" method="post" id="send-message-form" name="send-message-form">
                                        <div class="form-group">
											<?php print $ui->generateSendToUserSelect($user->getUserId()); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="<?php $lh->translateText("subject"); ?>"/>
                                        </div>
                                        <div>
                                            <textarea class="textarea" placeholder="<?php $lh->translateText("message"); ?>" id="message" name="message" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                        <input type="hidden" name="fromuserid" id="fromuserid" value="<?php print $user->getUserId(); ?>">
                                        <div id="messagesendingresult" name="messagesendingresult"></div>
								</div>
                                <div class="box-footer clearfix">
                                    <button type="submit" class="pull-right btn btn-default" id="sendEmail"><?php $lh->translateText("send"); ?> <i class="fa fa-send"></i></button>
                                </div>
                            </form>
                            </div>
                        </section><!-- right col -->
                    </div><!-- /.row (main row) -->

					<?php print $ui->hooksForDashboard(); ?>

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
        <!-- Morris.js charts -->
        <script src="js/raphael-min.js"></script>
        <script src="js/plugins/morris/morris.min.js" type="text/javascript"></script>
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

        <script>
        	// load data.
            $(".textarea").wysihtml5({"image": false});

            var area = new Morris.Area({
        element: 'revenue-chart',
        resize: true,
        data: [
			<?php print $ui->getStatisticsData(); ?>
        ],
        xkey: 'y',
        ykeys: ['item1', 'item2'],
        labels: ['<?php $lh->translateText("contacts"); ?>', '<?php $lh->translateText("customers"); ?>'],
        lineColors: ['#a0d0e0', '#3c8dbc'],
        hideHover: 'auto'
    });
        </script>

    </body>
</html>
