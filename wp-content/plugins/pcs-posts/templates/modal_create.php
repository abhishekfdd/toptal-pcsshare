<div class="modal fade new-post-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="enter-post-form" data-parsley-validate="">
          <input type="hidden" name="action" value="<?php echo pcs_posts_get_action_name_create(); ?>">
          <input type="hidden" name="_wpnonce" id="post-nonce" value="<?php echo pcs_posts_get_create_nonce(); ?>">
          <input type="hidden" name="author" id="post-author" value="<?php echo pcs_get_current_user_id(); ?>">
          <input type="hidden" name="category" id="post-category" value="<?php echo pcs_get_current_category_id(); ?>">
          <div class="bg-danger bs-callout bs-callout-warning hidden">
            <p>Please make sure to fill all post fields.</p>
          </div>
          <div class="form-group">
            <label for="post-title">Subject</label>
            <input type="text" name="title" class="form-control" placeholder="Subject" id="post-title" required>
          </div>
          <div class="form-group">
            <label for="post-description">Description</label>
            <textarea name="description" class="form-control" id="post-description" required></textarea>
          </div>
          <div class="form-group">
            <label for="post-link" class="">Link (if applicable)</label>
            <input type="url" name="link" class="form-control" id="post-link">
          </div>
          <div class="form-group">
              <span class="post-effective">Effective?</span>
              <div class="action-like">
                  <a class="lbg-style1 like-1 save-post-thumbs save-post-thumbs-up">
                      <img src="<?php echo plugins_url( 'wti-like-post-pro/images/pixel.gif' ); ?>" title="Like">
                  </a>
              </div>
              <div class="action-unlike">
                  <a class="unlbg-style1 unlike-1 save-post-thumbs save-post-thumbs-down">
                      <img src="<?php echo plugins_url( 'wti-like-post-pro/images/pixel.gif' ); ?>" title="Unlike">
                  </a>
              </div>
              <div class="clearfix"></div>
              <input type="hidden" name="vote" id="post-vote">
          </div>
          <?php if ( ! pcs_is_user_logged_in() ) : ?>
          <p>
              Create an account to have a username associated with this post. Posts created without
              signing in will be entered anonymously.
          </p>
          <?php endif; ?>
        </form>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-default btn-post-enter">Enter</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
