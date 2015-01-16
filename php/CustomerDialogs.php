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
								$("#createcustomerresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> Usuario creado con éxito. Puede crear otro a continuación o salir.');
								$("#createcustomerresult").fadeIn(); //show confirmation message
								$('#newclientform')[0].reset(); // reset form (except for hidden fields).
							} else {
								$("#createcustomerresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error creando el usuario: '+ data);
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
			$("#new-customer-header-text").html('<i class="fa fa-user"></i> Crear nuevo</h4>');
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
                    <h4 class="modal-title" id="new-customer-header-text"><i class="fa fa-user"></i> Nuevo </h4>
                </div>
                <form action="" method="post" name="newclientform" id="newclientform">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input name="name" id="name" type="text" class="form-control" placeholder="Nombre (obligatorio)">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-medkit"></i></span>
                                <input name="productType" id="nproductTypeame" type="text" class="form-control" placeholder="Producto/Tipo de Contacto">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                <input name="id_number" id="id_number" type="text" class="form-control" placeholder="ID number">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input name="email" id="email" type="text" class="form-control" placeholder="Email">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input name="phone" id="phone" type="text" class="form-control" placeholder="Teléfono fijo">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
                                <input name="mobile" id="mobile" type="text" class="form-control" placeholder="Teléfono móvil">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input name="address" id="address" type="text" class="form-control" placeholder="Dirección">
                            </div>                  
                        </div>
                        <div class="form-group">
                            <div class="row">
							<div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="city" id="city" type="text" class="form-control" placeholder="Ciudad">
	                            </div>
	                        </div><!-- /.col-lg-6 -->
	                        <div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="state" id="state" type="text" class="form-control" placeholder="Provincia">
	                            </div>                        
	                        </div><!-- /.col-lg-6 -->
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
							<div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="zipcode" id="zipcode" type="text" class="form-control" placeholder="Código Postal">
	                            </div>
	                        </div><!-- /.col-lg-6 -->
	                        <div class="col-lg-6">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="country" id="country" type="text" class="form-control" placeholder="País">
	                            </div>                        
	                        </div><!-- /.col-lg-6 -->
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Estado civil</label>
                            <select class="form-control" id="maritalstatus" name="maritalstatus">
                                <option value="0">elige una opción</option>
                                <option value="1">soltero/a</option>
                                <option value="2">casado/a</option>
                                <option value="3">divorciado/a</option>
                                <option value="4">separado/a</option>
                                <option value="5">viudo/a</option>
                            </select>
                        </div>
						<div class="form-group">
                            <label>Sexo</label>
                            <select class="form-control" id="gender" name="gender">
                                <option value="-1">elige una opción</option>
                                <option value="0">mujer</option>
                                <option value="1">hombre/a</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de nacimiento:</label>
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
                                    <input name="donotsendemail" id="donotsendemail" type="checkbox"/>Esta persona desea que no se le envíen emails
                                </label>
                            </div>
                        </div>
						<input type="hidden" id="customer_type" name="customer_type" value="">
						<div id="createcustomerresult" name="createcustomerresult"></div>
                    </div>
                    <div class="modal-footer clearfix">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="createCustomerCancelButton"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary pull-left" id="createCustomerOkButton"><i class="fa fa-check-circle"></i> Crear</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    

<!-- END CHANGE PASSWORD MODAL -->
