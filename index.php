<?php
	// check if Creamy has been installed.
	require_once('./php/CRMDefaults.php');
	if (!file_exists(CRM_INSTALLED_FILE)) { // check if already installed 
		header("location: ./install.php");
	}
	
	// initialize session and DDBB handler
    require_once('./php/Session.php');
	include_once('./php/DbHandler.php');
    $db = new DbHandler();
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
                        Home
                        <small>Your Creamy dashboard</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-bar-chart-o"></i> Home</a></li>
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
                                        <?php print $db->getNumberOfTodayNotifications($_SESSION["userid"]) ?> new
                                    </h3>
                                    <p>
                                        Notifications
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-clock"></i>
                                </div>
                                <a href="notifications.php" class="small-box-footer">
                                    See more  <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-4">
                            <!-- small box -->
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3>
                                        <?php print $db->getNumberOfNewCustomers(); ?> new
                                    </h3>
                                    <p>
                                        Customers
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-stalker"></i>
                                </div>
                                <a href="" class="small-box-footer">
                                    See stats  <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->
                        <div class="col-xs-4">
                            <!-- small box -->
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>
                                        <?php print $db->getNumberOfNewContacts(); ?> new
                                    </h3>
                                    <p>
                                        Contacts
                                    </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="./customerslist.php?customer_type=clients_1&customer_name=Contactos" class="small-box-footer">
                                    See all <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div><!-- ./col -->

                    </div><!-- /.row -->

                    <div class="row">
                        <div class="col-xs-12">
							<div class="box box-solid box-primary collapsed-box">
	                            <div class="box-header">
		                            <div class="box-tools pull-right">
                                        <button class="btn btn-primary btn-sm" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                        <button class="btn btn-primary btn-sm" data-widget="remove"><i class="fa fa-times"></i></button>
                                    </div>
	                                <i class="fa fa-warning"></i>
	                                <h3 class="box-title">First steps (click + to show)</h3>
	                            </div>
                                <div class="box-body" style="display: none;">
	                                <h2>Welcome to Creamy!</h2>
									<p>Creamy is a free, open source CRM for managing contacts & customers, lightweight, easy to use and customisable. Creamy is a full-fledged CRM framework that allows you to handle your contacts, customers and clients easily, manage the tasks associated with your business, and be notified of important events. This is a short guide to help you getting started with the main functionality of Creamy. Let's start!</p>
									<img src="img/sidebar.png" class="left-paragraph-image" width="200">

									<h3>Sidebar</h3>
									<p>The sidebar at the left will get you access to the different sections of Creamy. Let's have a look at what you can find there:</p>
									<h4 class="fa fa-bar-chart-o"> Home</h4>
									<p>The home screen is where we are right now. It's the main page of Creamy. Here you can find some statistics about the progress in the number of customers, contacts and clients, along with a quick access to the messaging system and this tutorial. Any module or plugin can also install some views here for you to see if they want to allow you to access some functionality or give you a quick overview of the data they handle.</p>
									<h4 class="fa fa-users"> Contacts and Customers</h4>
									<p>These sections contains all the contacts, customers and clients registered in your CRM, grouped by their type. This is the heart of Creamy. If this is the first time you (or someone in your company) is using Creamy, these sections will be empty, so one of the very first things you should do in order to enjoy Creamy is go to these sections and start filling in customers and contacts to feed your database!</p>
									<h4 class="fa fa-envelope"> Messages</h4>
									<p>In the messages section you can access a messaging system for the users of the CRM. This is an inner communication tool to give you a quick way of sending messages, questions and meeting appointments to other members of your company or business.</p>
									<h4 class="fa fa-exclamation"> Notifications</h4>
									<p>This section will give you a timeline with information about all the important events that happened today or occurred during the past week, warning you of anything that's worth your attention: new customers, calendar events, and issues amongst others. Here, you will be able to react with a proper action to most notifications.</p>
									<h4 class="fa fa-"
									<h3>Top bar</h3>
									<img src="img/topbar.png" class="right-paragraph-image" width="200">
									<p>The top bar will give you quick access to your messages, notifications and tasks. Each icon has a badge with a number of unread or unattended elements to help you get a quick overview of things that would require your attention.</p>
									<ul style="float: left;">
									<li>The messages icon shows you your unread messages, and clicking on it will show you them as a list. Select any of the messages to read it directly.</li>
									<li>The notifications icon shows you your notifications for today, and clicking on it will show you them as a list.</li>
									<li>The tasks icon shows you your unfinished tasks, and clicking on it will show you them in a list.</li>
									<li>The user icon at the right, close to your name, will open a menu where you will be able to access or modify your user data, change your password or logout.</li>
									</ul>
									<p>kdjhksjdhfgksjdhfgkjh</p>
	                            </div>
	                        </div>

                        </div>
                    </div>
                    
                    <!-- Main row -->
                    <div class="row">
                        <!-- Left col -->
                        <section class="col-lg-7 connectedSortable"> 
	                    	<!-- Gráfica de clientes -->   
	                        <div class="box box-success">
	                            <div class="box-header">
	                                <i class="fa fa-bar-chart-o"></i>
	                                <h3 class="box-title">Customer statistics</h3>
	                            </div>
                                <div class="box-body" id="graph-box">
                                    <div class="chart" id="revenue-chart" style="position: relative; height: 375px;"></div>
	                            </div>
	                        </div>
                        </section><!-- /.Left col -->
                        
                        <!-- right col (We are only adding the ID to make the widgets sortable)-->
                        <section class="col-lg-5 connectedSortable"> 

                            <!-- quick email widget -->
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-envelope"></i>
                                    <h3 class="box-title">Messaging System</h3>
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
                                            <textarea class="textarea" placeholder="Message" id="message" name="message" style="width: 100%; height: 125px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                                        </div>
                                        <input type="hidden" name="fromuserid" id="fromuserid" value="<?php print $_SESSION["userid"] ?>">
                                        <div id="messagesendingresult" name="messagesendingresult"></div>
								</div>
                                <div class="box-footer clearfix">
                                    <button type="submit" class="pull-right btn btn-default" id="sendEmail">Send <i class="fa fa-send"></i></button>
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
		<script src="js/sendmessageform.js" type="text/javascript"></script>

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
        labels: ['Seguros', 'Servicios'],
        lineColors: ['#20c1ed', '#18a55d'],
        hideHover: 'auto'
    });
        </script>

    </body>
</html>
