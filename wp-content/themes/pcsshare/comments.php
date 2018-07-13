<section class="post-comments" id="post-comments">
	<button class="btn btn-primary enter-comment">Enter Comment</button>
	<?php
		wp_list_comments( array(
			'style'	 => 'ul',
			'type'	 => 'comment',
			'walker' => new PCS_Walker_Comment
		) );
	?>
	<?php

		if ( ! isset( $_GET['replytocom'] ) ) {

			comment_form([
				'label_submit' => 'Post Comment',
				'class_submit' => 'btn',
				'comment_notes_before' => '<p class="comment-notes">' .
					__( 'Create an account to have a username associated with this comment. Comments created without signing in will be entered anonymously and need to be approved before they are created.' ) . ( $req ? $required_text : '' ) .
					'</p>',
				'fields' => []
			]);

		}
	?>
</section>
