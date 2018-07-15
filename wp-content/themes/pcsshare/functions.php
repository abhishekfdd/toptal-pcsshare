<?php

add_filter( 'show_admin_bar', '__return_false' );

add_filter( 'login_redirect', 'pcs_redirect_to_referrer', 10, 3 );

add_filter( 'the_author_posts_link', 'pcs_the_member_posts_link', 10, 1 );

add_filter( 'comment_text', 'pcs_filter_anchor_tag' );

/**
 * Moderator Notifications
 */
add_filter( 'notify_moderator', 'pcs_notify_moderator', 10, 2 );
add_filter( 'comment_moderation_recipients', 'pcs_filter_comment_moderation_recipients', 10, 2 );
add_filter( 'comment_moderation_text', 'pcs_comment_moderation_text', 10, 2 );

/**
 * Author Notifications
 */
add_filter( 'notify_post_author', 'pcs_notify_post_author', 10, 2 );
add_filter( 'comment_notification_recipients', 'pcs_comment_notification_recipients', 10, 2 );
add_filter( 'comment_notification_text', 'pcs_comment_notification_text', 10, 2 );
add_filter( 'comment_notification_headers', 'pcs_comment_notification_headers', 10, 2);

/**
 * Post Actions
 */
add_action( PCSCreatePostAction::AFTER_POST_CREATE_FILTER, 'pcs_add_information_for_anonymous_users', 10, 2 );
add_action( PCSCreatePostAction::AFTER_POST_CREATE_FILTER, 'pcs_send_email_after_creating_post', 10, 2 );


/**
 * 0. Filters
 */
function pcs_redirect_to_referrer($redirect_to, $request, $user) {
	$default_url = ( empty( $request ) ? get_home_url() : $request );

	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'administrator', $user->roles ) ) {
			if ( strpos( wp_get_referer(), 'redirect_to' ) !== false ) {
				$redirect_from_request = parse_url( urldecode( wp_get_referer() ) )['query'];
				return preg_replace( '/redirect_to=/', '', $redirect_from_request, 1 );
			} else {
				return $default_url;
			}
		} else {
			return $default_url;
		}
	} else {
		return $default_url;
	}
}

function pcs_add_information_for_anonymous_users($post, $post_id) {
	if ( ! $post['author'] ) {
		pcs_set_flash( 'create.success.anonymous', 'Your post needs to be approved before publishing.' );
	}
}

function pcs_send_email_after_creating_post($post, $post_id) {
	$email_template = file_get_contents( get_template_directory() . '/email_template.tmpl');

	$email_to = get_option( 'admin_email' );
	$email_subject = 'New Post on PCS Share Treatments';
	$email_message = str_replace( '{{title}}', $post['title'], $email_template );

	// Anonymous post
	if ( ! $post['author'] ) {
		$email_message .= sprintf(
			"\n\nYou can review the post via the Post Admin Page: %s.",
			get_admin_url( get_current_blog_id(), sprintf( 'post.php?post=%d&action=edit', $post_id ) )
		);
	}

	wp_mail( $email_to, $email_subject, $email_message );
}

function pcs_the_member_posts_link($link) {
	return str_replace('author', 'members', $link);
}

function pcs_filter_anchor_tag($content) {

	$content = preg_replace("/<a(.*?)>/", '<a$1 target="_blank">', $content);

	return $content;
}

/**
 * PCS Notify Moderator
 *
 * Prevents moderation email from being sent when comment has already been approved.
 *
 * @param  boolean $maybe_notify Whether to notify moderator
 * @param  integer $comment_ID ID of the given comment
 *
 * @return boolean Whether or not to send the moderation email.
 */
function pcs_notify_moderator( $maybe_notify = true, $comment_ID = null ) {

	if( $maybe_notify == true && ! empty( $comment_ID ) ) {
		$comment = get_comment( $comment_ID );

		if( isset( $comment->comment_approved ) && $comment->comment_approved == '1' ) {
			return false;
		}
	}

	return $maybe_notify;
}

/**
 * Filter Comment Moderation Recipients
 *
 * Filters the list of recipients for comment moderation emails.
 *
 * @param array $emails     List of email addresses to notify for comment moderation.
 * @param int   $comment_id Comment ID.
 */
