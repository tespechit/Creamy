<?php
require_once('LanguageHandler.php');
if (!isset($lh)) { $lh = \creamy\LanguageHandler::getInstance(); }

?>
<!-- CLIENT CREATION MODAL -->
	<!-- validation -->
	<script src="js/jquery.validate.min.js" type="text/javascript"></script>
	<!-- InputMask -->
    <script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
    <script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
    <script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>

	<script>
		$(document).ready(function() {
	
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
							if (data == 'success') {
								$("#createcustomerresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("user_successfully_created"); ?>');
								$("#createcustomerresult").fadeIn(); //show confirmation message
								$('#newclientform')[0].reset(); // reset form (except for hidden fields).
							} else {
								$("#createcustomerresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("error_creating_user"); ?>: '+ data);
								$("#createcustomerresult").fadeIn(); //show confirmation message
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
			var customerType = $(this).attr('href');
			$("#new-customer-header-text").html('<i class="fa fa-user"></i> <?php $lh->translateText("create_new"); ?></h4>');
			$("#customer_type").val(customerType);
		});
		
		/**
		 * Reload page when exiting creation of users.
		 */
		$("#createCustomerCancelButton").click(function(e) { location.reload(); });
	});
	
	
	</script>
	
	<!-- Modal form -->
    <div class="modal fade" id="create-client-dialog-modal" name="create-client-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="new-customer-header-text"><i class="fa fa-user"></i> <?php print strtoupper($lh->translationFor("new")); ?> </h4>
                </div>
                <form action="" method="post" name="newclientform" id="newclientform">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input name="name" id="name" type="text" class="form-control" placeholder="<?php print $lh->translationFor("name")." (".$lh->translationFor("mandatory"); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-medkit"></i></span>
                                <input name="productType" id="nproductTypeame" type="text" class="form-control" placeholder="<?php $lh->translateText("customer_or_service_type"); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input name="id_number" id="id_number" type="text" class="form-control" placeholder="<?php $lh->translateText("id_number"); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input name="email" id="email" type="text" class="form-control" placeholder="<?php $lh->translateText("email"); ?>">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input name="phone" id="phone" type="text" class="form-control" placeholder="<?php $lh->translateText("home_phone"); ?>">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
                                <input name="mobile" id="mobile" type="text" class="form-control" placeholder="<?php $lh->translateText("mobile_phone"); ?>">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input name="address" id="address" type="text" class="form-control" placeholder="<?php $lh->translateText("address"); ?>">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="row">
							<div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="city" id="city" type="text" class="form-control" placeholder="<?php $lh->translateText("city"); ?>">
	                            </div>
	                        </div><!-- /.col-lg-6 -->
	                        <div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="state" id="state" type="text" class="form-control" placeholder="<?php $lh->translateText("estate"); ?>">
	                            </div>                        
	                        </div><!-- /.col-lg-6 -->
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
							<div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="zipcode" id="zipcode" type="text" class="form-control" placeholder="<?php $lh->translateText("zip_code"); ?>">
	                            </div>
	                        </div><!-- /.col-lg-6 -->
	                        <div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="country" id="country" type="text" class="form-control" placeholder="<?php $lh->translateText("country"); ?>">
	                            </div>                        
	                        </div><!-- /.col-lg-6 -->
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Estado civil</label>
                            <select class="form-control" id="maritalstatus" name="maritalstatus">
                                <option value="0"><?php $lh->translateText("choose_an_option"); ?></option>
                                <option value="1"><?php $lh->translateText("single"); ?></option>
                                <option value="2"><?php $lh->translateText("married"); ?></option>
                                <option value="3"><?php $lh->translateText("divorced"); ?></option>
                                <option value="4"><?php $lh->translateText("separated"); ?></option>
                                <option value="5"><?php $lh->translateText("widow"); ?></option>
                            </select>
                        </div>
						<div class="form-group">
                            <label>Sexo</label>
                            <select class="form-control" id="gender" name="gender">
                                <option value="-1"><?php $lh->translateText("choose_an_option"); ?></option>
                                <option value="0"><?php $lh->translateText("female"); ?></option>
                                <option value="1"><?php $lh->translateText("male"); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php $lh->translateText("birthdate"); ?>:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input name="birthdate" id="birthdate" type="text" class="form-control" value="" data-inputmask="'alias': 'dd/mm/yyyy'" data-mask/>
                            </div><!-- /.input group -->
                        </div><!-- /.form group -->                        
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input name="donotsendemail" id="donotsendemail" type="checkbox"/> <?php $lh->translateText("do_not_send_email"); ?>
                                </label>
                            </div>
                        </div>
						<input type="hidden" id="customer_type" name="customer_type" value="">
						<div id="createcustomerresult" name="createcustomerresult"></div>
                    </div>
                    <div class="modal-footer clearfix">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="createCustomerCancelButton"><i class="fa fa-times"></i> <?php $lh->translateText("exit"); ?></button>
                        <button type="submit" class="btn btn-primary pull-left" id="createCustomerOkButton"><i class="fa fa-check-circle"></i> <?php $lh->translateText("create"); ?></button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    

<!-- END CHANGE PASSWORD MODAL -->
