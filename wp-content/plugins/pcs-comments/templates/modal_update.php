<div class="modal fade update-comment-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form action="%form%" method="post" class="update-comment-form" data-parsley-validate="">
          <input type="hidden" name="action" value="%action%">
          <input type="hidden" name="_wpnonce" id="comment-nonce" value="%nonce%">
          <input type="hidden" name="id" id="comment-id" value="">
          <div class="form-group">
            <label for="comment-message">Message</label>
            <textarea name="message" class="form-control" id="comment-message" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-comment-save" data-dismiss="modal">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
