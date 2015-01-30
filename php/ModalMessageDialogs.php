<!-- COMPOSE MESSAGE MODAL -->

<!-- Message forms -->
<script src="js/sendmessageform.js" type="text/javascript"></script>

<div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Nuevo Mensaje</h4>
            </div>
            <form action="#" method="post" id="send-message-form" name="send-message-form">
                <div class="modal-body">
                    <div class="form-group">
						<?php print $db->generateSendToUserSelect($_SESSION["userid"]); ?>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject"/>
                    </div>
                    <div class="form-group">
                        <textarea name="message" id="message" class="form-control" placeholder="Message" style="height: 120px;"></textarea>
                    </div>
                    <!--
                    <div class="form-group">
                        <div class="btn btn-success btn-file">
                            <i class="fa fa-paperclip"></i> Attachment
                            <input type="file" name="attachment"/>
                        </div>
                        <p class="help-block">Max. 32MB</p>
                    </div>
					-->
                </div>
                <input type="hidden" id="fromuserid" name="fromuserid" value="<?php print $_SESSION["userid"]; ?>">
                <div id="messagesendingresult" name="messagesendingresult">
                </div>
                <div class="modal-footer clearfix">

                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Cancelar</button>

                    <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-envelope"></i> Enviar Mensaje</button>
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
