<?php pcs_comments_show_delete_modal(); ?>
<?php pcs_comments_show_update_modal(); ?>

<?php $comments = pcs_get_comments_by_user( bp_displayed_user_id() ); ?>
<?php if ( count( $comments ) > 0 ): ?>
<?php foreach ($comments as $comment): ?>
<article class="post-excerpt comment" data-link="<?php echo get_permalink( $comment->comment_post_ID ); ?>" id="comment-<?php echo $comment->comment_ID; ?>">
  <h2 class="post-excerpt-title"><?php echo get_the_title( $comment->comment_post_ID ); ?></h2>
  <footer class="post-excerpt-footer">
    <div id="div-comment-<?php echo $comment->comment_ID; ?>"><?php comment_text( $comment->comment_ID ); ?></div>
	<?php if ( pcs_current_user_can_delete_comment( $comment ) ) : ?>
	  <span class="pull-left"><button class="btn btn-danger btn-delete-comment" data-comment-id="<?php echo $comment->comment_ID; ?>" data-wp-nonce="<?php echo wp_create_nonce( 'pcs-delete-comment' ); ?>" data-toggle="modal" data-target=".delete-comment-modal"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></span>
      <span class="pull-left"><?php pcs_comments_show_update_button(); ?></span>
      <div class="clearfix"></div>
    <?php endif; ?>
  </footer>
</article>
<?php endforeach; else : ?>
<p>There are no comments by this user. :(</p>
<?php endif; ?>
