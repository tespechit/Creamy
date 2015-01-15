<!-- CHANGE PASSWORD MODAL -->
	<script src="js/jquery.validate.min.js" type="text/javascript"></script>
	<script src="js/messages_es.min.js" type="text/javascript"></script>
	
	<script>
		$(document).ready(function() {
		/**
		 * Changes user password
		 */
		 $("#passwordform").validate({
		 	rules: {
				userid: "required",
				old_password: "required",
				new_password_1: "required",
			    new_password_2: {
			      minlength: 8,
			      equalTo: "#new_password_1"
			    }
	   		},
			submitHandler: function() {
				//submit the form
					$("#changepasswordresult").html();
					$("#changepasswordresult").fadeOut();
					$.post("./php/ChangePassword.php", //post
					$("#passwordform").serialize(), 
						function(data){
							//if message is sent
							if (data == 'success') {
								$("#changepasswordresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> La contraseña ha sido modificada correctamente.');
								$("#changepasswordresult").fadeIn(); //show confirmation message
								$("#changepassCancelButton").html("<i class=\"fa fa-check-circle\"></i> Salir");
								$("#changepassOkButton").fadeOut();
							} else {
								$("#changepasswordresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error modificando la contraseña: '+ data);
								$("#changepasswordresult").fadeIn(); //show confirmation message
							}
							//
						});
				return false; //don't let the form refresh the page...
			}					
		});
	});
	</script>
    <div class="modal fade" id="change-password-dialog-modal" name="change-password-dialog-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-lock"></i> Cambia tu contraseña</h4>
                </div>
                <form action="" method="post" name="passwordform" id="passwordform">
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input name="old_password" id="old_password" type="password" class="form-control" placeholder="Introduce tu antigua contraseña">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input name="new_password_1" id="new_password_1" type="password" class="form-control" placeholder="Introduce la nueva contraseña que quieres">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input name="new_password_2" id="new_password_2" type="password" class="form-control" placeholder="Vuelve a introducir la nueva contraseña">
                            </div>
                        </div>
						<input type="hidden" id="userid" name="userid" value="<?php print $_SESSION["userid"]; ?>">
						<div id="changepasswordresult" name="changepasswordresult"></div>
                    </div>
                    <div class="modal-footer clearfix">
                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="changepassCancelButton"><i class="fa fa-times"></i> Cancelar</button>
                        <button type="submit" class="btn btn-primary pull-left" id="changepassOkButton"><i class="fa fa-check-circle"></i> Cambiar contraseña</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    

<!-- END CHANGE PASSWORD MODAL -->


