<?php
// language handler
require_once('LanguageHandler.php');
if (!isset($lh)) { $lh = \creamy\LanguageHandler::getInstance(); }

// UI handler
require_once('UIHandler.php');
if (!isset($ui)) { $ui = \creamy\UIHandler::getInstance(); }
?>

<!-- COMPOSE MESSAGE MODAL -->

<!-- Message forms -->
<script type="text/javascript">
	$(document).ready(function() {

	/** 
	 * Sends a message
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
	        touserid: "You must choose a user to send the message to",
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
							$("#messagesendingresult").html('<div class="alert alert-success alert-dismissable"><i class="fa fa-check"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("success"); ?></b> <?php $lh->translateText("message_successfully_sent"); ?>');
							$("#messagesendingresult").fadeIn(); //show confirmation message
							$("#send-message-form")[0].reset();
	
						} else {
							$("#messagesendingresult").html('<div class="alert alert-danger alert-dismissable"><i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><b><?php $lh->translateText("oups"); ?></b> <?php $lh->translateText("unable_send_message"); ?>: '+ data);
							$("#messagesendingresult").fadeIn(); //show confirmation message
						}
						//
					});
			return false; //don't let the form refresh the page...
		}					
	});
	 
});
</script>

<div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> <?php $lh->translateText("new_message"); ?></h4>
            </div>
            <form action="#" method="post" id="send-message-form" name="send-message-form">
                <div class="modal-body">
                    <div class="form-group">
						<?php print $ui->generateSendToUserSelect($_SESSION["userid"]); ?>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="subject" name="<?php $lh->translateText("subject"); ?>" placeholder="Subject"/>
                    </div>
                    <div class="form-group">
                        <textarea name="message" id="message" class="form-control" placeholder="<?php $lh->translateText("message"); ?>" style="height: 120px;"></textarea>
                    </div>
                </div>
                <input type="hidden" id="fromuserid" name="fromuserid" value="<?php print $_SESSION["userid"]; ?>">
                <div id="messagesendingresult" name="messagesendingresult">
                </div>
                <div class="modal-footer clearfix">

                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> <?php $lh->translateText("cancel"); ?></button>

                    <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-envelope"></i> <?php $lh->translateText("send"); ?></button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- END COMPOSE MESSAGE MODAL -->

<!-- SHOW MESSAGE MODAL -->

<script type="text/javascript">
	$(document).ready(function() {
		
		// activate message: fill fields.
		$(".show-message-link").click(function(e) {
			e.preventDefault();
			var messageid = $(this).attr('href');
			var parentTr = $(this).closest('tr');

			$.post("./php/ReadMessage.php", { "folder": folder, "messageid": messageid}, function(data) {
				// show message.
				$("#show-message-modal").html(data);
				$("#show-message-modal").modal('show');
				// mark message as read.
				$.post("./php/MarkMessagesAsRead.php", { "folder": folder, "messageids": [messageid] }, function(data) {
					parentTr.removeClass("unread");
				});
			});
		});
		
	});
</script>

<div class="modal fade" id="show-message-modal" tabindex="-1" role="dialog" aria-hidden="true">

</div><!-- /.modal -->
<!-- END SHOW MESSAGE MODAL -->
