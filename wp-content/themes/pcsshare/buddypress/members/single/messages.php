<?php
/**
 * BuddyPress - Users Messages
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

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
    <div class="row content-container">

<?php

switch ( bp_current_action() ) :

	// Inbox/Sentbox
	case 'inbox'   :
	case 'sentbox' :

		/**
		 * Fires before the member messages content for inbox and sentbox.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_member_messages_content' ); ?>

		<div class="messages">
			<?php bp_get_template_part( 'members/single/messages/messages-loop' ); ?>
		</div><!-- .messages -->

		<?php

		/**
		 * Fires after the member messages content for inbox and sentbox.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_after_member_messages_content' );
		break;

	// Single Message View
	case 'view' :
		bp_get_template_part( 'members/single/messages/single' );
		break;

	// Compose
	case 'compose' :
		bp_get_template_part( 'members/single/messages/compose' );
		break;

	// Sitewide Notices
	case 'notices' :

		/**
		 * Fires before the member messages content for notices.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_member_messages_content' ); ?>

		<div class="messages">
			<?php bp_get_template_part( 'members/single/messages/notices-loop' ); ?>
		</div><!-- .messages -->

		<?php

		/**
		 * Fires after the member messages content for inbox and sentbox.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_after_member_messages_content' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;

?>

    </div>
</div>
