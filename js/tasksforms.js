
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
			taskInitialProgress: {
			  required: true,
			  range: [0, 100]
			}
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
	 * Info from task.
	 */
	 $(".info-task-action").click(function(e) {
		var task_id = $(this).attr('href');
		e.preventDefault();
		$.post("./php/TaskInfo.php", {"taskid": task_id, "format": "task-general-info" }, 
			function(data){
				$("#task-info-content").html(data);
			});
	 });
	 
	/**
	 * Complete progress from task.
	 */
	 $(".complete-task-action").click(function(e) {
		var task_id = $(this).attr('href');
		$("#changetaskresult").hide();
		e.preventDefault();
		$.post("./php/TaskInfo.php", {"taskid": task_id, "format": "task-progress-info" }, 
			function(data){
				$("#task-progress-properties-content").html(data);
				$("#complete-task-taskid").val(task_id);
				$('#task-new-progress-slider').slider({
	                tooltip: "hide"
                }).on('slide', function(ev) {
	                var newValue = ev.value;
	                if (!newValue) newValue = 0;
	                $('#task-new-progress-slider').value = newValue;
	                $('#new-task-progress-label').html("Completado: ("+newValue+") ");
                });
			});
	 });
	 
	 /**
	  * Modify the completion status of a task.
	  */
	 $("#modify-task-form").submit(function(e) {
		//submit the form
		e.preventDefault();
		$.post("./php/ModifyTask.php", //post
		$("#modify-task-form").serialize(), 
			function(data){
				//if message is sent
				if (data == 'success') {
					$("#changetaskresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Éxito!</b> La tarea fue modificada correctamente.');
					$("#changetaskresult").fadeIn(); //show confirmation message
					$("#changetaskOkButton").fadeOut();
					$("#changetaskCancelButton").html('<i class="fa fa-check-circle"></i> Salir');
				$("#task-progress-properties-content").html('');
				} else {
					$("#changetaskresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b>¡Vaya!</b> Parece que hubo un error modificando la tarea: '+ data);
					$("#changetaskresult").fadeIn(); //show confirmation message
				}
			});
	});
	
	/**
	 * Reload the page if we exit a task modification.
	 */
	$("#changetaskCancelButton").click(function(e) {
		setTimeout(location.reload(), 0.5);
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

	
});