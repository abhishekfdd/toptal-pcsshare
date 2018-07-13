<?php
/*
Plugin Name: WTI Like Post PRO
Plugin URI: http://www.webtechideas.in/product/wti-like-post-pro/
Description: WTI Like Post PRO is the advanced version plugin for adding like (thumbs up) and unlike (thumbs down) functionality for wordpress posts/pages. Please check the Features & Support section for complete list of features.
Version: 2.2
Author: webtechideas
Author URI: http://www.webtechideas.in
License: GPLv2 or later

Copyright 2013  Webtechideas  (email : support@webtechideas.com)
*/

#### INSTALLATION PROCESS ####
/*
1. Download the plugin and extract it
2. Upload the directory '/wti-like-post/' to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Click on 'WTI Like Post PRO' link under Settings menu to access the admin section
*/

global $wti_like_post_db_version, $wti_ip_address;

$wti_like_post_db_version = "2.2";
$wti_ip_address = WtiGetRealIpAddress();

add_action('init', 'WtiLikePostLoadPluginSettings');

/**
 * Load the basic settings like language file and filters
 * @param no-param
 * @return no-return
 */
function WtiLikePostLoadPluginSettings() {
	// Load the plugin language file
     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
	
	// Get the settings for post content and excerpt
	$disable_auto_load = get_option('wti_like_post_disable_auto_load');
	$use_on_excerpt = get_option('wti_like_post_use_on_excerpt');
	
	// Manage on post content
	if ($disable_auto_load) {
		remove_filter('the_content', 'PutWtiLikePost');
	} else if (!is_admin()) {
		add_filter('the_content', 'PutWtiLikePost');
	}
	
	// Manage on post excerpt
	if ($use_on_excerpt && !is_admin()) {
		add_filter('the_excerpt', 'ExcerptWtiLikePost');
	} else {
		remove_filter('the_excerpt', 'ExcerptWtiLikePost');
	}
}

/**
 * Show additional message for plugin update
 * @param void
 * @return void
 */
function WtiLikePostUpdateNotice() {
    $info_title = __( 'In case there was any customization done with this plugin, then please backup the plugin files and wti_like_post table before updating.', 'wti-like-post' );
    $info_text = '';
    echo '<div style="border-top:1px solid #CCC; margin-top:3px; padding-top:3px; font-weight:normal;"><strong style="color:#CC0000">' . strip_tags( $info_title ) . '</strong> ' . strip_tags( $info_text, '<br><a><strong><em><span>' ) . '</div>';
}

add_filter('plugin_action_links', 'WtiLikePostPluginLinks', 10, 2);

/**
 * Create the settings link for this plugin
 * @param $links array
 * @param $file string
 * @return $links array
 */
function WtiLikePostPluginLinks($links, $file) {
     static $this_plugin;

     if ( !$this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
     }

     if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=WtiLikePostAdminMenu' ) .'">' . __( 'Settings', 'wti-like-post' ) . '</a>';
		array_unshift( $links, $settings_link );
     }

     return $links;
}

/**
 * Basic options function which takes care of multi sites
 * @param no-param
 * @return void
 */
function SetOptionsWtiLikePost($network_wide) {
     global $wpdb;

     if (function_exists('is_multisite') && is_multisite()) {
		// In case of a network activation, run the activation function for each blog
		if ($network_wide) {
			$main_blog = $wpdb->blogid;
			
			// Get all blog ids
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			
			foreach ($blog_ids as $blog_id) {
				// Switch to specific blog and set the options for that blog
				switch_to_blog($blog_id);
				SetAllOptionsWtiLikePost();
			}
			
			// Switch back to main blog
			switch_to_blog($main_blog);
			return;
		}
	}
	
	// Call for the main site
	SetAllOptionsWtiLikePost();
}

register_activation_hook(__FILE__, 'SetOptionsWtiLikePost');

/**
 * Create table and set the options for this plugin
 * @param no-param
 * @return no-return
 */
