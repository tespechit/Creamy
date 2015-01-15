
/**
 * messagesform.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */
 $(document).ready(function() {
 
 	var selectedMessages = [];
 
     "use strict";

	 // ------------- Favorites -------------------

    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });
    
    // check individual message
	$('input[type=checkbox]').on("ifUnchecked", function(e) {
		var index = selectedMessages.indexOf(e.currentTarget.value);
		if (index >= 0) selectedMessages.splice(index, 1);
	});
    
    // uncheck individual message
	$('input[type=checkbox]').on("ifChecked", function(e) {
		if (e.currentTarget.value != 'on') selectedMessages.push(e.currentTarget.value);
	});

    //When unchecking the checkbox
    $("#check-all").on('ifUnchecked', function(event) {
        //Uncheck all checkboxes
        $("input[type='checkbox']", ".mailbox").iCheck("uncheck");
    });
    //When checking the checkbox
    $("#check-all").on('ifChecked', function(event) {
        //Check all checkboxes
        $("input[type='checkbox']", ".mailbox").iCheck("check");
    });
    // de-star a starred video
    $(".fa-star, .glyphicon-star, .fa-star-o, .glyphicon-star-empty").click(function(e) {
        e.preventDefault();
        
        // e.currentTarget.id contiene el id del mensaje.
        //detect type
        var glyph = $(this).hasClass("glyphicon");
        var fa = $(this).hasClass("fa");
		var starredByStar = $(this).hasClass("fa-star");
		var starredByGlyph = $(this).hasClass("glyphicon-star");
		var favorite = 1;
		var selectedItem = this;
		
		if (starredByGlyph || starredByStar) { // unmark message as favorite
			favorite = 0;   
		} // else mark message as favorite
		
		$.post("./php/MarkMessagesAsFavorite.php", { "favorite": favorite, "messageids": [e.currentTarget.id], "folder": folder } ,function(data){
				if (data == "success") { 
					$("#messages-message-box").hide();
					// toggle visual change.
			        if (fa) {
			            $(selectedItem).toggleClass("fa-star");
			            $(selectedItem).toggleClass("fa-star-o");
			        }		
				    if (glyph) {
				        $(selectedItem).toggleClass("glyphicon-star");
				        $(selectedItem).toggleClass("glyphicon-star-empty");
				    }
				}
				else {
					$("#messages-message-box").hide();
					$("#messages-message").html("<div class=\"callout callout-danger\"><h4>Mensaje</h4><p>Error estableciendo favorito. Por favor, inténtelo de nuevo. Si el problema persiste, contacte con el administrador.</p></div>");
					$("#messages-message-box").fadeIn();
				}
			});
		
    });

    // mark messages as read.
    $("#messages-mark-as-favorite").click(function(e) {
	    if (selectedMessages.length > 0) {
			$.post("./php/MarkMessagesAsFavorite.php", { "messageids": selectedMessages, "folder": folder, "favorite": 1 } ,function(data){
				if (data == "success") { location.reload(); }
				else {
					$("#messages-message-box").hide();
					$("#messages-message").html("<div class=\"callout callout-danger\"><h4>Mensaje</h4><p>Error marcando mensajes como leidos. "+data+"</p></div>");
					$("#messages-message-box").fadeIn();
				}
			});
	    }
    });

    //Initialize WYSIHTML5 - text editor
    $("#email_message").wysihtml5();
    
	// ------------- Read / Unread -------------------
    
    // mark messages as read.
    $("#messages-mark-as-read").click(function(e) {
	    if (selectedMessages.length > 0) {
			$.post("./php/MarkMessagesAsRead.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
				if (data == "success") { location.reload(); }
				else {
					$("#messages-message-box").hide();
					$("#messages-message").html("<div class=\"callout callout-danger\"><h4>Mensaje</h4><p>Error marcando mensajes como leidos. "+data+"</p></div>");
					$("#messages-message-box").fadeIn();
				}
			});
	    }
    });
        
    // mark messages as read.
    $("#messages-mark-as-unread").click(function(e) {
	    if (selectedMessages.length > 0) {
			$.post("./php/MarkMessagesAsUnread.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
				if (data == "success") { location.reload(); }
				else {
					$("#messages-message-box").hide();
					$("#messages-message").html("<div class=\"callout callout-danger\"><h4>Mensaje</h4><p>Error marcando mensajes como no leidos. "+data+"</p></div>");
					$("#messages-message-box").fadeIn();
				}
			});
	    }
    });
    
    // -------------------- Junk and delete messages ----------------------
    
    // send to junk mail
    $("#messages-send-to-junk").click(function (e) {
   		e.preventDefault();
			$("#messages-message-box").hide();
   		$.post("./php/JunkMessages.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
   			reloadWithMessage("Enviados "+data+" de "+selectedMessages.length+" mensajes a la papelera.");
		});
    });
        
    // restore mail from junk
    $("#messages-restore-message").click(function (e) {
   		e.preventDefault();
   		$.post("./php/UnjunkMessages.php", { "messageids": selectedMessages } ,function(data){
   			reloadWithMessage("Recuperados "+data+" de "+selectedMessages.length+" mensajes de la papelera.");
		});
    });
    
    // delete messages.
    $("#messages-delete-permanently").click(function (e) {
	    if (selectedMessages.length < 1) return;
		var r = confirm("¿Estás seguro que deseas borrar permanentemente los mensajes? Esta acción no puede deshacerse.");
		e.preventDefault();
		if (r == true) {
			$.post("./php/DeleteMessages.php", { "messageids": selectedMessages, "folder": folder } ,function(data){
				if (data == "success") { location.reload(); }
				else { alert ("Hubo un error borrando los mensajes. Por favor, inténtelo más tarde."); }
			});
		}
    });
    
    function reloadWithMessage(message) {
	    var url = window.location.href;
		if (url.indexOf('?') > -1){
		   url += '&message='+message;
		} else{
		   url += '?message='+message;
		}
		window.location.href = url;
    }
    
});

