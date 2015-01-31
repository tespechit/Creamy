<?php
	// check if Creamy has been installed.
	require_once('./php/CRMDefaults.php');
	if (!file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
		header("location: ./install.php");
	}
	
	// initialize session and DDBB handler
    require_once('./php/Session.php');
	include_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');
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
                                        <?php print $db->getNumberOfTodayNotifications($_SESSION["userid"])." ".$lh->translationFor("new");  ?>
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
                                        <?php print $db->getNumberOfNewCustomers()." ".$lh->translationFor("new"); ?>
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
                                        <?php print $db->getNumberOfNewContacts()." ".$lh->translationFor("new"); ?>
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

                    <div class="row">
                        <div class="col-xs-12">
							<div class="box collapsed-box">
	                            <div class="box-header">
		                            <div class="box-tools pull-right">
                                        <button class="btn btn-primary btn-sm" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                        <button class="btn btn-primary btn-sm" data-widget="remove"><i class="fa fa-times"></i></button>
                                    </div>
	                                <i class="fa fa-info"></i>
	                                <h3 class="box-title"><?php $lh->translateText("getting_started"); ?></h3>
	                            </div>
                                <div class="box-body" style="display: none;">
								<?php
									$gettingStartedFile = str_replace(".html", "_".CRM_LOCALE.".html", CRM_GETTING_STARTED_FILE);
									if (!file_exists($gettingStartedFile)) { $gettingStartedFile = CRM_GETTING_STARTED_FILE; } // fallback to default file
									$gettingStartedContents = file_get_contents($gettingStartedFile);
									print $gettingStartedContents;
								?>
	                            </div>
	                        </div>

                        </div>
                    </div>
                    
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
											<?php print $db->generateSendToUserSelect($_SESSION["userid"]); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="<?php $lh->translateText("subject"); ?>"/>
                                        </div>
                                        <div>
                                            <textarea class="textarea" placeholder="<?php $lh->translateText("message"); ?>" id="message" name="message" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                        <input type="hidden" name="fromuserid" id="fromuserid" value="<?php print $_SESSION["userid"] ?>">
                                        <div id="messagesendingresult" name="messagesendingresult"></div>
								</div>
                                <div class="box-footer clearfix">
                                    <button type="submit" class="pull-right btn btn-default" id="sendEmail"><?php $lh->translateText("send"); ?> <i class="fa fa-send"></i></button>
                                </div>
                            </form>
                            </div>
                        </section><!-- right col -->
                    </div><!-- /.row (main row) -->

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
            {y: '2011 Q1', item1: 2666, item2: 2666},
            {y: '2011 Q2', item1: 2778, item2: 2294},
            {y: '2011 Q3', item1: 4912, item2: 1969},
            {y: '2011 Q4', item1: 3767, item2: 3597},
            {y: '2012 Q1', item1: 6810, item2: 1914},
            {y: '2012 Q2', item1: 5670, item2: 4293},
            {y: '2012 Q3', item1: 4820, item2: 3795},
            {y: '2012 Q4', item1: 15073, item2: 5967},
            {y: '2013 Q1', item1: 10687, item2: 4460},
            {y: '2013 Q2', item1: 8432, item2: 5713}
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