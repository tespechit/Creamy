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
	
	require_once ('./php/CRMDefaults.php');
	require_once ('./php/UIHandler.php');
	require_once ('./php/LanguageHandler.php');
    include ('./php/Session.php');

    $ui = \creamy\UIHandler::getInstance();
	$lh = \creamy\LanguageHandler::getInstance();
	$user = \creamy\CreamyUser::currentUser();
    
    // get the type of customers.
    $customerType = NULL;
    $customerName = NULL;
    
    if (isset($_GET["customer_type"])) {
	    $customerType = $_GET["customer_type"];
	    if (isset($_GET["customer_name"])) $customerName = $_GET["customer_name"];
		else { 
		    // if we have not been provided with the "human readable" name, we need to find it in the database.
		    require_once('./php/DbHandler.php'); 
		    $db = new \creamy\DbHandler();
			$customerName = $db->getNameForCustomerType($customerType);
		}
    }
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
        <!-- DATA TABLES -->
        <link href="css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- Creamy style -->
        <link href="css/creamycrm.css" rel="stylesheet" type="text/css" />
        <link href="css/skins/skin-blue.min.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="js/html5shiv.js"></script>
          <script src="js/respond.min.js"></script>
        <![endif]-->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js" type="text/javascript"></script>
        <!-- DATA TABES SCRIPT -->
        <script src="./js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="./js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- Creamy App -->
        <script src="js/app.min.js" type="text/javascript"></script>
    </head>
    <body class="skin-blue">
        <div class="wrapper">
        <!-- header logo: style can be found in header.less -->
		<?php print $ui->creamyHeader($user); ?>
			<?php print $ui->getSidebar($user->getUserId(), $user->getUserName(), $user->getUserRole(), $user->getUserAvatar()); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php print $customerName; ?>
                        <small><?php $lh->translateText("customer_list"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-users"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li class="active"><?php print $customerName; ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
	                <!-- check permissions -->
	                <?php if ($user->userHasBasicPermission()) { ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title"><?php print $customerName; ?></h3>
                                </div><!-- /.box-header -->
                                <?php 
	                            if ($user->userHasWritePermission()) { ?>
								<div class="box-tools" style="padding-left: 1%;">
                                   <a id="create-customer-trigger-button" href="<?php print $customerType; ?>" class="btn btn-success" data-toggle="modal" data-target="#create-client-dialog-modal"><?php print($lh->translationFor("add_to")." ".strtolower($customerName)); ?></a>
                                </div>
								<?php } ?>
                                <div class="box-body table-responsive">
									<?php 
										print $ui->getEmptyCustomersList($customerType);
									?>
                                </div><!-- /.box-body -->
                                <div class="box-footer">
								<?php print $ui->getCustomerListFooter($customerType); ?>
                                </div>
                            </div><!-- /.box -->
                        </div>
                    </div>
                    <!-- user not authorized -->
					<?php } else { print $ui->getUnauthotizedAccessMessage(); } ?>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<?php include_once "./php/CustomerDialogs.php" ?>

        <!-- page script -->
        <script type="text/javascript">
	        // load datatable of customer.
            $(function() {
                $("#contacts").dataTable({
	                "bProcessing": true,
	                "bPaginate": true,
					"bServerSide": true,
					"sAjaxSource": "./php/CustomerListJSON.php",
					"fnServerParams": function (aoData) { // custom param: customer_type
			            aoData.push({
			                "name": "customer_type",
			                "value": "<?php echo $customerType; ?>"
			            })
		            },
					<?php
						$datatablesTranslationURL = $lh->urlForDatatablesTranslation();
						if (isset($datatablesTranslationURL)) { print '"oLanguage": { "sUrl": "'.$datatablesTranslationURL.'" },'."\n"; } 
					?>
                });     
			});
            // function to delete a customer with the "delete" button.
            function deleteCustomer(customerId, customerType) {
				var r = confirm("¿Estás seguro? Esta acción no puede deshacerse");
				if (r == true) {
					$.post("./php/DeleteCustomer.php", { "customerid": customerId, "customer_type": customerType }, function(data){
						if (data == "success") { location.reload(); }
						else { alert(data); }
					});
				}
            }
        </script>

    </body>
</html>
