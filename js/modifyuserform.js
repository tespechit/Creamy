
/**
 * userforms.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */ 
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
							<b>¡Éxito!</b> Los datos del usuario se modificaron correctamente.');
							$("#resultmessage").fadeIn(); //show confirmation message
						} else {
							$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i>\
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
							<b>¡Vaya!</b> Parece que hubo un error modificando los datos de usuario: '+ data);
							$("#resultmessage").fadeIn(); //show confirmation message
						}
				    }
				});
			return false; //don't let the form refresh the page...
		}					
	});
	 
});