<?php get_header(); ?>

    <div class="row"><!-- Start Page Content -->
          <div class="col-lg-10 col-lg-offset-1">
            <div class="row">
              <h2><?php echo bp_get_displayed_user_username(); ?></h2>
            </div>
            <div class="row">
              <header class="profile-header">
                  <a class="btn btn-default" href="<?php echo pcs_get_visited_user_profile_url(); ?>">Profile</a>
                  <div class="dropdown dropdown-inline">
                    <button class="btn btn-default" id="treatments-used-dd" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Posts <span class="caret"></span></button>
                    <ul class="dropdown-menu" aria-labelledby="treatments-used-dd">
                      <li><a href="<?php echo pcs_get_visited_user_profile_url( 'posts' ); ?>">My Posts</a></li>
                      <li><a href="<?php echo pcs_get_visited_user_profile_url( 'thumbs' ); ?>">Liked/Disliked Posts</a></li>
                      <li><a href="<?php echo pcs_get_visited_user_profile_url( 'comments' ); ?>">Comments</a></li>
                    </ul>
                  </div>
				  <?php if ( pcs_is_displayed_user_author() ): ?>
                  <a href="<?php echo pcs_get_visited_user_profile_url( 'inbox' ); ?>" class="btn btn-default">Inbox
                      <?php if ( bp_get_total_unread_messages_count( pcs_get_current_user_id() ) > 0 ) : ?>
                          <span class="badge badge-unread-messages"><?php echo bp_get_total_unread_messages_count( pcs_get_current_user_id() ); ?></span>
                      <?php endif; ?>
                  </a>
				  <?php elseif ( pcs_is_current_user_logged_in() ): ?>
				  <a href="<?php echo bp_get_send_private_message_link(); ?>"class="btn btn-default">Message</a>
				  <?php endif; ?>
              </header>
            </div>
            <div class="row  content-container">
              <?php get_template_part( 'profile/user', get_active_profile_tab() ); ?>
            </div>
          </div>
      </div><!-- EO Page Content -->

    </div>

<?php get_footer(); ?>