function pcs_filter_comment_moderation_recipients( $emails = array(), $comment_id = null) {

	$comment = get_comment( $comment_id );

	// Prevent non-admins (e.g authors) from receiving comment moderation emails.
	foreach( $emails as $index => $email ) {

		$user = get_user_by( 'email', $email );

		if( ! in_array( 'administrator', $user->roles ) ) {
			unset( $emails[$index] );
		}

	}

	return $emails;
}

/**
 * Filters the comment moderation email text.
 *
 * @since 1.5.2
 *
 * @param string $notify_message Text of the comment moderation email.
 * @param int    $comment_id     Comment ID.
 */
function pcs_comment_moderation_text( $notify_message = null, $comment_id = null ) {

	// add "Anonymous" when author is blank
	$notify_message = preg_replace('/(Author:)\s+(\(IP address.*\)?)/', "$1 Anonymous $2", $notify_message);

	// add line break before comment
	$notify_message = preg_replace('/(Comment:)/', "\r\n$1", $notify_message);

	// add line break before Currently # comments are waiting for approval
	$notify_message = preg_replace('/(Currently \d comments are waiting for approval)/', "\r\n$1", $notify_message);

	return $notify_message;
}

/**
 * Notify Post Author
 *
 * Notify Post Author when someone makes a comment on one of their posts.
 *
 * @param bool $maybe_notify Whether to notify the post author about the new comment.
 * @param int  $comment_ID   The ID of the comment for the notification.
 */
function pcs_notify_post_author( $maybe_notify = false, $comment_ID = null ) {
	return true;
}

/**
 * Comment Notification Recipients
 *
 * Filters the list of email addresses to receive a comment notification.
 *
 * @param array $emails     An array of email addresses to receive a comment notification.
 * @param int   $comment_id The comment ID.
 */
function pcs_comment_notification_recipients( $emails = array(), $comment_id ) {

	// do not send emails to "Anonymous <anonymous@pcssharetreatments.com>"
	if (($key = array_search('anonymous@pcssharetreatments.com', $emails)) !== false) {
		unset($emails[$key]);
	}

	return $emails;
}

/**
 * Comment Notification Text
 *
 * Filters the comment notification email text.
 *
 * @param string $notify_message The comment notification email text.
 * @param int    $comment_id     Comment ID.
 */
function pcs_comment_notification_text( $notify_message = null, $comment_id = null ) {

	// add "Anonymous" for blank usernames
	$notify_message = preg_replace('/(Author:)(\s+)\(IP address.*\)/', "\r\n$1 Anonymous", $notify_message);

	// remove IP Address
	$notify_message = preg_replace('/(Author:.*)\(IP address.*\)?/', "\r\n$1", $notify_message);

	// remove "Email"
	$notify_message = preg_replace('/(Email:.*)\r\n/', "", $notify_message);

	// add line break before comment
	$notify_message = preg_replace('/(Comment:)/', "\r\n$1", $notify_message);

	// remove "Trash it" link
	$notify_message = preg_replace('/(Trash it:.*)/', "", $notify_message);

	// remove "Spam it" link
	$notify_message = preg_replace('/(Spam it:.*)/', "", $notify_message);

	return $notify_message;
}

/**
 * Comment Notification Headers
 *
 * Filters the comment notification email headers.
 *
 * @param string $message_headers Headers for the comment notification email.
 * @param int    $comment_id      Comment ID.
 */
function pcs_comment_notification_headers( $headers = null, $comment_id = null ) {

	// send comment notifications emails from "PCS Share" not "$comment->comment_author"
	$headers = preg_replace('/(From:)\s+"(.*)?"/', '$1 "PCS Share"', $headers);

	// remove "Reply-To:" header
	$headers = preg_replace('/(Reply-To:.*)/', '', $headers);

	return $headers;
}

/**
 * I. Scripts & Styles
 */
