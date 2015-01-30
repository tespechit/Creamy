
/**
 * userforms.js
 * Javascript validation and submission of forms.
 * Author Ignacio Nieto Carvajal
 * requires: jquery, jquery-validate
 */ 
$(document).ready(function() {
	/** 
	 * Creates a new user.
 	 */
	$("#createtask").validate({
		rules: {
			taskDescription: "required",
		},
		submitHandler: function() {
			//submit the form
				$("#resultmessage").html();
				$("#resultmessage").fadeOut();
				$.post("./php/CreateTask.php", //post
				$("#createtask").serialize(), 
					function(data){
						//if message is sent
						if (data == 'success') {
							location.reload();
						} else {
							$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error creando la nueva tarea: '+ data);
							$("#resultmessage").fadeIn(); //show confirmation message
						}
						//
					});
			return false; //don't let the form refresh the page...
		}					
	});

	/**
	 * Delete a task
	 */
	 $(".delete-task-action").click(function(e) {
		var r = confirm("¿Estás seguro que deseas eliminar la tarea? Esta acción no puede deshacerse");
		e.preventDefault();
		if (r == true) {
			var taskid = $(this).attr('href');
			$.post("./php/DeleteTask.php", { "taskid": taskid } ,function(data){
				if (data == "success") { location.reload(); }
				else { alert ("Hubo un error borrando la tarea. Por favor, inténtelo más tarde."); }
			});
		}
	 });
	 
	/**
	 * Show the edit task dialog, filling the edit fields properly.
	 */
	$(".edit-task-action").click(function(e) {
		// Set ID of the task to edit
        var ele = $(this).parents("li").first();
		var task_id = ele[0].id; // task ID is contained in the ID element of the li object.
		$('#edit-task-taskid').val(task_id);
		
		// set the previous description of task.
		var current_text = $('.text', ele);
		$('#edit-task-description').val(current_text.text());
	});

	/**
	 * Edit the description of a task
	 */
	$("#edit-task-form").validate({
		submitHandler: function() {
			//submit the form
				$("#resultmessage").html();
				$("#resultmessage").fadeOut();
				$.post("./php/ModifyTask.php", //post
				$("#edit-task-form").serialize(), 
					function(data){
						//if message is sent
						if (data == 'success') {
							location.reload();
						} else {
							$("#resultmessage").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error creando la nueva tarea: '+ data);
							$("#resultmessage").fadeIn(); //show confirmation message
						}
						//
					});
			return false; //don't let the form refresh the page...
		}					
	});


	/**
	 * React to checking and unchecking of boxes -- Mark tasks as completed.
	 */
    $('input', this).on('ifChecked', function(event) {
        var ele = $(this).parents("li").first();
		// task ID is contained in the ID element of the li object.
		var task_id = ele[0].id;
		
		// clear current result field
		$("#changetaskresult").html();
		$("#changetaskresult").fadeOut();
		
		// mark item as "done" and call ModifyTask. 
        ele.toggleClass("done");
		$.post("./php/CompleteTask.php", {"complete-task-taskid": task_id, "complete-task-progress": "100" }, 
		function(data){
			if (data == "success") { location.reload(); }
			else {
				$("#changetaskresult").html(data);
				$("#changetaskresult").fadeIn();
			}
		});
    });

    $('input', this).on('ifUnchecked', function(event) {
        var ele = $(this).parents("li").first();
        ele.toggleClass("done");
    });
	
});