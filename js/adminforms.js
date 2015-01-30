
/**
 * adminforms.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */
 $(document).ready(function() {
 
 
 	/** 
	 * Creates a new user.
 	 */
	$("#createuser").validate({
		rules: {
			name: "required",
			password1: "required",
		    password2: {
		      minlength: 8,
		      equalTo: "#password1"
		    }
   		},
		submitHandler: function(e) {
			//submit the form
				$("#resultmessage").html();
				$("#resultmessage").hide();
				var formData = new FormData(e);

				$.ajax({
				  url: "./php/CreateUser.php",
				  data: formData,
				  processData: false,
				  contentType: false,
				  type: 'POST',
				  success: function(data) {
						if (data == 'success') {
							$("#resultmessage").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check">\
							</i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
							<b>¡Éxito!</b> El usuario fue creado correctamente.');
							$("#resultmessage").fadeIn(); //show confirmation message
						} else {
							$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i>\
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>\
							<b>¡Vaya!</b> Parece que hubo un error creando el usuario: '+ data);
							$("#resultmessage").fadeIn(); //show confirmation message
						}
				    }
				});
			return false; //don't let the form refresh the page...
		}					
	});
	
	/**
	 * Delete user.
	 */
	 $(".delete-action").click(function(e) {
		var r = confirm("¿Estás seguro que deseas eliminar al usuario? Esta acción no puede deshacerse");
		e.preventDefault();
		if (r == true) {
			var user_id = $(this).attr('href');
			$.post("./php/DeleteUser.php", { userid: user_id } ,function(data){
				if (data == "success") { location.reload(); }
				else { alert ("Hubo un error borrando al usuario. Por favor, inténtelo más tarde."); }
			});
		}
	 });

	 /**
	  * Edit user details
	  */
	 $(".edit-action").click(function(e) {
		e.preventDefault();
		var url = './edituser.php';
		var form = $('<form action="' + url + '" method="post"><input type="hidden" name="userid" value="' + $(this).attr('href') + '" /></form>');
		//$('body').append(form);  // This line is not necessary
		$(form).submit();
	 });

	 /**
	  * Deactivate user
	  */
	 $(".deactivate-user-action").click(function(e) {
		e.preventDefault();
		var user_id = $(this).attr('href');
		$.post("./php/SetUserStatus.php", { "userid": user_id, "status": 0 } ,function(data){
			if (data == "success") { location.reload(); }
			else { alert ("Hubo un error desactivando al usuario. Por favor, inténtelo más tarde."); }
		});
	 });

	 /**
	  * Activate user
	  */
	 $(".activate-user-action").click(function(e) {
		e.preventDefault();
		var user_id = $(this).attr('href');
		$.post("./php/SetUserStatus.php", { "userid": user_id, "status": 1 } ,function(data){
			if (data == "success") { location.reload(); }
			else { alert ("Hubo un error activando al usuario. Por favor, inténtelo más tarde."); }
		});
	 });

	 /**
	  * Show change user password.
	  */
	 $(".change-password-action").click(function(e) {
		e.preventDefault();
		var usertochangepasswordid = $(this).attr('href');
		$("#usertochangepasswordid").val(usertochangepasswordid);
		$("change-password-admin-ok-button").show();
		$("#changepasswordadminresult").html();
		$("#changepasswordadminresult").hide();

		$("#change-password-admin-dialog-modal").modal('show');
	 });

	 /**
	  * Change user password from admin.
	  */
	 $("#adminpasswordform").validate({
		rules: {
			new_password1: "required",
		    new_password2: {
			  required: true,
		      minlength: 8,
		      equalTo: "#password1"
		    }
   		},
		submitHandler: function(e) {
			$.post("./php/ChangePasswordAdmin.php", //post
			$("#adminpasswordform").serialize(), 
				function(data){
					//if message is sent
					if (data == 'success') {
						$("#changepasswordadminresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> Contraseña cambiada correctamente.');
						$("#changepasswordadminresult").fadeIn(); //show confirmation message
						$("change-password-admin-ok-button").fadeOut();
						$("#adminpasswordform")[0].reset();

					} else {
						$("#changepasswordadminresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error cambiando la contraseña: '+ data);
						$("#changepasswordadminresult").fadeIn(); //show confirmation message
					}
					//
				});
		}
	 });

});

