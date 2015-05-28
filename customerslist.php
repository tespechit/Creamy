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
	    <!-- iCheck for checkboxes and radio inputs -->
	    <link href="css/iCheck/minimal/blue.css" rel="stylesheet" type="text/css" />
        <!-- DATA TABLES -->
        <link href="css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- Creamy style -->
        <link href="css/creamycrm.css" rel="stylesheet" type="text/css" />
        <?php print $ui->creamyThemeCSS(); ?>

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
	    <!-- iCheck -->
	    <script src="js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
        <!-- JQuery Validate -->
        <script src ="js/jquery.validate.min.js" type="text/javascript"></script>
		<!-- Input mask for date textfields -->
		<script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
		<script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
		<script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
        <!-- Creamy App -->
        <script src="js/app.min.js" type="text/javascript"></script>
    </head>
    <?php print $ui->creamyBody(); ?>
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
                            <div class="box box-default">
                                <div class="box-header">
                                    <h3 class="box-title"><?php print $customerName; ?></h3>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive">
								<?php } ?>
									<?php 
										print $ui->getEmptyCustomersList($customerType);
									?>
                                </div><!-- /.box-body -->
                                <div class="box-footer">
	                                <?php 
										if ($user->userHasWritePermission()) { ?>
										<a id="create-customer-trigger-button" customer_type="<?php print $customerType; ?>" class="btn btn-primary" data-toggle="modal" data-target="#create-client-dialog-modal">
											<i class="fa fa-plus"></i>&nbsp; <?php print($lh->translationFor("add_to")." ".strtolower($customerName)); ?></a>
										<?php print $ui->getCustomerListFooter($customerType); 	
									?>
                                </div>
                            </div><!-- /.box -->
                        </div>
                    </div>
                    <!-- user not authorized -->
					<?php } else { print $ui->getUnauthotizedAccessMessage(); } ?>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
            <?php print $ui->creamyFooter(); ?>
        </div><!-- ./wrapper -->

        <!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>
		<?php 
			// Create new customer form as a modal dialog
			$fields = $ui->customerFieldsForForm(null, null, null);
			// buttons
			$okButton = $ui->buttonWithLink("createCustomerOkButton", "", $lh->translationFor("create"), "submit", "times", CRM_UI_STYLE_DEFAULT, "pull-right");
			$koButton = $ui->modalDismissButton("createCustomerCancelButton", $lh->translationFor("cancel"), "left", true);
			$buttons = $okButton.$koButton;
			
			// form
			$form = $ui->modalFormStructure("create-client-dialog-modal", "newclientform", $lh->translationFor("create_new"), null, $fields, $buttons, "user", "createcustomerresult");
			print $form;
		?>

        <!-- page script -->
        <script type="text/javascript">
			"use strict";
	        var clientCreated = false;

	        // load datatable of customer.
            $(document).ready(function() {
			    // uncheck individual customer
				$('input[type=checkbox]').on("ifChecked", function(e) {
					if (e.currentTarget.value != 'on') selectedCustomers.push(e.currentTarget.value);
					alert("customers: "+selectedCustomers);
				});

                $("#contacts").dataTable({
	                "bProcessing": true,
	                "bPaginate": true,
					"bServerSide": true,
					"sAjaxSource": "./php/CustomerListJSON.php",
					"aoColumnDefs": [ { "bSortable": false, "bVisible": true, "aTargets": [ -1 ] } ],
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
                
                //Datemask dd/mm/yyyy
			    $("#birthdate").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
			
				/**
				 * Create a new customer/contact
				 */
				 $("#newclientform").validate({
				 	rules: {
						name: "required",
			   		},
					submitHandler: function() {
						//submit the form
							$("#createcustomerresult").html();
							$("#createcustomerresult").hide();
							$.post("./php/CreateCustomer.php", //post
							$("#newclientform").serialize(), 
								function(data){
									//if message is sent
									if (data == '<?php print CRM_DEFAULT_SUCCESS_RESPONSE; ?>') {
									<?php 
									$errorMsg = $ui->dismissableAlertWithMessage($lh->translationFor("user_successfully_created"), true, false);
									print $ui->fadingInMessageJS($errorMsg, "createcustomerresult"); 
									?>
									$('#newclientform')[0].reset(); // reset form (except for hidden fields).
									clientCreated = true;
									} else {
									<?php 
									$errorMsg = $ui->dismissableAlertWithMessage($lh->translationFor("error_creating_user"), false, true);
									print $ui->fadingInMessageJS($errorMsg, "createcustomerresult"); 
									?>
									}
									//
								});
						return false; //don't let the form refresh the page...
					}					
				});
				
				/**
				 * Set the elements of the newly created customers.
				 */
				$("#create-customer-trigger-button").click(function (e) {
					e.preventDefault();
					clientCreated = false;
					var customerType = $(this).attr('customer_type');
					$("#new-customer-header-text").html('<i class="fa fa-user"></i> <?php $lh->translateText("create_new"); ?></h4>');
					$("#customer_type").val(customerType);
				});
				
				/**
				 * Reload page when exiting users creation dialog.
				 */
				$("#createCustomerCancelButton").click(function(e) { location.reload(); });
				$('#create-client-dialog-modal').on('hidden.bs.modal', function () { if (clientCreated) { location.reload(); } });

			});
			
            // function to delete a customer with the "delete" button.
            function deleteCustomer(customerId, customerType) {
				var r = confirm("¿Estás seguro? Esta acción no puede deshacerse");
				if (r == true) {
					$.post("./php/DeleteCustomer.php", { "customerid": customerId, "customer_type": customerType }, function(data){
						if (data == '<?php print CRM_DEFAULT_SUCCESS_RESPONSE; ?>') { location.reload(); }
						else { alert(data); }
					});
				}
            }
            // function to create an event associated with a customer.
            function createEventForCustomer(customerId, customerType) {
				$.post("./php/CreateEvent.php", { "customerid": customerId, "customer_type": customerType }, function(data){
					if (data != '0') { window.location.href = "events.php" }
					else { alert(data); }
				});
            }
            // function to change a customer type
            function changeCustomerType(customerId, oldCustomerType, newCustomerType) {
				$.post("./php/ChangeCustomerType.php", 
				{ "customerid": customerId, "old_customer_type": oldCustomerType, "new_customer_type": newCustomerType }, 
				function(data){
					if (data == '<?php print CRM_DEFAULT_SUCCESS_RESPONSE; ?>') { location.reload(); }
					else { alert(data); }
				});
            }
        </script>

    </body>
</html>
