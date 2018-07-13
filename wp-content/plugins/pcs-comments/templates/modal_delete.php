<div class="modal fade delete-comment-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <p>Are you sure you want to delete this comment?</p>
        <input type="hidden" id="delete-comment-action" value="%action%">
        <input type="hidden" id="delete-comment-nonce" value="%nonce%">
        <input type="hidden" id="delete-comment-id">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-delete-comment-confirm" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>