function SetAllOptionsWtiLikePost() {
     global $wpdb, $wti_like_post_db_version;

     //creating the like post table on activating the plugin
     $wti_like_post_table_name = $wpdb->prefix . "wti_like_post";
	add_option("wti_like_post_db_version", $wti_like_post_db_version);
	
     if($wpdb->get_var("show tables like '$wti_like_post_table_name'") != $wti_like_post_table_name) {
		$sql = "CREATE TABLE " . $wti_like_post_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`value` int(2) NOT NULL,
			`date_time` datetime NOT NULL,
			`ip` varchar(40) NOT NULL,
			`user_id` int(11) NOT NULL DEFAULT '0',
			`cookie_value` varchar(22) NOT NULL,
			PRIMARY KEY (`id`)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
     }
	
	// Set all post/page like, unlike, total meta data
	WtiUpdateLikeMetaData();
	
     //adding options for the like post plugin
     add_option('wti_like_post_use_plugin_css', '1', '', 'yes');
     add_option('wti_like_post_drop_settings_table', '0', '', 'yes');
     add_option('wti_like_post_disable_auto_load', '0', '', 'yes');
     add_option('wti_like_post_use_on_excerpt', '1', '', 'yes');
     add_option('wti_like_post_voting_period', '0', '', 'yes');
     add_option('wti_like_post_voting_style', 'style1', '', 'yes');
     add_option('wti_like_post_alignment', 'left', '', 'yes');
     add_option('wti_like_post_position', 'bottom', '', 'yes');
     add_option('wti_like_post_login_required', '0', '', 'yes');
     add_option('wti_like_post_default_message', __('Be the 1st to vote.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_login_message', __('Please login to vote.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_thank_message', __('Thanks for your vote.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_voted_message', __('You have already voted.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_min_like', '', '', 'yes');
     add_option('wti_like_post_max_like', '', '', 'yes');
	add_option('wti_like_post_apply_existing', '0', '', 'yes');
     add_option('wti_like_post_post_types', 'post', '', 'yes');
     add_option('wti_like_post_bp_like_activity', '0', '', 'yes');
     add_option('wti_like_post_disallow_author_voting', '1', '', 'yes');
     add_option('wti_like_post_author_disallowed_message', __('You can not vote your own post.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_allowed_posts', '', '', 'yes');
     add_option('wti_like_post_excluded_posts', '', '', 'yes');
     add_option('wti_like_post_allowed_categories', '', '', 'yes');
     add_option('wti_like_post_excluded_categories', '', '', 'yes');
     add_option('wti_like_post_excluded_sections', '', '', 'yes');
     add_option('wti_like_post_show_on_pages', '0', '', 'yes');
     add_option('wti_like_post_show_on_widget', '1', '', 'yes');
     add_option('wti_like_post_show_symbols', '1', '', 'yes');
     add_option('wti_like_post_show_dislike', '1', '', 'yes');
     add_option('wti_like_post_title_text', 'Like/Unlike', '', 'yes');
     add_option('wti_like_post_redirect_url', '', '', 'yes');
     add_option('wti_like_post_like_text', 'Click to like it', '', 'yes');
     add_option('wti_like_post_unlike_text', 'Click to unlike it', '', 'yes');
     add_option('wti_like_post_show_like_unlike_text', '0', '', 'yes');
     add_option('wti_like_post_show_user_likes', '0', '', 'yes');
     add_option('wti_like_post_check_option', '0', '', 'yes');
     add_option('wti_like_post_db_version', $wti_like_post_db_version, '', 'yes');
}

/**
 * For dropping the table and removing options
 * @param no-param
 * @return no-return
 */
function UnsetOptionsWtiLikePost() {
     global $wpdb;
	
	if (function_exists('is_multisite') && is_multisite()) {
		// In case of a network activation, run the uninstall function for each blog
		$main_blog = $wpdb->blogid;
		
		// Get all blog ids
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		
		foreach ($blog_ids as $blog_id) {
			switch_to_blog($blog_id);
			UnsetAllOptionsWtiLikePost();
		}
		
		switch_to_blog($main_blog);
		return;
	}
	
	UnsetAllOptionsWtiLikePost();
}

register_uninstall_hook(__FILE__, 'UnsetOptionsWtiLikePost');

function UnsetAllOptionsWtiLikePost() {
     global $wpdb;
     
	// Check the option whether to drop the settings table on plugin uninstall or not
	$drop_settings_table = get_option('wti_like_post_drop_settings_table');
	
	if ($drop_settings_table == 1) {
		// Drop the table and delete the settings on plugin uninstall
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wti_like_post");
		
		delete_option('wti_like_post_use_plugin_css');
		delete_option('wti_like_post_drop_settings_table');
		delete_option('wti_like_post_disable_auto_load');
		delete_option('wti_like_post_use_on_excerpt');
		delete_option('wti_like_post_voting_period');
		delete_option('wti_like_post_voting_style');
		delete_option('wti_like_post_alignment');
		delete_option('wti_like_post_position');
		delete_option('wti_like_post_login_required');
		delete_option('wti_like_post_default_message');
		delete_option('wti_like_post_login_message');
		delete_option('wti_like_post_thank_message');
		delete_option('wti_like_post_voted_message');
		delete_option('wti_like_post_min_like');
		delete_option('wti_like_post_max_like');
		delete_option('wti_like_post_apply_existing');
		delete_option('wti_like_post_post_types');
		delete_option('wti_like_post_bp_like_activity');
		delete_option('wti_like_post_disallow_author_voting');
		delete_option('wti_like_post_author_disallowed_message');
		delete_option('wti_like_post_db_version');
		delete_option('wti_like_post_allowed_posts');
		delete_option('wti_like_post_excluded_posts');
		delete_option('wti_like_post_allowed_categories');
		delete_option('wti_like_post_excluded_categories');
		delete_option('wti_like_post_excluded_sections');
		delete_option('wti_like_post_show_on_pages');
		delete_option('wti_like_post_show_on_widget');
		delete_option('wti_like_post_show_symbols');
		delete_option('wti_like_post_show_dislike');
		delete_option('wti_like_post_title_text');
		delete_option('wti_like_post_redirect_url');
		delete_option('wti_like_post_show_like_unlike_text');
		delete_option('wti_like_post_like_text');
		delete_option('wti_like_post_unlike_text');
		delete_option('wti_like_post_check_option');
		delete_option('wti_like_post_show_user_likes');
	}
}

/**
 * Create the update function for this plugin
 * @param no-param
 * @return no-return
 */
function UpdateOptionsWtiLikePost() {
     global $wpdb;

     if (function_exists('is_multisite') && is_multisite()) {
		// In case of a network activation, run the activation function for each blog

		$main_blog = $wpdb->blogid;
		
		// Get all blog ids
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		
		foreach ($blog_ids as $blog_id) {
			// Switch to specific blog and set the options for that blog
			switch_to_blog($blog_id);
			UpdateAllOptionsWtiLikePost();
		}
		
		// Switch back to main blog
		switch_to_blog($main_blog);
		return;
	}
	
	// Call for the main site
	UpdateAllOptionsWtiLikePost();
}

/**
 * Create the update function for this plugin
 * @param no-param
 * @return no-return
 */
function UpdateAllOptionsWtiLikePost() {
	global $wpdb, $wti_like_post_db_version;
	
	// Remove all the entries with 0 values
	//$wpdb->query("DELETE FROM {$wpdb->prefix}wti_like_post WHERE `value` = 0");

	// Get current database version for this plugin
	$current_db_version = get_option('wti_like_post_db_version');

	if ($current_db_version != $wti_like_post_db_version) {
		// Increase column size to support IPv6
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}wti_like_post` CHANGE `ip` `ip` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");

		$check_col = $wpdb->get_row("SHOW COLUMNS FROM {$wpdb->prefix}wti_like_post LIKE 'user_id'");
	
		if (count($check_col) == 0) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}wti_like_post ADD `user_id` INT NOT NULL DEFAULT '0'");
		}
		
		$cookie_col = $wpdb->get_row("SHOW COLUMNS FROM {$wpdb->prefix}wti_like_post LIKE 'cookie_value'");
		
		if(count($cookie_col) == 0) {
			$wpdb->query("ALTER TABLE {$wpdb->prefix}wti_like_post ADD `cookie_value` VARCHAR( 22 ) NOT NULL ");
		}
		
		update_option("wti_like_post_db_version", $wti_like_post_db_version);
	}
}

add_action('plugins_loaded', 'UpdateOptionsWtiLikePost');

if (is_admin()) {
	// Include the file for loading plugin settings
	require_once('wti-like-post-admin.php');
} else {
	// Include the file for loading plugin settings for
	require_once('wti-like-post-site.php');
	
     add_action('init', 'WtiLikePostEnqueueScripts');
     
     // Load the plugin css only when its set
     $use_plugin_css = get_option('wti_like_post_use_plugin_css');
     
     if($use_plugin_css) {
          add_action('wp_head', 'WtiLikePostAddHeaderLinks');
     }
}

/**
 * Register the options those are used
 * @param no-param
 * @return no-return
 */
function WtiLikePostAdminRegisterSettings() {
     //registering the settings
     register_setting( 'wti_like_post_options', 'wti_like_post_use_plugin_css' );
     register_setting( 'wti_like_post_options', 'wti_like_post_drop_settings_table' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voting_period' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voting_style' );
     register_setting( 'wti_like_post_options', 'wti_like_post_alignment' );
     register_setting( 'wti_like_post_options', 'wti_like_post_position' );
     register_setting( 'wti_like_post_options', 'wti_like_post_login_required' );
     register_setting( 'wti_like_post_options', 'wti_like_post_default_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_login_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_thank_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voted_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_min_like' );
     register_setting( 'wti_like_post_options', 'wti_like_post_max_like' );
     register_setting( 'wti_like_post_options', 'wti_like_post_apply_existing' );
     register_setting( 'wti_like_post_options', 'wti_like_post_post_types' );
     register_setting( 'wti_like_post_options', 'wti_like_post_bp_like_activity' );
     register_setting( 'wti_like_post_options', 'wti_like_post_disallow_author_voting' );
     register_setting( 'wti_like_post_options', 'wti_like_post_author_disallowed_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_allowed_posts' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_posts' );
     register_setting( 'wti_like_post_options', 'wti_like_post_allowed_categories' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_categories' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_sections' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_on_pages' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_on_widget' );
     register_setting( 'wti_like_post_options', 'wti_like_post_db_version' );	
     register_setting( 'wti_like_post_options', 'wti_like_post_show_symbols' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_dislike' );
     register_setting( 'wti_like_post_options', 'wti_like_post_title_text' );
     register_setting( 'wti_like_post_options', 'wti_like_post_redirect_url' );
     register_setting( 'wti_like_post_options', 'wti_like_post_like_text' );
     register_setting( 'wti_like_post_options', 'wti_like_post_unlike_text' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_like_unlike_text' );
     register_setting( 'wti_like_post_options', 'wti_like_post_disable_auto_load' );
     register_setting( 'wti_like_post_options', 'wti_like_post_use_on_excerpt' );
     register_setting( 'wti_like_post_options', 'wti_like_post_check_option' );	
     register_setting( 'wti_like_post_options', 'wti_like_post_show_user_likes' );	
}

add_action('admin_init', 'WtiLikePostAdminRegisterSettings');

// Load the widgets
require_once('wti-like-post-widgets.php');

/**
 * Show the list of users who like the content
 * @param $post_id int
 * @return string
 */
function GetWtiUserLikes($post_id, $show_user_likes = 1) {
	global $wpdb;
	
	$query = "SELECT DISTINCT(user_id) FROM " . $wpdb->prefix . "wti_like_post WHERE post_id = $post_id AND user_id > 0 AND value > 0 ORDER BY date_time DESC";
	$user_ids = $wpdb->get_col($query);
	$users = array();
	$wti_user_likes = "";
	
	if ($user_ids) {
		global $current_user;
		$flag = false;
		
		// If logged in user is in the like users list, then set the flag remove him from the list
		if ($current_user->ID > 0 && in_array($current_user->ID, $user_ids)) {
			$flag = true;
			$users[] = __('You', 'wti-like-post');
			$user_key = array_search($current_user->ID, $user_ids);
			unset($user_ids[$user_key]);
		}
		
		// Get the usernames
		foreach ($user_ids as $user_id) {
			$user_info = get_userdata($user_id);
			$users[] = apply_filters( 'wti_like_post_user_profile_link', $user_info->display_name, $user_id );
		}
		
		$users_count = count($users);
		
		// List the users who liked the post
		if ($users_count > 0 && $show_user_likes == 1) {
			$show_count = 3;
			if ($users_count > $show_count) {
				$other_users_count = $users_count - $show_count;
				
				for ($i = 0; $i < $show_count; $i++) {
					$wti_user_likes .= $users[$i] . ', ';
					unset($users[$i]);
				}
				
				$wti_user_likes = substr($wti_user_likes, 0, -2) . ' ' . __('and', 'wti-like-post') . ' ';
				$wti_user_likes .= '<span class="wti-others-like">' . $other_users_count . ' ';
				$wti_user_likes .= ($other_users_count > 1) ? __('others', 'wti-like-post') : __('other', 'wti-like-post');
				$wti_user_likes .= '<span class="wti-others-tip">' . implode(", ", $users) . '</span>';
				$wti_user_likes .= '</span> ';
			} else {
				$wti_user_likes .= implode(", ", $users) . ' ';
			}
			
			$wti_user_likes .= ($users_count > 1 || ($users_count == 1 && $flag == true)) ? __('like this', 'wti-like-post') : __('likes this', 'wti-like-post');
		}
	}
	
	return $wti_user_likes;
}

/**
 * Get like count for a post
 * @param $post_id integer
 * @return string
 */
function GetWtiLikeCount($post_id) {
     global $wpdb;
     $show_symbols = get_option('wti_like_post_show_symbols');
     $wti_like_count = $wpdb->get_var("SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND value > 0");
	
     if(!$wti_like_count) {
		$wti_like_count = 0;
     } else {
		if($show_symbols) {
			$wti_like_count = "+" . $wti_like_count;
		} else {
			$wti_like_count = $wti_like_count;
		}
     }
	
     return $wti_like_count;
}

/**
 * Get unlike count for a post
 * @param $post_id integer
 * @return string
 */
function GetWtiUnlikeCount($post_id) {
     global $wpdb;
     $show_symbols = get_option('wti_like_post_show_symbols');
     $wti_unlike_count = $wpdb->get_var("SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND value < 0");
     
     if(!$wti_unlike_count) {
		$wti_unlike_count = 0;
     } else {
		if($show_symbols) {
		} else {
			$wti_unlike_count = str_replace('-', '', $wti_unlike_count);
		}
     }
     
     return $wti_unlike_count;
}

/**
 * Get total count for a post
 * @param $post_id integer
 * @return string
 */
function GetWtiTotalCount($post_id) {
     global $wpdb;
     $show_symbols = get_option('wti_like_post_show_symbols');
     $wti_like_count = (int)get_post_meta($post_id, '_wti_like_count', true);
     $wti_unlike_count = (int)get_post_meta($post_id, '_wti_unlike_count', true);
	$wti_total_count = $wti_like_count - $wti_unlike_count;
     
     return $wti_total_count;
}

/**
 * Check whether user has already voted or not
 * @param $post_id integer
 * @return integer
 */
function HasWtiAlreadyVoted($post_id, $check_option = 0, $voting_period = '', $get_voted_id = false) {
     global $wpdb, $wti_ip_address;
	$where = '';
	$wti_has_voted = 0;
	$voted_id = 0;
	$voted_details = array();
	
     if ( $voting_period != 0 && $voting_period != 'once' ) {
          // If there is restriction on revoting with voting period, check with voting time
          $last_voted_date = GetWtiLastDate( $voting_period );
          $where = "AND date_time >= '$last_voted_date'";
     }
     
	if ( $check_option == 2 ) {
		// 0 never voted, 1 this user has voted, 2 others have voted
		if ( isset($_COOKIE["wtilp_count_$post_id"]) && !empty($_COOKIE["wtilp_count_$post_id"]) ) {
			// In case cookie already set
			$wti_has_voted = 1;
		}  else {
			// In case cookie deleted
			$votes_key = $wpdb->get_col("SELECT cookie_value AS has_voted FROM {$wpdb->prefix}wti_like_post
								   WHERE post_id = '$post_id' $where", 0);
			
			if ( empty( $votes_key ) ) {
				$wti_has_voted = 0;
			} else {
				$wti_has_voted = 2;
			}
		}
	} else if ( $check_option == 1 ) {
		// Check with user id
		$current_user = wp_get_current_user();
		$user_id = (int)$current_user->ID;
		
		$result = $wpdb->get_row("SELECT COUNT(id) AS has_voted, SUM(value) AS vote_count
							FROM {$wpdb->prefix}wti_like_post
							WHERE post_id = '$post_id' AND user_id = $user_id $where");
		
		$wti_has_voted = $result->has_voted > 0 ? 1 : 0;
		$wti_voted_count = $result->vote_count;
		
		if ( $get_voted_id ) {
			$voted = $wpdb->get_var("SELECT MAX(id) FROM {$wpdb->prefix}wti_like_post
								WHERE post_id = '$post_id' AND user_id = $user_id");
			
			$voted_id = (int)$voted;
		}
	} else {
		/*$result = $wpdb->get_row("SELECT COUNT(id) AS has_voted, SUM(value) AS vote_count
							FROM {$wpdb->prefix}wti_like_post
							WHERE post_id = '$post_id' AND ip = '$wti_ip_address' $where");*/
		
		$result = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT COUNT(id) AS has_voted, SUM(value) AS vote_count
						FROM {$wpdb->prefix}wti_like_post
						WHERE post_id = %d AND ip = %s $where",
						$post_id, $wti_ip_address
					)
				);
		
		$wti_has_voted = $result->has_voted > 0 ? 1 : 0;
		$wti_voted_count = $result->vote_count;
		
		if ( $get_voted_id ) {
			/*$voted = $wpdb->get_var("SELECT MAX(id) FROM {$wpdb->prefix}wti_like_post
								WHERE post_id = '$post_id' AND ip = '$wti_ip_address'");*/
			
			$voted = $wpdb->get_var(
						$wpdb->prepare(
								"SELECT MAX(id) FROM {$wpdb->prefix}wti_like_post
								WHERE post_id = %d AND ip = %s",
								$post_id, $wti_ip_address
							)
						);
			
			$voted_id = (int)$voted;
		}
	}
	
     return array(
			   'has_voted' => $wti_has_voted,
			   'voted_id' => $voted_id,
			   'voted_count' => $wti_voted_count
			);
}

/**
 * Get last voted date for a given post
 * @param $post_id integer
 * @param $check_option integer
 * @param $user_id integer
 * @return string
 */
function GetWtiLastVotedDate($post_id, $check_option, $user_id) {
     global $wpdb, $wti_ip_address;
	
	if ( $check_option == 1 ) {
		// Check with user id
		$query = $wpdb->prepare(
							"SELECT date_time FROM {$wpdb->prefix}wti_like_post
							WHERE post_id = %d AND user_id = %d",
							$post_id, $user_id
						);
	} else {
		// Check with ip
		$query = $wpdb->prepare(
						    "SELECT date_time FROM {$wpdb->prefix}wti_like_post
							WHERE post_id = %d AND ip = %s",
							$post_id, $wti_ip_address
						);
	}

     return $wpdb->get_var( $query );
}

/*function GetWtiLastVotedDate($post_id, $check_option, $user_id) {
     global $wpdb, $wti_ip_address;
	
	if ( $check_option == 1 ) {
		// Check with user id
		$query = "SELECT date_time FROM {$wpdb->prefix}wti_like_post
				WHERE post_id = '$post_id' AND user_id = '$user_id'";
	} else {
		// Check with ip
		$query = "SELECT date_time FROM {$wpdb->prefix}wti_like_post
				WHERE post_id = '$post_id' AND ip = '$wti_ip_address'";
	}
     
     $wti_last_voted = $wpdb->get_var( $query );

     return $wti_last_voted;
}*/

/**
 * Get next vote date for a given user
 * @param $last_voted_date string
 * @param $voting_period integer
 * @return string
 */
function GetWtiNextVoteDate($last_voted_date, $voting_period) {
	$hour = $day = $month = $year = 0;
	
     switch($voting_period) {
		case "1h":
			$hour = 1;
			break;
		case "2h":
			$hour = 2;
			break;
		case "3h":
			$hour = 3;
			break;
		case "4h":
			$hour = 4;
			break;
		case "6h":
			$hour = 6;
			break;
		case "8h":
			$hour = 8;
			break;
		case "12h":
			$hour = 12;
			break;
		case "1":
			$day = 1;
			break;
		case "2":
			$day = 2;
			break;
		case "3":
			$day = 3;
			break;
		case "7":
			$day = 7;
			break;
		case "14":
			$day = 14;
			break;
		case "21":
			$day = 21;
			break;
		case "1m":
			$month = 1;
			break;
		case "2m":
			$month = 2;
			break;
		case "3m":
			$month = 3;
			break;
		case "6m":
			$month = 6;
			break;
		case "1y":
			$year = 1;
	       break;
     }
	
     $last_strtotime = strtotime($last_voted_date);
     $next_strtotime = mktime(date('H', $last_strtotime) + $hour, date('i', $last_strtotime), date('s', $last_strtotime),
			     date('m', $last_strtotime) + $month, date('d', $last_strtotime) + $day, date('Y', $last_strtotime) + $year);
     
     $next_vote_date = date('Y-m-d H:i:s', $next_strtotime);
     
     return $next_vote_date;
}

/**
 * Get last voted date as per voting period
 * @param $post_id integer
 * @return string
 */
function GetWtiLastDate($voting_period) {
	$hour = $day = $month = $year = 0;
	
     switch($voting_period) {
		case "1h":
			$hour = 1;
			break;
		case "2h":
			$hour = 2;
			break;
		case "3h":
			$hour = 3;
			break;
		case "4h":
			$hour = 4;
			break;
		case "6h":
			$hour = 6;
			break;
		case "8h":
			$hour = 8;
			break;
		case "12h":
			$hour = 12;
			break;
		case "1":
			$day = 1;
			break;
		case "2":
			$day = 2;
			break;
		case "3":
			$day = 3;
			break;
		case "7":
			$day = 7;
			break;
		case "14":
			$day = 14;
			break;
		case "21":
			$day = 21;
			break;
		case "1m":
			$month = 1;
			break;
		case "2m":
			$month = 2;
			break;
		case "3m":
			$month = 3;
			break;
		case "6m":
			$month = 6;
			break;
		case "1y":
			$year = 1;
	       break;
     }

     $last_strtotime = strtotime(date('Y-m-d H:i:s'));
     $last_strtotime = mktime(date('H', $last_strtotime) - $hour, date('i', $last_strtotime), date('s', $last_strtotime),
			     date('m', $last_strtotime) - $month, date('d', $last_strtotime) - $day, date('Y', $last_strtotime) - $year);
     
     $last_voting_date = date('Y-m-d H:i:s', $last_strtotime);
     
     return $last_voting_date;
}

/**
 * Update like meta data
 * @param void
 * @return integer
 */
function WtiUpdateLikeMetaData() {
	global $wpdb;
	$like_count = $unlike_count = $total_count = 0;
	$all_ids = array();

	// Update all like meta data which are already recorded
	$post_data = $wpdb->get_results("SELECT post_id, SUM(value) as like_count FROM {$wpdb->prefix}wti_like_post
							  WHERE value > 0 GROUP BY post_id");
	
	if(count($post_data) > 0) {
		foreach($post_data as $data) {
			if(update_post_meta($data->post_id, '_wti_like_count', $data->like_count)) {
				$like_count++;
			}
			
			$all_ids[] = $data->post_id;
		}
	}
	
	// Update all unlike meta data which are already recorded
	$post_data = $wpdb->get_results("SELECT post_id, SUM(value) as unlike_count FROM {$wpdb->prefix}wti_like_post
							  WHERE value < 0 GROUP BY post_id");
	
	if(count($post_data) > 0) {
		foreach($post_data as $data) {
			if(update_post_meta($data->post_id, '_wti_unlike_count', -($data->unlike_count))) {
				$unlike_count++;
			}
			
			$all_ids[] = $data->post_id;
		}
	}
	
	// Get all posts in the system
	$posts = get_posts(array( 'post_status' => array( 'publish', 'private', 'trash' )));
	if(count($posts) > 0) {
		foreach($posts as $p) {
			$all_ids[] = $p->ID;
		}
	}
	
	// Get all pages in the system
	$show_on_pages = get_option('wti_like_post_show_on_pages');
	if($show_on_pages == 1) {
		$pages = get_pages( array( 'post_status' => 'publish,private,trash' ) );
		if(count($pages) > 0) {
			foreach($pages as $p) {
				$all_ids[] = $p->ID;
			}
		}
	}
	
	// Update all like, unlike, total meta counts
	$all_ids = array_unique($all_ids);
	if(count($all_ids) >0) {
		foreach($all_ids as $id) {
			$like_value = get_post_meta($id, '_wti_like_count', true);
			$unlike_value = get_post_meta($id, '_wti_unlike_count', true);
			$total_value = (int)$like_value - (int)$unlike_value;
			
			if($like_value == false || $like_value == '') {
				update_post_meta($id, '_wti_like_count', 0);
				$like_count++;
			}
			
			if($unlike_value == false || $unlike_value == '') {
				update_post_meta($id, '_wti_unlike_count', 0);
				$unlike_count++;
			}
			
			if(update_post_meta($id, '_wti_total_count', $total_value)) {
				$total_count++;
			}
		}
	}
	
	return $like_count + $unlike_count + $total_count;
}

/**
 * Add like data in database
 * @param integer
 * @return boolean
 */
function WtiLikePostAddLikeData($post_id, $value, $user_id, $wti_ip_address, $cookie_value, $like_data = array())
{
	global $wpdb;
	$db_query = "INSERT INTO {$wpdb->prefix}wti_like_post SET ";
	$query = "INSERT INTO {$wpdb->prefix}wti_like_post SET ";
						$query .= "post_id = '" . $post_id . "', ";
						$query .= "value = '-1', ";
						$query .= "user_id = $user_id, ";
						$query .= "date_time = '" . date('Y-m-d H:i:s') . "', ";
						$query .= "ip = '$wti_ip_address', ";
						$query .= "cookie_value = '$cookie_value'";
	
	foreach($like_data as $key => $data)
	{
		$db_query .= "`$key` = '" . $data . "', ";
	}
	
	$db_query = "date_time = '" . date('Y-m-d H:i:s') . "'";
	
	$success = $wpdb->query($query);
	
	return $success;
}

/**
 * Get the actual ip address
 * @param no-param
 * @return string
 */
function WtiGetRealIpAddress()
{
	if (getenv('HTTP_CLIENT_IP')) {
		$wti_ip_address = getenv('HTTP_CLIENT_IP');
	} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
		$wti_ip_address = getenv('HTTP_X_FORWARDED_FOR');
	} elseif (getenv('HTTP_X_FORWARDED')) {
		$wti_ip_address = getenv('HTTP_X_FORWARDED');
	} elseif (getenv('HTTP_FORWARDED_FOR')) {
		$wti_ip_address = getenv('HTTP_FORWARDED_FOR');
	} elseif (getenv('HTTP_FORWARDED')) {
		$wti_ip_address = getenv('HTTP_FORWARDED');
	} else {
		$wti_ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	return $wti_ip_address;
}

/**
 * Integrating like/unlike activity with buddypress
 * @param integer
 * @param string
 * @param string
 * @return void
 */
function WtiLikePostAddBpActivity($user_id, $activity_action, $primary_link, $content)
{
	// Now write the values
	$activity_id = bp_activity_add(
					array(
						'user_id'      => $user_id,
						'action'       => apply_filters('bp_activity_new_update_action', $activity_action),
						'content'      => apply_filters('bp_activity_new_update_content', $content),
						'primary_link' => apply_filters('bp_activity_new_update_primary_link', $primary_link),
						'component'    => 'activity',
						'type'         => 'activity_update'
					)
				);
	
	// Add this update to the "latest update" usermeta so it can be fetched anywhere.
	bp_update_user_meta(bp_loggedin_user_id(), 'bp_latest_update', array('id' => $activity_id, 'content' => $content));
	
	do_action('bp_activity_posted_update', '', $user_id, $activity_id);
}

function wti_like_post_register_activity_actions() {
	// Your plugin is creating a custom BuddyPress component
	//$component_id = 'plugin_component_id';
	// You can also use one of the BuddyPress component
	
	if ( function_exists( 'bp_is_active' ) && bp_is_active( 'activity' ) ) {
		$component_id = buddypress()->activity->id;
		
		bp_activity_set_action(
			$component_id,
			'activity_wti_like',
			__( 'liked', 'wti-like-post' ),
			'bp_activity_format_activity_action_activity_wti_like',
			__( 'Likes', 'wti-like-post' ),
			array( 'activity' ),
			1
		);
		
		bp_activity_set_action(
			$component_id,
			'activity_wti_dislike',
			__( 'disliked', 'wti-like-post' ),
			'bp_activity_format_activity_action_activity_wti_dislike',
			__( 'Dislikes', 'wti-like-post' ),
			array( 'activity' ),
			2
		);
	}
}

//add_action( 'bp_register_activity_actions', 'wti_like_post_register_activity_actions' );

// Include the file for ajax calls
require_once('wti-like-post-ajax.php');

/**
 * Showing the users who liked/disliked the post
 */
function WtiLikePostShowLikeUsers()
{
	global $wpdb;

	// Get requested params
	$post_id = (int)$_REQUEST['post_id'];
	//$last_time = $_REQUEST['last_time'];
	$limit = isset( $_REQUEST['limit'] ) ? (int)$_REQUEST['limit'] : 20;

	// SQL where conditions
	$where = array();
	$where[] = "post_id = $post_id";
	$where[] = "value > 0";
	$where[] = "user_id > 0";
	
	if(!empty($_REQUEST['last_time'])) {
		$where[] = "date_time > '" . $_REQUEST['last_time'] . "'";
	}

	// Build where clause and get the result
	$where_clause = " WHERE " . implode(" AND ", $where);
	
	$query = "SELECT user_id, SUM(value) AS like_count FROM `{$wpdb->prefix}wti_like_post`
			$where_clause GROUP BY user_id ORDER BY like_count DESC, user_id ASC LIMIT $limit";
	$result = $wpdb->get_results($query);
	$counter = 0;
	
	if(count($result) > 0) {
		?>
		<div class="ui-sortable meta-box-sortables">
			<table cellspacing="0" class="wp-list-table widefat fixed likes" width="95%" align="center">
				<thead>
					<tr>
						<td width="5%">
							<strong><?php echo __('#', 'wti-like-post'); ?></strong>
						</td>
						<td width="8%">
							<strong><?php echo __('User Id', 'wti-like-post'); ?></strong>
						</td>
						<td width="30%">
							<strong><?php echo __('Name', 'wti-like-post'); ?></strong>
						</td>
						<td width="40%">
							<strong><?php echo __('Email', 'wti-like-post'); ?></strong>
						</td>
						<td>
							<strong><?php echo __('Count', 'wti-like-post'); ?></strong>
						</td>
					<tr>
				</thead>
				<tbody class="list:likes" id="the-list">
					<?php
					$nonce = wp_create_nonce("wti_like_post_clear_nonce");
					foreach($result as $row) {
						$user = get_userdata($row->user_id);
						?>
						<tr>
							<td>
								<?php echo ++$counter; ?>
							</td>
							<td>
								<?php echo $row->user_id; ?>
							</td>
							<td>
								<?php echo $user->display_name; ?>
							</td>
							<td>
								<a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a>
							</td>
							<td>
								<?php echo $row->like_count; ?>
							</td>
						<tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php
	} else {
		echo '<p>';
		echo __('No users voted yet. This will show the users only when logged in users are allowed to vote.', 'wti-like-post');
		echo '</p>';
	}
	
	exit;
}

/**
 * Clear the votes for a specific post and user
 */
function WtiLikePostClearVote()
{
	global $wpdb;
	
	$post_id = $_POST['post_id'];
	$user_id = $_POST['user_id'];
	
	$deleted = $wpdb->delete(
					"{$wpdb->prefix}wti_like_post",
					array(
						'post_id' => $post_id,
						'user_id' => $user_id
					),
					array( '%d', '%d' )
				);
	
	if ( $deleted === false ) {
		$result = array(
					'error' => 1,
					'msg' => __('Error!! Please try again', 'wti-like-post')
				);
	} else {
		$result = array(
					'error' => 0,
					'msg' => __('Cleared', 'wti-like-post')
				);
	}
	
	echo json_encode( $result );
	exit;
}

/**
 * Get like count for a specific category posts
 *
 * @param int
 * @param string
 *
 * @return int
 *
 */
function WtiGetCategoryTotalCount($cat_id, $show = 'all')
{
	global $wpdb;
	$wti_count = 0;
	$cat_posts = array();
	
	// Get posts for the category
	$category_posts = get_posts(array('category' => $cat_id));

	if(count($category_posts) > 0) {
		foreach($category_posts as $category_post) {
			$cat_posts[] = $category_post->ID;
		}
		
		// Get the count
		$query = "SELECT SUM(value) FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' ";
		$query .= "AND post_id IN(" . implode(",", $cat_posts) . ") ";
		
		// Check for type of vote
		if($show == 'like') {
			$query .= "AND L.value > 0";
		} elseif($show == 'unlike') {
			$query .= "AND L.value < 0";
		}
		
		$wti_count = $wpdb->get_var($query);
	}
	
	return (int)$wti_count;
}

// Associate the respective functions with the ajax call
add_action('wp_ajax_wti_like_post_show_like_users', 'WtiLikePostShowLikeUsers');
add_action('wp_ajax_wti_like_post_process_vote', 'WtiLikePostProcessVote');
add_action('wp_ajax_wti_like_post_clear_vote', 'WtiLikePostClearVote');
add_action('wp_ajax_nopriv_wti_like_post_process_vote', 'WtiLikePostProcessVote');
add_action('wp_ajax_wti_like_post_update_meta', 'WtiLikePostUpdateMeta');
add_action('wp_ajax_nopriv_wti_like_post_update_meta', 'WtiLikePostUpdateMeta');
?>