function pcs_enqueue_scripts() {
	wp_enqueue_script(
		'pcs_bootstrap_js', get_template_directory_uri() . '/assets/js/bootstrap.min.js',
		['jquery']
	);
	wp_enqueue_script(
		'pcs_parsley_js', get_template_directory_uri() . '/assets/js/parsley.min.js'
	);
	wp_enqueue_script(
		'pcs_moment_js', get_template_directory_uri() . '/assets/js/moment.min.js'
	);
	wp_enqueue_script(
		'pcs_slider_js', get_template_directory_uri() . '/assets/js/pcsshare.slider.js'
	);
	wp_enqueue_script(
		'pcs_post_js', get_template_directory_uri() . '/assets/js/pcsshare.post.js'
	);
	wp_enqueue_script(
		'pcs_theme_js', get_template_directory_uri() . '/assets/js/pcsshare.js',
		['jquery']
	);
}

function pcs_enqueue_styles() {
	wp_enqueue_style( 'pcs_bootstrap_js', get_template_directory_uri() . '/assets/css/bootstrap.min.css' );
	wp_enqueue_style( 'pcs_theme', get_stylesheet_uri() );
}

add_action( 'wp_enqueue_scripts', 'pcs_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'pcs_enqueue_styles' );


/**
 * II. Navigation Menu
 */
define( 'MAIN_NAVIGATION_MENU', __('Main Navigation Menu') );
register_nav_menu( 'main', MAIN_NAVIGATION_MENU );

function pcs_get_navigation_menu($theme_location) {
	$menu = wp_get_nav_menu_object( $theme_location );
	$menu_items = wp_get_nav_menu_items( $menu->term_id );

	// Build a parent-child list of menu items
	list( $menu_parents, $menu_children ) = pcs_build_menu_parent_child_relationship( $menu_items );

	$menu_code = [];

	// Start menu
	$menu_code[] = '<ul class="nav navbar-nav">';

	// Add items
	foreach ( $menu_parents as $parent_item ) {
		// Top level item without children
		if ( ! array_key_exists( $parent_item->ID, $menu_children ) ) {
			$menu_code[] = sprintf(
				'<li><a href="%s">%s <span class="sr-only">(current)</span></a></li>',
				$parent_item->url, $parent_item->title
			);
			continue;
		}

		// Top level item with children
		$menu_code[] = '<li class="dropdown">';
		$menu_code[] = sprintf(
			'<a href="%s" target="_blank" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">%s <span class="caret"></span></a>',
			$parent_item->url, $parent_item->title
		);
		$menu_code[] = '<ul class="dropdown-menu">';

		// Children items
		foreach ( $menu_children[$parent_item->ID] as $child_item ) {
			$menu_code[] = sprintf('<li><a href="%s">%s</a></li>', $child_item->url, $child_item->title);
		}

		$menu_code[] = '</ul>';
		$menu_code[] = '</li>';
	}

	// End menu
	$menu_code[] = '</ul>';

	return implode("\n", $menu_code);
}

function pcs_build_menu_parent_child_relationship($menu_items) {
	$menu_parents = [];
	$menu_children = [];

	foreach ( $menu_items as $menu_item ) {
		if ( ! $menu_item->menu_item_parent ) {
			$menu_parents[$menu_item->ID] = $menu_item;
			continue;
		}

		$parent_item = $menu_item->menu_item_parent;

		if ( array_key_exists( $parent_item, $menu_children ) ) {
			$menu_children[$parent_item][] = $menu_item;
		} else {
			$menu_children[$parent_item] = [$menu_item];
		}
	}

	return [ $menu_parents, $menu_children ];
}

function pcs_show_navigation_menu($theme_location) {
	echo pcs_get_navigation_menu( $theme_location );
}


/**
 * III. Post Slider
 */
function pcs_show_posts_slider($limit = 5, $order = 'desc') {

	$posts = pcs_get_posts_by_thumbs_up( $limit, $order );

	$top_post = ( count( $posts ) > 0 ? $posts[0] : null );

	$img_dir =  get_stylesheet_directory_uri() . '/assets/img/';

	$img_carousel = array(
		'pcs-share-logo-blue-450x450.png',
		'pcs-share-logo-purple-450x450.png',
		'pcs-share-logo-orange-450x450.png',
		'pcs-share-logo-green-450x450.png',
		'pcs-share-logo-red-450x450.png'
	);

	$slider_markup = [];

	// Slides
	$slider_markup[] = '<div class="slider-contents">';

		foreach ( $posts as $i => $post ) {

			$image_index = ( $i < $limit ? $i : (( $limit % 5 ) * 5) );
			$post_image =  $img_dir . $img_carousel[$image_index];

			$slider_markup[] = sprintf(
				'<a href="%s" target="_blank" class="%s" data-id="%s">'.
					'<img src="%s" alt="%s" data-description="%s" class="slider-slide">'.
				'</a>',
				pcs_get_post_excerpt_link( $post->ID ),
				( $i > 0 ? 'hidden' : '' ),
				$post->ID,
				$post_image,
				esc_html( pcs_get_post_excerpt_title( $post->ID ) ? : $post->post_title ),
				esc_html( pcs_get_post_excerpt_meta_description( $post->ID ) )
			);
		}

	$slider_markup[] = '</div>';

	$slider_markup[] = '<div class="slider-arrow slider-arrow-prev">&lt;</div>';
	$slider_markup[] = '<div class="slider-arrow slider-arrow-next">&gt;</div>';

	// Navigation items (= bullet points)
	$slider_markup[] = '<div class="slider-navigation">';

		foreach ( $posts as $post ) {
			$label_class = ( is_null( $top_post ) || $post->post_title !== $top_post->post_title ? 'slider-navigation-item' : 'slider-navigation-item slider-navigation-item-active' );
			$slider_markup[] = sprintf( '<label class="%s"></label>', $label_class );
		}

	$slider_markup[] = '</div>';

	// Current slide title
	$slider_markup[] = '<div class="slider-active-slide-container">';

		$slider_markup[] = sprintf(
			'<a href="%s" target="_blank" class="slider-active-slide-link">'.
				'<span class="slider-active-slide-title">%s</span>'.
				'<span class="slider-active-slide-description">%s</span>'.
				'<span class="slider-active-slide-domain">%s</span>'.
			'</a>',
			( is_null( $top_post ) ? '' : pcs_get_post_excerpt_link( $top_post->ID ) ),
			( is_null( $top_post ) ? '' : ( pcs_get_post_excerpt_title( $top_post->ID ) ? : $top_post->post_title ) ),
			( is_null( $top_post ) ? '' : ( pcs_get_post_excerpt_meta_description( $top_post->ID ) ? : '' ) ),
			preg_replace( '/^www\./', '', parse_url( pcs_get_post_excerpt_link( $top_post->ID ) )['host'] )
		);

	$slider_markup[] = '</div>';

	echo implode("\n", $slider_markup);
}

function pcs_save_post($post_id) {

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;

	if( 'post' != get_post_type( $post_id ) )
		return;

	$excerpt_link = get_post_meta( $post_id, 'pcs_post_excerpt_link' );

	if( ! empty( $excerpt_link ) ) {

		$tags = get_meta_tags( esc_url( $excerpt_link[0] ) );

		if( ! empty( $tags['og:description'] ) ) {
			$description = $tags['og:description'];
		} else if( ! empty( $tags['description'] ) ) {
			$description = $tags['description'];
		}

		if( ! empty( $description) ) {
			update_post_meta( $post_id, 'pcs_post_excerpt_meta_description', $description );
		}
	}
}

add_action( 'save_post', 'pcs_save_post', 10, 1 );

/**
 * IV. Utilities
 */
function pcs_get_current_url() {
	global $wp;
	$current_url = home_url( add_query_arg( [], $wp->request) );
	return $current_url;
}

function pcs_show_profile_field_value($field_name, $field_value) {
	$field_output_value = trim( $field_value );

	if ( strtolower( $field_name ) === 'date of injury' ) {
		$date_time = DateTime::createFromFormat( 'Y-m-d', strip_tags( $field_output_value) );
		$field_output_value = sprintf( '<p>%s</p>', strftime( '%e %B %Y', $date_time->getTimestamp() ) );
	}

	echo $field_output_value;
}

function pcs_disable_rich_text($enabled, $field_id) {
  return false;
}
add_filter('bp_xprofile_is_richtext_enabled_for_field','pcs_disable_rich_text', 10, 2);


function on_delete_user_remove_likes_and_dislikes($id) {
  global $wpdb;

  $wpdb->delete( 'wp_wti_like_post', array( 'user_id' => $id ) );
}
add_action( 'delete_user', 'on_delete_user_remove_likes_and_dislikes', 10, 1);
