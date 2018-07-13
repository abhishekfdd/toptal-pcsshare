<?php

/*
 * Plugin Name: PCS Thumbs
 * Plugin URI: http://pcssharetreatments.com
 * Description: The plug-in provides some convenient methods over Wti Like Post PRO plug-in
 * Author: Hrvoje Gazibara
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', 'check_wti_like_post_pro_active' );

function check_wti_like_post_pro_active() {

    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  ! is_plugin_active( 'wti-like-post-pro/wti-like-post-pro.php' ) ) {

        add_action( 'admin_notices', 'wti_like_post_pro_missing' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function wti_like_post_pro_missing(){
    echo '<div class="error"><p>Sorry, but you need to install Wti Like Post PRO first. :(</p></div>';
}

function pcs_get_posts_by_thumbs_up($limit = 5, $order = 'desc') {
    global $wpdb;

    $unprepared_query = implode( " ", [
        "SELECT DISTINCT `post_id` FROM {$wpdb->prefix}postmeta",
        "JOIN {$wpdb->prefix}posts ON `ID` = `post_id`",
        "WHERE meta_key=%s AND meta_value <> '' AND post_status = 'publish'"
    ]);

    $posts_with_links = $wpdb->get_results(
    	$wpdb->prepare(
        	$unprepared_query,
			'pcs_post_excerpt_link'
    	)
	);

    $posts_with_links_ids = [];

    array_map(function ($entry) use (&$posts_with_links_ids) {
        $posts_with_links_ids[] = $entry->post_id;
    }, $posts_with_links);

    $ids_to_take_into_account = implode(',', $posts_with_links_ids);

    $posts = $wpdb->get_results($wpdb->prepare(
    	"SELECT post_id, SUM(value) as total_value FROM {$wpdb->prefix}wti_like_post WHERE post_id IN ({$ids_to_take_into_account}) GROUP BY post_id ORDER BY total_value $order LIMIT %d",
    	$limit
    ));

    # If there are some posts, return them, otherwise try to get any posts
    if ( count( $posts ) !== 0 ) {
        $post_ids = array_map(function ($entry) {
            return $entry->post_id;
        }, $posts);

    	$found_posts = get_posts([
            'post__in' => $post_ids,
            'orderby' => 'post__in'
    	]);

        return $found_posts;
    }

    return get_posts([
     'posts_per_page' => $limit
    ]);
}

function pcs_get_posts_liked_by_user($user_id) {
    global $wpdb;

    $statement = $wpdb->prepare(
     	"SELECT DISTINCT post_id FROM {$wpdb->prefix}wti_like_post WHERE user_id = %d",
     	$user_id
    );

    $post_ids = $wpdb->get_results( $statement );

    return empty( $post_ids ) ? [] : array_map( function ($element) {
    	return $element->post_id;
    }, $post_ids );
}

function pcs_get_post_thumbs_up_class($post_id) {
    $user_id = pcs_get_visited_user_id();
    return pcs_has_user_liked_post( $post_id, $user_id ) ? 'btn-success' : 'btn-default';
}

function pcs_get_post_thumbs_down_class($post_id) {
    $user_id = pcs_get_visited_user_id();
    return pcs_has_user_disliked_post( $post_id, $user_id ) ? 'btn-danger' : 'btn-default';
}

function pcs_has_user_liked_post($post_id, $user_id) {
    global $wpdb;

    $wti_like_count = (int)$wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post
			WHERE post_id = %d AND user_id = %d AND value >= 0",
			$post_id,
			$user_id
		)
	);

    return $wti_like_count > 0;
}

function pcs_has_user_disliked_post($post_id, $user_id) {
    global $wpdb;

    $wti_unlike_count = (int)$wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post
			WHERE post_id = %d AND user_id = %d AND value <= 0",
			$post_id,
			$user_id
		)
	);

    return $wti_unlike_count < 0;
}

function pcs_users_who_liked_post($post_id) {

    $output = '';

    $users = pcs_get_users_who_liked_post( $post_id );

    foreach ( $users as $user ) {

        if ( 'anonymous' != $user->user_login ) {
            $output .= sprintf( '<li><a href="%s">%s</a></li>', site_url( "/members/{$user->user_login}" ), $user->user_login );
        } else {
			$output .= '<li>Anonymous</li>';
        }
    }

    echo $output;
}

function pcs_get_users_who_liked_post($post_id) {
    global $wpdb;

    $wti_users_liked = $wpdb->get_results(
    	$wpdb->prepare(
    		"SELECT DISTINCT `user_id`, `user_login` FROM `{$wpdb->prefix}wti_like_post`
    		JOIN `wp_users` ON `{$wpdb->prefix}wti_like_post`.`user_id` = `wp_users`.`ID`
    		WHERE `post_id` = %d AND value > 0",
    		$post_id
    	)
    );

    return $wti_users_liked;
}

function pcs_users_who_disliked_post($post_id) {

    $output = '';

    $users = pcs_get_users_who_disliked_post( $post_id );

    foreach ( $users as $user ) {

        if ( 'anonymous' != $user->user_login ) {
            $output .= sprintf( '<li><a href="%s">%s</a></li>', site_url( "/members/{$user->user_login}" ), $user->user_login );
        } else {
			$output .= '<li>Anonymous</li>';
        }
    }

    echo $output;
}

function pcs_get_users_who_disliked_post($post_id) {
    global $wpdb;

    $wti_users_disliked = $wpdb->get_results(
    	$wpdb->prepare(
    		"SELECT DISTINCT `user_id`, `user_login` FROM `{$wpdb->prefix}wti_like_post`
    		JOIN `wp_users` ON `{$wpdb->prefix}wti_like_post`.`user_id` = `wp_users`.`ID`
    		WHERE `post_id` = %d AND value < 0",
    		$post_id
    	)
    );

    return $wti_users_disliked;
}
