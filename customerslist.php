<?php
	require_once('./php/CRMDefaults.php');
	require_once "./php/DbHandler.php";
    $db = new DbHandler();
    include "./php/Session.php";
    
    // get the type of customers.
    $customerType = NULL;
    $customerName = NULL;
    if (isset($_GET["customer_type"])) {
	    $customerType = $_GET["customer_type"];
	    if (isset($_GET["customer_name"])) $customerName = $_GET["customer_name"];
		else $customerName = $db->getNameForCustomerType($customerType);
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Creamy - <?php print $customerName; ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- DATA TABLES -->
        <link href="./css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="./css/AdminLTE.css" rel="stylesheet" type="text/css" />

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
			<?php print $db->getSidebar($_SESSION["userid"], $_SESSION["username"], $_SESSION["userrole"], $_SESSION["avatar"]); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php print $customerName; ?>
                        <small>Lista de entradas</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-users"></i> Inicio</a></li>
                        <li class="active"><?php print $customerName; ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
	                <!-- check permissions -->
	                <?php if (userHasBasicPermission($_SESSION["userrole"])) { ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title"><?php print $customerName; ?></h3>
                                </div><!-- /.box-header -->
                                <?php 
	                            if (userHasWritePermission($_SESSION["userrole"])) { ?>
								<div class="box-tools" style="padding-left: 1%;">
                                   <a id="create-customer-trigger-button" href="<?php print $customerType; ?>" class="btn btn-success" data-toggle="modal" data-target="#create-client-dialog-modal">Añadir a <?php print(strtolower($customerName)); ?></a>
                                </div>
								<?php } ?>
                                <div class="box-body table-responsive">
									<?php print $db->getAllCustomersOfTypeAsTable($customerType); ?>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>
                    <!-- user not authorized -->
					<?php } else { print $db->getUnauthotizedAccessMessage(); } ?>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- DATA TABES SCRIPT -->
        <script src="./js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="./js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="./js/AdminLTE/app.js" type="text/javascript"></script>
        <!-- Modal Dialogs -->
        <!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<?php include_once "./php/CustomerDialogs.php" ?>

        <!-- page script -->
        <script type="text/javascript">
            $(function() {
                $("#contacts").dataTable();
            });
        </script>

    </body>
</html>
