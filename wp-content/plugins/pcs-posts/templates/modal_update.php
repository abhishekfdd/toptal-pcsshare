<div class="modal fade update-post-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="update-post-form" data-parsley-validate="">
            <input type="hidden" name="action" value="<?php echo pcs_posts_get_action_name_update(); ?>">
          <input type="hidden" name="_wpnonce" id="post-nonce" value="<?php echo pcs_posts_get_update_nonce(); ?>">
          <input type="hidden" name="id" id="post-id" value="">
          <div class="bg-danger bs-callout bs-callout-warning hidden">
            <p>Please make sure to fill all post fields.</p>
          </div>
          <div class="form-group">
            <label for="post-title">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Title" id="post-title" required>
          </div>
          <div class="form-group">
            <label for="post-description">Description</label>
            <textarea name="description" class="form-control" id="post-description" required></textarea>
          </div>
          <div class="form-group">
            <label for="post-link" class="">Link</label>
            <input type="url" name="link" class="form-control" id="post-link">
          </div>
        </form>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-default btn-post-save" data-dismiss="modal">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
