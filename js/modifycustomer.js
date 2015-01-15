
/**
 * userforms.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */ 
$(document).ready(function() {
	
	//Datemask dd/mm/yyyy
    $("#birthdate").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});

	/** 
	 * Modifies a customer
 	 */
	$("#modifycustomerform").validate({
		submitHandler: function() {
			//submit the form
				$("#resultmessage").html();
				$("#resultmessage").fadeOut();
				$.post("./php/ModifyCustomer.php", //post
				$("#modifycustomerform").serialize(), 
					function(data){
						//if message is sent
						if (data == 'success') {
							$("#modifycustomerresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> Los datos se modificaron correctamente.');
							$("#modifycustomerresult").fadeIn(); //show confirmation message
	
						} else {
							$("#modifycustomerresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error modificando los datos: '+ data);
							$("#modifycustomerresult").fadeIn(); //show confirmation message
						}
						//
					});
			return false; //don't let the form refresh the page...
		}					
	});
	
	/**
	 * Deletes a customer
	 */
	 $("#modifyCustomerDeleteButton").click(function (e) {
		var r = confirm("¿Estás seguro que deseas eliminar al usuario? Esta acción no puede deshacerse");
		e.preventDefault();
		if (r == true) {
			var customerid = $(this).attr('href');
			$.post("./php/DeleteCustomer.php", $("#modifycustomerform").serialize() ,function(data){
				if (data == "success") { 
					alert("Cliente borrado correctamente.");
					window.location = "index.php";
				}
				else { alert ("Hubo un error borrando al cliente: "+data); }
			});
		}
	 });
	 
});