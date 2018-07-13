<div class="modal fade update-profile-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form class="update-profile-form" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
          <input type="hidden" name="action" value="pcs-update-profile">
          <input type="hidden" name="_wpnonce" id="profile-nonce" value="<?php echo wp_create_nonce( 'pcs-update-profile' ); ?>">
          <input type="hidden" name="id" id="profile-id" value="">
          <div class="form-group">
            <label for="date-of-injury">Date of Injury</label>
            <input type="date" name="dateOfInjury" class="form-control" placeholder="Title" id="date-of-injury" required>
          </div>
          <div class="form-group">
            <label for="cause-of-injury">Cause of Injury</label>
            <textarea class="form-control" name="causeOfInjury" id="cause-of-injury" required></textarea>
          </div>
          <div class="form-group">
            <label for="symptoms" class="">Symptoms</label>
            <textarea type="url" name="symptoms" class="form-control" id="symptoms"></textarea>
          </div>
          <div class="form-group">
            <label for="additional-information" class="">Additional Information</label>
            <textarea type="url" name="additionalInformation" class="form-control" id="additional-information"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-default btn-profile-save" data-dismiss="modal">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<section class="author-information">
<?php if ( bp_has_profile() ) : ?>
  <?php while ( bp_profile_groups() ) : bp_the_profile_group(); ?>

    <?php if ( bp_profile_group_has_fields() ) : ?>

        <?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

          <?php if ( bp_field_has_data() ) : ?>
            <?php if ( strtolower( bp_get_the_profile_field_name() ) !== 'name' ): ?>
              <div class="profile-info">
                <div class="profile-info-name"><strong><?php bp_the_profile_field_name() ?>:</strong></div>
                <?php $valueClass = strtolower( implode( '-', explode( ' ', bp_get_the_profile_field_name() ) ) ); ?>
                <div class="profile-info-value profile-info-value-<?php echo $valueClass; ?>"><?php pcs_show_profile_field_value( bp_get_the_profile_field_name(), bp_get_the_profile_field_value() ); ?></div>
              </div>
            <?php endif; ?>
          <?php endif; ?>

        <?php endwhile; ?>

    <?php endif; ?>

  <?php endwhile; ?>

<?php else: ?>

  <div id="message" class="info">
    <p>This user does not have a profile.</p>
  </div>

<?php endif;?>

<?php if ( pcs_can_manage_visited_user( bp_displayed_user_id() ) ) : ?>
<!-- Modal window for double-checking user's intention of deleting their profile -->
<div class="modal fade delete-profile-warning" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <p>Are you sure you want to delete your profile?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-profile-delete-confirm" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<div>
  <p>
      <button class="btn btn-default btn-edit-profile" data-toggle="modal" data-target=".update-profile-modal" data-profile-id="<?php echo bp_displayed_user_id(); ?>" data-wp-nonce="<?php echo wp_create_nonce( 'pcs-update-profile' ); ?>">Edit profile</button>
      <button class="btn btn-danger btn-delete-profile" data-toggle="modal" data-target=".delete-profile-warning" data-profile-id="<?php echo pcs_get_visited_user_id(); ?>" data-wp-nonce="<?php echo wp_create_nonce( 'pcs-delete-profile' ); ?>">Delete profile</button>
  </p>
</div>
<?php endif; ?>
</section>
