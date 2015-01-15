
/**
 * sendmessageform.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */ 
$(document).ready(function() {

	/** 
	 * Modifies a customer
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
	        touserid: "Por favor, elija un usuario al que enviarle el mensaje",
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
							$("#messagesendingresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> Mensaje enviado correctamente.');
							$("#messagesendingresult").fadeIn(); //show confirmation message
							$("#send-message-form")[0].reset();
	
						} else {
							$("#messagesendingresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error enviando el mensaje: '+ data);
							$("#messagesendingresult").fadeIn(); //show confirmation message
						}
						//
					});
			return false; //don't let the form refresh the page...
		}					
	});
	 
});