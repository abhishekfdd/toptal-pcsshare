<?php
	if ( isset($_POST['send']) ) {
		pcs_save_private_message();
		$message = 'Message sent!';
	} else {
		$message = '';
	}
?>

<?php if ( $message ): ?><div class="bg-success success"><?php echo $message; ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
	<div class="form-group">
		<label for="message-subject">Subject</label>
		<input type="text" name="subject" id="message-subject" class="form-control">
	</div>
	<input type="hidden" name="send-to-input" value="<?php echo get_query_var('author_name'); ?>">
	<input type="hidden" name="send_to_usernames" value>
	<div class="form-group">
		<label for="message-content">Content</label>
		<textarea name="content" id="message-content" class="form-control"></textarea>
	</div>
	<div class="form-group">
		<input type="submit" name="send" value="Send Message" class="btn btn-primary">
	</div>
	<?php wp_nonce_field( 'messages_send_message' ); ?>
</form>
