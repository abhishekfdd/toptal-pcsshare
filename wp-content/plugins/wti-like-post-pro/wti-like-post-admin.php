<?php
/**
 * Create the admin menu for this plugin
 * 
 * @param no-param
 * @return no-return
 */
function WtiLikePostAdminMenu() {
	// Adds a menu WTI Like Post PRO which shows the configuration settings
	add_menu_page( __( 'WTI Like Post PRO', 'wti-like-post' ), __( 'WTI Like Post PRO', 'wti-like-post' ), 'activate_plugins', 'WtiLikePostAdminMenu', 'WtiLikePostAdminContent', plugins_url( 'images/menu-icon.png' , __FILE__ ) );
	
	// Adds a submenu Most Liked Posts which shows a listing of most liked posts and their like counts
	add_submenu_page( 'WtiLikePostAdminMenu', __( 'Most Liked Posts', 'wti-like-post' ), __( 'Most Liked Posts', 'wti-like-post' ), 'activate_plugins', 'wtilp-most-liked-posts', 'WtiLikePostAdminShowLikedPosts' );
	
	// Adds a submenu Features & Support to show the features of this PRO version and the different ways for the support
	add_submenu_page( 'WtiLikePostAdminMenu', __( 'Features & Support', 'wti-like-post' ), __( 'Features & Support', 'wti-like-post' ), 'activate_plugins', 'wtilp-features-support', 'WtiLikePostAdminFeaturesSupport' );
}

add_action('admin_menu', 'WtiLikePostAdminMenu');

/**
 * Get the voting style options
 * 
 * @param string
 * @return string
 */
function WtiLikePostGetStyleOptions($voting_style) {
	// Voting style options
	$style_options_array = array(
						'style1' => __('Style1', 'wti-like-post'),
						'style2' => __('Style2', 'wti-like-post'),
						'style3' => __('Style3', 'wti-like-post'),
						'style4' => __('Style4', 'wti-like-post'),
						'style5' => __('Style5', 'wti-like-post'),
						'style6' => __('Style6', 'wti-like-post')
					);
	$style_options = '';
	
	foreach ( $style_options_array as $style_key => $style_text ) {
		$style_options .= '<option value="' . $style_key . '" ';
		$style_options .= ($style_key == $voting_style) ? 'selected="selected">' : '>';
		$style_options .= $style_text . '</option>';
	}
	
	return apply_filters( 'wti_like_post_style_options', $style_options, $voting_style );
}

/**
 * Pluing settings page
 * 
 * @param no-param
 * @return no-return
 */
function WtiLikePostAdminContent() {
     // Creating the admin configuration interface
     global $wpdb, $wti_like_post_db_version;
     
	$excluded_sections = get_option( 'wti_like_post_excluded_sections' );
	$excluded_categories = get_option( 'wti_like_post_excluded_categories' );
	$allowed_categories = get_option( 'wti_like_post_allowed_categories' );
	
	if ( empty( $excluded_sections ) ) {
		$excluded_sections = array();
	}
	
	if ( empty( $excluded_categories ) ) {
		$excluded_categories = array();
	}
	
	if ( empty( $allowed_categories ) ) {
		$allowed_categories = array();
	}
	?>
	<div class="wrap">
		<h2><?php echo __( 'WTI Like Post PRO', 'wti-like-post' ) . ' ' . $wti_like_post_db_version; ?></h2>
		<br class="clear" />

		<?php
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			?>
			<div class="updated below-h2" id="message">
				<p><strong><?php echo __( 'Plugin settings updated successfully.', 'wti-like-post' ); ?></strong></p>
			</div>
			<?php
		}
		?>
		
		<div class="metabox-holder" id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<div id="WtiLikePostOptions" class="postbox">
						<h3><?php echo __( 'Configuration Settings', 'wti-like-post' ); ?></h3>
						
						<div class="inside">
							<form method="post" action="options.php">
								<?php settings_fields('wti_like_post_options'); ?>
								<table class="form-table">
									<tr valign="top">
										<th scope="row"><label for="drop_settings_table_no"><?php _e('Remove plugin settings and table on plugin un-install', 'wti-like-post'); ?></label></th>
										<td>
											<input type="radio" name="wti_like_post_drop_settings_table" id="drop_settings_table_yes" value="1" <?php if (1 == get_option('wti_like_post_drop_settings_table')) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_drop_settings_table" id="drop_settings_table_no" value="0" <?php if ((0 == get_option('wti_like_post_drop_settings_table')) || ('' == get_option('wti_like_post_drop_settings_table'))) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Setting this to NO is helpful if you are planning to reuse this in future with old data.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Use plugin css file', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_use_plugin_css" id="use_plugin_css_yes" value="1" <?php if(1 == get_option('wti_like_post_use_plugin_css') || ('' == get_option('wti_like_post_use_plugin_css'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_use_plugin_css" id="use_plugin_css_no" value="0" <?php if(0 == get_option('wti_like_post_use_plugin_css')) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('This option is useful if you want to control the plugin layout from your theme. By setting this to No, your custom css code will be used and will not be overwritten by the plugin css.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Disable automatic loading of buttons for post content', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_disable_auto_load" id="auto_load_yes" value="1" <?php if(1 == get_option('wti_like_post_disable_auto_load')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_disable_auto_load" id="auto_load_no" value="0" <?php if((0 == get_option('wti_like_post_disable_auto_load')) || ('' == get_option('wti_like_post_disable_auto_load'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('This option is useful if you want to use the template tag GetWtiLikePost() instead of the like/unlike buttons which loads automatically.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Load buttons for post excerpt', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_use_on_excerpt" id="use_on_excerpt_yes" value="1" <?php if(1 == get_option('wti_like_post_use_on_excerpt')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_use_on_excerpt" id="use_on_excerpt_no" value="0" <?php if((0 == get_option('wti_like_post_use_on_excerpt')) || ('' == get_option('wti_like_post_use_on_excerpt'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('This option is useful mainly on home/blog page where you show post excerpts. You must have excerpts added to the post.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Voting Style', 'wti-like-post'); ?></label></th>
										<td>
											<?php
											$voting_style = get_option('wti_like_post_voting_style');
											//$style_options = WtiLikePostGetStyleOptions( $voting_style );
											/*
											?>
											<select name="wti_like_post_voting_style" id="wti_like_post_voting_style">
												<option value="style1" <?php if("style1" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style1', 'wti-like-post'); ?></option>
												<option value="style2" <?php if("style2" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style2', 'wti-like-post'); ?></option>
												<option value="style3" <?php if("style3" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style3', 'wti-like-post'); ?></option>
												<option value="style4" <?php if("style4" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style4', 'wti-like-post'); ?></option>
												<option value="style5" <?php if("style5" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style5', 'wti-like-post'); ?></option>
												<option value="style6" <?php if("style6" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style6', 'wti-like-post'); ?></option>
											</select>
											<?php
											*/
											?>
											<select name="wti_like_post_voting_style" id="wti_like_post_voting_style">
												<?php echo WtiLikePostGetStyleOptions( $voting_style ); ?>
											</select>
											<span class="description"><?php _e('Select the voting style from 6 available options with 6 different sets of images.', 'wti-like-post'); ?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Voting Period', 'wti-like-post'); ?></label></th>
										<td>
											<?php
											$voting_period = get_option('wti_like_post_voting_period');
											?>
											<select name="wti_like_post_voting_period" id="wti_like_post_voting_period">
												<option value="0"><?php echo __('Always can vote', 'wti-like-post'); ?></option>
												<option value="once" <?php if ("once" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Only once', 'wti-like-post'); ?></option>												
												<option value="1h" <?php if ("1h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One hour', 'wti-like-post'); ?></option>
												<option value="2h" <?php if ("2h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two hours', 'wti-like-post'); ?></option>
												<option value="3h" <?php if ("3h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three hours', 'wti-like-post'); ?></option>												
												<option value="4h" <?php if ("4h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Four hours', 'wti-like-post'); ?></option>
												<option value="6h" <?php if ("6h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Six hours', 'wti-like-post'); ?></option>
												<option value="8h" <?php if ("8h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Eight hours', 'wti-like-post'); ?></option>
												<option value="12h" <?php if ("12h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Twelve hours', 'wti-like-post'); ?></option>
												<option value="1" <?php if ("1" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One day', 'wti-like-post'); ?></option>
												<option value="2" <?php if ("2" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two days', 'wti-like-post'); ?></option>
												<option value="3" <?php if ("3" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three days', 'wti-like-post'); ?></option>
												<option value="7" <?php if ("7" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One week', 'wti-like-post'); ?></option>
												<option value="14" <?php if ("14" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two weeks', 'wti-like-post'); ?></option>
												<option value="21" <?php if ("21" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three weeks', 'wti-like-post'); ?></option>
												<option value="1m" <?php if ("1m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One month', 'wti-like-post'); ?></option>
												<option value="2m" <?php if ("2m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two months', 'wti-like-post'); ?></option>
												<option value="3m" <?php if ("3m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three months', 'wti-like-post'); ?></option>
												<option value="6m" <?php if ("6m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Six Months', 'wti-like-post'); ?></option>
												<option value="1y" <?php if ("1y" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One Year', 'wti-like-post'); ?></option>
											</select>
											<span class="description"><?php _e('Select the voting period after which user can vote again.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Check revoting with', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_check_option" id="check_option_ip" value="0" <?php if((0 == get_option('wti_like_post_check_option')) || ('' == get_option('wti_like_post_check_option'))) { echo 'checked="checked"'; } ?> /> <?php echo __('IP Address', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_check_option" id="check_option_user_id" value="1" <?php if(1 == get_option('wti_like_post_check_option')) { echo 'checked="checked"'; } ?> /> <?php echo __('User Id', 'wti-like-post'); ?>
											<?php /*<input type="radio" name="wti_like_post_check_option" id="check_option_cookies" value="2" <?php if(2 == get_option('wti_like_post_check_option')) { echo 'checked="checked"'; } ?> /> <?php echo __('Cookies', 'wti-like-post'); */?>
											<span class="description"><?php _e('This option is useful to cross check user revoting a post/page and you have restricted revoting with specific voting period.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Login required to vote', 'wti-like-post'); ?></label></th>
										<td>
											<input type="radio" name="wti_like_post_login_required" id="login_yes" value="1" <?php if(1 == get_option('wti_like_post_login_required')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_login_required" id="login_no" value="0" <?php if((0 == get_option('wti_like_post_login_required')) || ('' == get_option('wti_like_post_login_required'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select whether only logged in users can vote or not. If you check revoting with user id, then it must be set to yes.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Login required message', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_login_message" id="wti_like_post_login_message" value="<?php echo get_option('wti_like_post_login_message'); ?>" />
											<span class="description"><?php _e('Message to show in case login required and user is not logged in.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Default message', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_default_message" id="wti_like_post_default_message" value="<?php echo get_option('wti_like_post_default_message'); ?>" />
											<span class="description"><?php _e('Message to show when there has not been any vote for the post. You can put some encouraging message so users will vote.', 'wti-like-post');?></span>
										</td>
									</tr>			
									<tr valign="top">
										<th scope="row"><label><?php _e('Thank you message', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_thank_message" id="wti_like_post_thank_message" value="<?php echo get_option('wti_like_post_thank_message'); ?>" />
											<span class="description"><?php _e('Message to show after successful voting.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Already voted message', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_voted_message" id="wti_like_post_voted_message" value="<?php echo get_option('wti_like_post_voted_message'); ?>" />
											<span class="description"><?php _e('Message to show if user has already voted. This will also be shown when user tries to vote again with multiple voting not allowed.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Disallow author to like/unlike own post', 'wti-like-post') . get_option('wti_like_post_disallow_author_voting'); ?></label></th>
										<td>
											<input type="radio" name="wti_like_post_disallow_author_voting" id="disallow_author_voting_yes" value="1" <?php if((1 == get_option('wti_like_post_disallow_author_voting')) || ('' == get_option('wti_like_post_disallow_author_voting'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_disallow_author_voting" id="disallow_author_voting_no" value="0" <?php if(0 == get_option('wti_like_post_disallow_author_voting')) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select whether to disallow author to like/unlike own post. User must be logged in for this cross-checking.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Disallow message', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_author_disallowed_message" id="wti_like_post_author_disallowed_message" value="<?php echo get_option('wti_like_post_author_disallowed_message'); ?>" />
											<span class="description"><?php _e('Message to show when author tries to like/unlike own post.', 'wti-like-post');?></span>
										</td>
									</tr>
									<?php
									/*
									<tr valign="top">
										<th scope="row"><label><?php _e('Default like count range', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="5" name="wti_like_post_min_like" id="wti_like_post_min_like" value="<?php echo get_option('wti_like_post_min_like'); ?>" />
											&nbsp;<?php _e('to', 'wti-like-post'); ?>&nbsp;
											<input type="text" size="5" name="wti_like_post_max_like" id="wti_like_post_max_like" value="<?php echo get_option('wti_like_post_max_like'); ?>" />
											<span class="description"><?php _e('If you want to assign predefined like counts to new posts, then enter the minimum and maximum values.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Apply to existing content', 'wti-like-post'); ?></label></th>
										<td>
											<input type="radio" name="wti_like_post_apply_existing" id="wti_like_post_apply_yes" value="1" <?php if(1 == get_option('wti_like_post_apply_existing')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_apply_existing" id="wti_like_post_apply_no" value="0" <?php if((0 == get_option('wti_like_post_apply_existing')) || ('' == get_option('wti_like_post_apply_existing'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select whether the above default like counts will be assigned to those existing posts which have no like and dislike counts.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Post types to use', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="120" name="wti_like_post_post_types" id="wti_like_post_post_types" value="<?php echo get_option('wti_like_post_post_types'); ?>" />
											<span class="description"><?php _e('Please enter comma separated values. This is useful when there are different post types used and there is need to show the like/unlike buttons on selected post types only.', 'wti-like-post');?></span>
										</td>
									</tr>
									*/
									?>
									<tr valign="top">
										<th scope="row"><label><?php _e('Post types to use', 'wti-like-post'); ?></label></th>
										<td>
											<input type="text" name="wti_like_post_post_types" id="wti_like_post_post_types" value="<?php echo get_option('wti_like_post_post_types'); ?>" />
											<span class="description"><?php _e('Please enter comma separated values. This is useful when there are different post types used and there is need to show the like/unlike buttons on selected post types only.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Integrate with buddypress activity', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_bp_like_activity" id="wti_like_post_bp_like_no" value="0" <?php if('0' == get_option('wti_like_post_bp_like_activity') || ('' == get_option('wti_like_post_bp_like_activity'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_bp_like_activity" id="wti_like_post_bp_like_yes" value="1" <?php if(('1' == get_option('wti_like_post_bp_like_activity'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes, Like only', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_bp_like_activity" id="wti_like_post_bp_dislike_yes" value="2" <?php if(('2' == get_option('wti_like_post_bp_like_activity'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes, Dislike only', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_bp_like_activity" id="wti_like_post_bp_like_dislike_yes" value="3" <?php if(('3' == get_option('wti_like_post_bp_like_activity'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes, Like and Dislike both', 'wti-like-post'); ?>
											<span class="description"><?php _e('It will show a liked/disliked activity message on buddypress actvity page. Buddypress must have been installed with Activity Streams component enabled and user must be logged in for this to work.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Show on pages', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_on_pages" id="show_pages_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_on_pages'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_on_pages" id="show_pages_no" value="0" <?php if('0' == get_option('wti_like_post_show_on_pages') || ('' == get_option('wti_like_post_show_on_pages'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select yes if you want to show the like option on pages as well.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Exclude on selected sections', 'wti-like-post'); ?></label></th>
										<td>
											<input type="checkbox" name="wti_like_post_excluded_sections[]" id="wti_like_post_excluded_home" value="home" <?php if(in_array('home', $excluded_sections)) { echo 'checked'; } ?> class="excluded_sections" /> <?php echo __('Home', 'wti-like-post'); ?>
											<input type="checkbox" name="wti_like_post_excluded_sections[]" id="wti_like_post_excluded_archive" value="archive" <?php if(in_array('archive', $excluded_sections)) { echo 'checked'; } ?> class="excluded_sections" /> <?php echo __('Archive', 'wti-like-post'); ?>
											<input type="checkbox" name="wti_like_post_excluded_sections[]" id="wti_like_post_excluded_search" value="search" <?php if(in_array('search', $excluded_sections)) { echo 'checked'; } ?> class="excluded_sections" /> <?php echo __('Search', 'wti-like-post'); ?>
											<span class="description"><?php _e('Check the sections where you do not want to avail the like/dislike options. This has higher priority than the "Exclude post/page IDs" setting.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Allow selected categories', 'wti-like-post'); ?></label></th>
										<td>	
											<select name='wti_like_post_allowed_categories[]' id='wti_like_post_allowed_categories' multiple="multiple" size="4" style="height:auto !important;">
												<?php 
												$categories = get_categories(array('category', 'taxonomy'));
												
												foreach ($categories as $category) {
													$selected = (in_array($category->cat_ID, $allowed_categories)) ? 'selected="selected"' : '';
													$option  = '<option value="' . $category->cat_ID . '" ' . $selected . '>';
													$option .= $category->cat_name;
													$option .= ' (' . $category->category_count . ')';
													$option .= '</option>';
													echo $option;
												}
												?>
											</select>
											<span class="description"><?php _e('Select categories where you want to show the like/dislike option. This will be used only when nothing is selected from Exclude selected categories option.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Exclude selected categories', 'wti-like-post'); ?></label></th>
										<td>	
											<select name='wti_like_post_excluded_categories[]' id='wti_like_post_excluded_categories' multiple="multiple" size="4" style="height:auto !important;">
												<?php 
												$categories = get_categories(array('category', 'taxonomy'));
												
												foreach ($categories as $category) {
													$selected = (in_array($category->cat_ID, $excluded_categories)) ? 'selected="selected"' : '';
													$option  = '<option value="' . $category->cat_ID . '" ' . $selected . '>';
													$option .= $category->cat_name;
													$option .= ' (' . $category->category_count . ')';
													$option .= '</option>';
													echo $option;
												}
												?>
											</select>
											<span class="description"><?php _e('Select categories where you do not want to show the like option. It has higher priority than "Exclude post/page IDs" setting.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Allow post IDs', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_allowed_posts" id="wti_like_post_allowed_posts" value="<?php _e(get_option('wti_like_post_allowed_posts')); ?>" />
											<span class="description"><?php _e('Suppose you have a post which belongs to more than one categories and you have excluded one of those categories. So the like/dislike will not be available for that post. Enter comma separated those post ids where you want to show the like/dislike option irrespective of that post category being excluded.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Exclude post/page IDs', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="text" size="40" name="wti_like_post_excluded_posts" id="wti_like_post_excluded_posts" value="<?php _e(get_option('wti_like_post_excluded_posts')); ?>" />
											<span class="description"><?php _e('Enter comma separated post/page ids where you do not want to show the like option. If Show on pages setting is set to Yes but you have added the page id here, then like option will not be shown for the same page.', 'wti-like-post');?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Show excluded posts/pages on widget', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_on_widget" id="show_widget_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_on_widget')) || ('' == get_option('wti_like_post_show_on_widget'))) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_on_widget" id="show_widget_no" value="0" <?php if('0' == get_option('wti_like_post_show_on_widget')) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select yes if you want to show the excluded posts/pages on widget.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Position Setting', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_position" id="position_top" value="top" <?php if(('top' == get_option('wti_like_post_position')) || ('' == get_option('wti_like_post_position'))) { echo 'checked'; } ?> /> <?php echo __('Top of Content', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_position" id="position_bottom" value="bottom" <?php if('bottom' == get_option('wti_like_post_position')) { echo 'checked'; } ?> /> <?php echo __('Bottom of Content', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select the position where you want to show the like options. You can place it anywhere inside content by using the shortcode [wtilikepost] irrespective of the position selected.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Alignment Setting', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_alignment" id="alignment_left" value="left" <?php if(('left' == get_option('wti_like_post_alignment')) || ('' == get_option('wti_like_post_alignment'))) { echo 'checked'; } ?> /> <?php echo __('Left', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_alignment" id="alignment_right" value="right" <?php if('right' == get_option('wti_like_post_alignment')) { echo 'checked="checked"'; } ?> /> <?php echo __('Right', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select the alignment whether to show on left or on right.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Redirect Url', 'wti-like-post'); ?></label></th>
										<td>
											<input type="text" name="wti_like_post_redirect_url" id="wti_like_post_redirect_url" value="<?php echo get_option('wti_like_post_redirect_url')?>" size="40" />
											<span class="description"><?php echo __('Provide the full url only when you want the user to be redirected on successful voting.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Title text for like/unlike images', 'wti-like-post'); ?></label></th>
										<td>
											<input type="text" name="wti_like_post_title_text" id="wti_like_post_title_text" value="<?php echo get_option('wti_like_post_title_text')?>" />
											<span class="description"><?php echo __('Enter both texts separated by "/" to show when user puts mouse over like/unlike images.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Use like/unlike text', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_like_unlike_text" id="show_like_unlike_text_yes" value="1" <?php if(1 == get_option('wti_like_post_show_like_unlike_text')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_like_unlike_text" id="show_like_unlike_text_no" value="0" <?php if(0 == get_option('wti_like_post_show_like_unlike_text') || ('' == get_option('wti_like_post_show_like_unlike_text'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php echo __('Select whether you want to show like/unlike text instead of images. If yes, then enter the respective texts in the fields below.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Text to use instead of like image', 'wti-like-post'); ?></label></th>
										<td>
											<input type="text" name="wti_like_post_like_text" id="wti_like_post_like_text" value="<?php echo get_option('wti_like_post_like_text')?>" />
											<span class="description"><?php echo __('Enter the text to show if you do not want to use like image.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Text to use instead of unlike image', 'wti-like-post'); ?></label></th>
										<td>
											<input type="text" name="wti_like_post_unlike_text" id="wti_like_post_unlike_text" value="<?php echo get_option('wti_like_post_unlike_text')?>" />
											<span class="description"><?php echo __('Enter the text to show if you do not want to use unlike image.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Show dislike option', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_dislike" id="show_dislike_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_dislike')) || ('' == get_option('wti_like_post_show_dislike'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_dislike" id="show_dislike_no" value="0" <?php if('0' == get_option('wti_like_post_show_dislike')) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select the option whether to show or hide the dislike option.', 'wti-like-post')?></span>
										</td>
									</tr>	
									<tr valign="top">
										<th scope="row"><label><?php _e('Show +/- symbols', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_symbols" id="show_symbol_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_symbols')) || ('' == get_option('wti_like_post_show_symbols'))) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_symbols" id="show_symbol_no" value="0" <?php if('0' == get_option('wti_like_post_show_symbols')) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<span class="description"><?php _e('Select the option whether to show or hide the plus or minus symbols before like/unlike count.', 'wti-like-post')?></span>
										</td>
									</tr>
									<tr valign="top">
										<th scope="row"><label><?php _e('Show user likes', 'wti-like-post'); ?></label></th>
										<td>	
											<input type="radio" name="wti_like_post_show_user_likes" id="show_user_likes_comma" value="1" <?php if(1 == get_option('wti_like_post_show_user_likes')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes, comma separated', 'wti-like-post'); ?>
											<input type="radio" name="wti_like_post_show_user_likes" id="show_user_likes_no" value="0" <?php if((0 == get_option('wti_like_post_show_user_likes')) || ('' == get_option('wti_like_post_show_user_likes'))) { echo 'checked="checked"'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
											<?php /*<input type="radio" name="wti_like_post_show_user_likes" id="show_user_likes_line" value="2" <?php if(2 == get_option('wti_like_post_show_user_likes')) { echo 'checked="checked"'; } ?> /> <?php echo __('Yes, in new lines', 'wti-like-post'); ?> */?>
											<span class="description"><?php _e('Select the option whether to show or hide the users who like the posts. This will work if only registered users are allowed to vote and we have records of those users. Since this is implemented in this pro version, old user votings will not reflect here. This will work only for future votings.', 'wti-like-post')?></span>
										</td>
									</tr>	
									<tr valign="top">
										<th scope="row"></th>
										<td>
											<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'wti-like-post'); ?>" />
											<input class="button-secondary" type="submit" name="Reset" value="<?php _e('Reset Options', 'wti-like-post'); ?>" onclick="return confirmReset()" />
											<input class="button-secondary" type="button" name="Update" id="update_post_meta" value="<?php _e('Update Like Meta', 'wti-like-post'); ?>" />
											<span id="update_like_text"></span>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>	
		<script>
		/**
		 *Plugin settings reset option
		 *
		 *@param no-param
		 *@return boolean
		 */
		function confirmReset()
		{
			//check whether user agrees to reset the settings to default or not
			var check = confirm("<?php echo __('Are you sure to reset the options to default settings?', 'wti-like-post')?>");
			
			if(check)
			{
				//reset the settings
				jQuery('#drop_settings_table_no').attr('checked', 'checked');
				jQuery('#use_plugin_css_yes').attr('checked', 'checked');
				jQuery('#auto_load_no').attr('checked', 'checked');
				jQuery('#use_on_excerpt_yes').attr('checked', 'checked');
				jQuery('#wti_like_post_voting_period').val(0);
				jQuery('#wti_like_post_voting_style').val('style1');
				jQuery('#check_option_ip').attr('checked', 'checked');
				jQuery('#login_no').attr('checked', 'checked');
				jQuery('#wti_like_post_default_message').val('<?php echo __('Be the 1st to vote.', 'wti-like-post')?>');
				jQuery('#wti_like_post_login_message').val('<?php echo __('Please login to vote.', 'wti-like-post')?>');
				jQuery('#wti_like_post_thank_message').val('<?php echo __('Thanks for your vote.', 'wti-like-post')?>');
				jQuery('#wti_like_post_voted_message').val('<?php echo __('You have already voted.', 'wti-like-post')?>');
				//jQuery('#wti_like_post_min_like').val('');
				//jQuery('#wti_like_post_max_like').val('');
				jQuery('#wti_like_post_apply_no').attr('checked', 'checked');
				jQuery('#wti_like_post_post_types').val('post');
				jQuery('#wti_like_post_bp_like_no').attr('checked', 'checked');
				jQuery('#disallow_author_voting_yes').attr('checked', 'checked');
				jQuery('#wti_like_post_author_disallowed_message').val('<?php echo __('You can not vote your own post.', 'wti-like-post')?>');
				jQuery('#wti_like_post_allowed_posts').val('');
				jQuery('#wti_like_post_excluded_posts').val('');
				jQuery('#wti_like_post_excluded_categories').val('');
				jQuery('#wti_like_post_allowed_categories').val('');
				jQuery('input.excluded_sections').removeAttr('checked');
				jQuery('#show_widget_yes').attr('checked', 'checked');
				jQuery('#position_bottom').attr('checked', 'checked');
				jQuery('#alignment_left').attr('checked', 'checked');
				jQuery('#show_symbol_yes').attr('checked', 'checked');
				jQuery('#show_dislike_yes').attr('checked', 'checked');
				jQuery('#show_user_likes_no').attr('checked', 'checked');
				jQuery('#show_like_unlike_text_no').attr('checked', 'checked');
				jQuery('#wti_like_post_redirect_url').val('');
				jQuery('#wti_like_post_title_text').val('<?php echo __('Like/Unlike', 'wti-like-post')?>');
				jQuery('#wti_like_post_like_text').val('<?php echo __('Click to like it', 'wti-like-post')?>');
				jQuery('#wti_like_post_unlike_text').val('<?php echo __('Click to unlike it', 'wti-like-post')?>');		
				//jQuery('input.selected_types').removeAttr('checked');
				
				return true;
			}
			
			return false;
		}
		
		// Update the like meta contents
		jQuery(document).ready(function(){
			jQuery('#update_post_meta').click(function(){
				jQuery("#update_like_text").text("<?php echo __('Updating...', 'wti-like-post')?>");
				jQuery.ajax({
					type: "POST",
					url: "<?php echo admin_url('admin-ajax.php?action=wti_like_post_update_meta&nonce=' . wp_create_nonce("wti_like_post_update_nonce"))?>",
					data: "num=" + Math.random(),
					success: function(data){
						jQuery("#update_like_text").text(data.msg);
					},
					dataType: "json"
				});
			});
		});
		</script>
	</div>
	<?php
}

function WtiLikePostAdminShowLikedPosts() {
	global $wpdb;
	?>
	<div class="wrap">
		<h2><?php echo __( 'Most Liked Posts', 'wti-like-post' ); ?></h2>
		<br class="clear" />
		
		<script type="text/javascript">
		/**
		 * Reset all like counts
		 * 
		 * @param no-param
		 * @return boolean
		 */
		function processAll()
		{
			return confirm('<?php echo __('Are you sure to reset all the counts present in the database?', 'wti-like-post')?>');
		}
		
		/**
		 * Reset selected like counts
		 * 
		 * @param no-param
		 * @return boolean
		 */
		function processSelected()
		{
			return confirm('<?php echo __('Are you sure to reset selected counts present in the database?', 'wti-like-post')?>');
		}
		</script>
		
		<?php
		if ( isset( $_POST['resetall'] ) ) {
			// Truncate the table
			$status = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wti_like_post" );
	
			if ( $status ) {
				$all_ids = array();
				$posts = get_posts(
								array(
									'post_status' => array( 'publish', 'private', 'trash' )
								)
							);
				
				if ( count( $posts ) > 0 ) {
					foreach ( $posts as $p ) {
						$all_ids[] = $p->ID;
					}
				}
				
				$show_on_pages = get_option('wti_like_post_show_on_pages');
				
				if ( $show_on_pages == 1 ) {
					$pages = get_pages(
									array( 'post_status' => 'publish,private,trash' )
								);
					
					if ( count( $pages ) > 0 ) {
						foreach ( $pages as $p ) {
							$all_ids[] = $p->ID;
						}
					}
				}
				
				if ( count( $all_ids ) > 0 ) {
					foreach ( $all_ids as $id ) {
						update_post_meta( $id, '_wti_like_count', 0 );
						update_post_meta( $id, '_wti_unlike_count', 0 );
						update_post_meta( $id, '_wti_total_count', 0 );
						update_post_meta( $id, '_wti_last_voted_time', '' );
						
						// Destroy the cookie
						//@setcookie("wtilp_count_{$id}", 0, time() - 3600);
					}
				}
				
				echo '<div class="updated below-h2" id="message"><p><strong>';
				echo __( 'All counts have been reset successfully.', 'wti-like-post' );
				echo '</strong></p></div>';
			} else {
				echo '<div class="error below-h2" id="error"><p><strong>';
				echo __( 'All counts could not be reset.', 'wti-like-post' );
				echo '</strong></p></div>';
			}
		}
		
		if ( isset( $_POST['resetselected'] ) ) {
			if ( count( $_POST['post_ids'] ) > 0 ) {
				$post_ids = implode( ",", $_POST['post_ids'] );
				$status = $wpdb->query( "DELETE FROM {$wpdb->prefix}wti_like_post WHERE post_id IN ($post_ids)" );
				
				if ( $status ) {
					// Reset like count post meta to 0
					foreach ( $_POST['post_ids'] as $post_id ) {
						update_post_meta( $post_id, '_wti_like_count', 0 );
						update_post_meta( $post_id, '_wti_unlike_count', 0 );
						update_post_meta( $post_id, '_wti_total_count', 0 );
						update_post_meta( $post_id, '_wti_last_voted_time', '' );
						
						// Destroy the cookie
						//@setcookie("wtilp_count_{$post_id}", 0, time() - 3600);
					}
					
					echo '<div class="updated below-h2" id="message"><p><strong>';
					
					if ( $status > 1 ) {
						echo $status . ' ' . __( 'counts have been reset successfully.', 'wti-like-post' );
					} else {
						echo $status . ' ' . __( 'count has been reset successfully.', 'wti-like-post' );
					}
					
					echo '</strong></p></div>';
				} else {
					echo '<div class="error below-h2" id="error"><p><strong>';
					echo __( 'Selected counts could not be reset.', 'wti-like-post' );
					echo '</strong></p></div>';
				}
			} else {
				echo '<div class="error below-h2" id="error"><p><strong>';
				echo __( 'Please select posts to reset count.', 'wti-like-post' );
				echo '</strong></p></div>';
			}
		}
		?>
		
		<div id="poststuff" class="ui-sortable meta-box-sortables">
			<?php
			// Load the js and css file for the thickbox
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );

			$where = array();
			$vperiod = '';
			
			if ( isset( $_REQUEST['vp'] ) && !empty( $_REQUEST['vp'] ) && $_REQUEST['vp'] != 'all' ) {
				// Filter params
				$vperiod = $_REQUEST['vp'];
				$last_vote_time = GetWtiLastDate($vperiod);
				
				$where[] = "date_time > '$last_vote_time'";
			}

			// Build where clause
			$where_clause = empty($where) ? "" : " WHERE " . implode(" AND ", $where);
			
			// Getting the most liked posts
			$query = "SELECT COUNT(DISTINCT(post_id)) AS total FROM `{$wpdb->prefix}wti_like_post`" . $where_clause;
			$post_count = $wpdb->get_var($query);
		
			if ( $post_count > 0 ) {
				// Pagination script
				$limit = get_option( 'posts_per_page' );
				
				if ( isset( $_GET['paged'] ) ) {
					$current = max( 1, $_GET['paged'] );
				} else {
					$current = 1;
				}
				
				$total_pages = ceil($post_count / $limit);
				$start = $current * $limit - $limit;
				$nonce = wp_create_nonce( "wti_like_post_vote_nonce" );
				
				$query = "SELECT post_id, SUM(value) AS like_count FROM `{$wpdb->prefix}wti_like_post` $where_clause
						GROUP BY post_id ORDER BY like_count DESC LIMIT $start, $limit";
				$result = $wpdb->get_results($query);
				?>
				<form method="post" action="<?php echo admin_url( 'admin.php?page=wtilp-most-liked-posts' )?>" name="most_liked_posts" id="most_liked_posts">
					<div style="float:left">
						<input class="button-secondary" type="submit" name="resetall" id="resetall" onclick="return processAll()" value="<?php echo __('Reset All Counts', 'wti-like-post')?>" />
						<input class="button-secondary" type="submit" name="resetselected" id="resetselected" onclick="return processSelected()" value="<?php echo __('Reset Selected Counts', 'wti-like-post')?>" />
						
						<span>Filter by </span>
						<select name="vp" id="voting_period">
							<option value="all" <?php if ("all" == $vperiod) echo "selected='selected'"; ?>><?php echo __('All time', 'wti-like-post'); ?></option>
							<option value="1h" <?php if ("1h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last one hour', 'wti-like-post'); ?></option>
							<option value="2h" <?php if ("2h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last two hours', 'wti-like-post'); ?></option>
							<option value="3h" <?php if ("3h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last three hours', 'wti-like-post'); ?></option>												
							<option value="4h" <?php if ("4h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last four hours', 'wti-like-post'); ?></option>
							<option value="6h" <?php if ("6h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last six hours', 'wti-like-post'); ?></option>
							<option value="8h" <?php if ("8h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last eight hours', 'wti-like-post'); ?></option>
							<option value="12h" <?php if ("12h" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last twelve hours', 'wti-like-post'); ?></option>
							<option value="1" <?php if ("1" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last one day', 'wti-like-post'); ?></option>
							<option value="2" <?php if ("2" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last two days', 'wti-like-post'); ?></option>
							<option value="3" <?php if ("3" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last three days', 'wti-like-post'); ?></option>
							<option value="7" <?php if ("7" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last one week', 'wti-like-post'); ?></option>
							<option value="14" <?php if ("14" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last two weeks', 'wti-like-post'); ?></option>
							<option value="21" <?php if ("21" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last three weeks', 'wti-like-post'); ?></option>
							<option value="1m" <?php if ("1m" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last one month', 'wti-like-post'); ?></option>
							<option value="2m" <?php if ("2m" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last two months', 'wti-like-post'); ?></option>
							<option value="3m" <?php if ("3m" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last three months', 'wti-like-post'); ?></option>
							<option value="6m" <?php if ("6m" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last six Months', 'wti-like-post'); ?></option>
							<option value="1y" <?php if ("1y" == $vperiod) echo "selected='selected'"; ?>><?php echo __('Last one Year', 'wti-like-post'); ?></option>
						</select>
						
						<input class="button-primary" type="submit" name="filterall" id="filterall" value="<?php echo __( 'Submit', 'wti-like-post' )?>" />
					</div>
					<div style="float:right">
						<div class="tablenav top">
							<div class="tablenav-pages">
								<span class="displaying-num"><?php echo $post_count?> <?php echo __( 'items', 'wti-like-post' ); ?></span>
								<?php
								echo paginate_links(
											array(
												'current' 	=> $current,
												'prev_text'	=> '&laquo; ' . __( 'Prev', 'wti-like-post' ),
												'next_text'    => __( 'Next', 'wti-like-post' ) . ' &raquo;',
												'base' 		=> @add_query_arg( array( 'paged' => '%#%', 'vp' => $vperiod ) ),
												'format'  	=> '?page=wtilp-most-liked-posts',
												'total'   	=> $total_pages
											)
								);
								?>
							</div>
						</div>
					</div>
					<table cellspacing="0" class="wp-list-table widefat fixed likes">
						<thead>
							<tr>
								<th class="manage-column column-cb check-column" id="cb" scope="col">
									<input type="checkbox" id="checkall">
								</th>
								<th>
									<?php echo __( 'Post Title', 'wti-like-post' ); ?>
								</th>
								<th>
									<?php echo __( 'Like Count', 'wti-like-post' ); ?>
								</th>
								<th>
									<?php echo __( 'Unlike Count', 'wti-like-post' ); ?>
								</th>
								<th>
									<?php echo __( 'Total Count', 'wti-like-post' ); ?>
								</th>
							</tr>
						</thead>
						<tbody class="list:likes" id="the-list">
						
						<?php
						foreach ( $result as $post ) {
							$post_title = get_the_title( $post->post_id );
							$permalink = get_permalink( $post->post_id );
							
							if ( isset( $last_vote_time ) ) {
								$view_users = admin_url( 'admin-ajax.php?action=wti_like_post_show_like_users&post_id=' . $post->post_id . '&last_time=' . $last_vote_time . '&nonce=' . $nonce . '&TB_iframe=1&width=750&height=500' );
							} else {
								$view_users = admin_url( 'admin-ajax.php?action=wti_like_post_show_like_users&post_id=' . $post->post_id . '&nonce=' . $nonce . '&TB_iframe=1&width=750&height=500' );
							}
							//$view_users = plugins_url( 'wti_like_post_view_users.php?action=wti_like_post_show_like_users&post_id=' . $post->post_id . '&nonce=' . $nonce . '&TB_iframe=1&width=750&height=500', __FILE__ );
							?>
							<tr>
								<th class="check-column" scope="row" align="center">
									<input type="checkbox" value="<?php echo $post->post_id; ?>" class="administrator" id="post_id_<?php echo $post->post_id; ?>" name="post_ids[]" />
								</th>
								
								<td>
								<?php
									echo '<a href="' . $permalink . '" title="' . $post_title . '" target="_blank">' . $post_title . '</a>';
									echo '<div class="row-actions"><span class="edit">';
									echo '<a href="' . $view_users . '" class="thickbox" title="' . __( 'View users who liked', 'wti-like-post' ) . ' ' . $post_title . '">' . __( 'View Users', 'wti-like-post' ) . '</a>';
									echo '</span></div>';
								?>
								</td>
							
								<td>
									<?php echo (int)get_post_meta( $post->post_id, '_wti_like_count', true ); ?>
								</td>
								
								<td>
									<?php echo (int)get_post_meta( $post->post_id, '_wti_unlike_count', true ); ?>
								</td>
								
								<td>
									<?php echo (int)get_post_meta( $post->post_id, '_wti_total_count', true ); ?>
								</td>
							</tr>
							<?php
						}
						?>
						
						</tbody>
					</table>
				</form>
				<?php
			} else {
				echo '<div class="error below-h2" id="error"><p><strong>';
				echo __( 'Nothing liked yet.', 'wti-like-post' );
				echo '</strong></p></div>';
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Section for showing Features and Support
 * 
 * @param no-param
 * @return no-return
 */
function WtiLikePostAdminFeaturesSupport() {
	?>
	<div class="wrap">
		<h2><?php echo __( 'Features & Support', 'wti-like-post' ); ?></h2>
		<br class="clear" />
		
		<div class="metabox-holder has-right-sidebar" id="poststuff">
			<div class="inner-sidebar" id="side-info-column">
				<div class="meta-box-sortables ui-sortable" id="side-sortables">
					<div id="WtiLikePostOptions" class="postbox">
						<div title="Click to toggle" class="handlediv"><br /></div>
						<h3 class="hndle"><?php echo __( 'Support / Manual / Review', 'wti-like-post' ); ?></h3>
						<div class="inside">
							<p style="margin:10px 0px;"><?php echo __('For any suggestion / query / issue / requirement, please use the forum') . ' <a href="http://support.webtechideas.com/forums/forum/wti-like-post-pro/" target="_blank">WTI Like Post PRO</a> ' . __('or drop an email to', 'wti-like-post'); ?> <a href="mailto:support@webtechideas.com?subject=WTI Like Post PRO">support@webtechideas.com</a>.</p>
							<p style="margin:10px 0px;"><?php echo __('Get the', 'wti-like-post'); ?> <a href="http://www.webtechideas.com/product/wti-like-post-pro/" target="_blank"><?php echo __('PRO Manual here', 'wti-like-post'); ?></a> <?php echo __('for a complete list of features', 'wti-like-post'); ?>.</p>
							<p style="margin:10px 0px;"><?php echo __('We will be glad if you can add your reviews on', 'wti-like-post'); ?> <a href="http://wordpress.org/support/view/plugin-reviews/wti-like-post" target="_blank"><?php echo __('Wordpress', 'wti-like-post');?></a>.</p>
						</div>
					</div>
				</div>
			</div>

			<div id="post-body">
				<div id="post-body-content">
					<div id="WtiLikePostOptions" class="postbox">
						<h3><?php echo __( 'Features', 'wti-like-post' ); ?></h3>
						
						<div class="inside">
							<ol id="wtilp-features">
								<li><?php echo __( 'Ability to change your vote when multiple voting is disabled.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Hook available for successful voting so that you can attach any functionality like sending thank you mail to user.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for viewing like, unlike, total counts for individual post on admin Most Liked Posts screen. You can also view users who voted for each of them. This works only when logged in users are allowed to vote.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Buddypress activity integration to show activity updates when a user likes and dislikes any content.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for highlighting the like/unlike icon on successful voting and page load.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Compatible with multisite set up.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Can be used with custom post types. You can control which post types to use with. You can show most liked posts based on custom post types using shortcode and widget.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for disabling the plugin css file so that you can use custom css from your theme.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for redirecting user to a specific page on successful voting.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for showing the like/unlike buttons for post excerpts.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for allowing/disallowing author to vote against own post with custom message.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Option to save plugin settings and table after plugin un-installation in case you need to reuse the data in future.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to disable auto-loading of the like/unlike buttons so that you can use the template tag GetWtiLikePost() inside your theme or [wtilp_buttons] inside post/page content using the editor. Please note that [wtilp_buttons] is NOT a shortcode.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to store voting counts (like, unlike, total) as post meta which can be used to show posts sorted by voting counts.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to have default message to encourage users to like posts.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to show users who liked a post below the like/unlike buttons.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'In total 6 styles of buttons for like/unlike functionality.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to show like count for each post in admin post list/edit section.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Has 2 template files which can be used to show most liked posts throughout the site and the most liked posts by an author on author page.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to use texts instead of like/unlike images in case you want to have some encouraging texts which can not be conveyed using images.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality for adding default like/unlike entries i.e. 0 for posts and pages when they are created.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to show most liked/unliked posts from selected categories on Most Liked/Unliked Posts widget.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to show posts liked/unliked by all users or the logged in user on the widget and also shortcode for showing the same on a page.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Wide range of time including hours to have more control on the posts shown on the widget.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Most Liked/Unliked Category Posts widget to show posts liked/unliked on the specific category page.', 'wti-like-post' ); ?></li>
								<li><?php echo __( 'Functionality to show post excerpt, thumbnail on all the available widgets.', 'wti-like-post' ); ?></li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}

// For adding metabox for posts/pages
add_action('admin_menu', 'WtiLikePostAddMetaBox');

/**
 * Metabox for for like post
 * 
 * @param no-param
 * @return no-return
 */
function WtiLikePostAddMetaBox() {
	// Add the meta box for posts/pages
     add_meta_box('wti-like-post-meta-box', __('WTI Like Post Exclude Option', 'wti-like-post'), 'WtiLikePostShowMetaBox', 'post', 'side', 'high');
     add_meta_box('wti-like-post-meta-box', __('WTI Like Post Exclude Option', 'wti-like-post'), 'WtiLikePostShowMetaBox', 'page', 'side', 'high');
}

/**
 * Callback function to show fields in meta box
 * 
 * @param no-param
 * @return string
 */
function WtiLikePostShowMetaBox() {
     global $post;

     // Use nonce for verification
     echo '<input type="hidden" name="wti_like_post_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '" />';

     // Get whether current post is excluded or not
	$excluded_posts = explode( ',', get_option( 'wti_like_post_excluded_posts' ) );
	
	if ( in_array( $post->ID, $excluded_posts ) ) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

     echo '<p>';    
     echo '<label for="wti_exclude_post"><input type="checkbox" name="wti_exclude_post" id="wti_exclude_post" value="1" ' . $checked . ' /> ';
	echo __('Check to disable like/unlike functionality', 'wti-like-post');
     echo '</label><br /><br />';
	
	echo __('Like Count', 'wti-like-post') . ': ' . (int)get_post_meta($post->ID, '_wti_like_count', true) . '<br />';//str_replace("+", "", GetWtiLikeCount($post->ID));
	echo __('Unike Count', 'wti-like-post') . ': ' . (int)get_post_meta($post->ID, '_wti_unlike_count', true) . '<br />';
	echo __('Total Count', 'wti-like-post') . ': ' . (int)get_post_meta($post->ID, '_wti_total_count', true) . '<br />';
	
     echo '</p>';
}

add_action('save_post', 'WtiLikePostSaveData');

/**
 * Save data from meta box
 * 
 * @param no-param
 * @return string
 */
function WtiLikePostSaveData($post_id) {
     // Verify nonce
     if ( empty( $_POST['wti_like_post_meta_box_nonce'] ) ||
			!wp_verify_nonce( $_POST['wti_like_post_meta_box_nonce'], basename( __FILE__ ) ) ) {
          return $post_id;
     }

     // Check autosave
     if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
          return $post_id;
     }

     // Check permissions
     if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
          if ( !current_user_can( 'edit_page', $post_id ) ) {
               return $post_id;
          }
     } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
          return $post_id;
     }

	// Initialise the excluded posts array
	$excluded_posts = array();
	
	if ( isset( $_POST['wti_exclude_post'] ) ) {
		// Check whether this post/page is to be excluded
		$exclude_post = $_POST['wti_exclude_post'];
		
		// Get old excluded posts/pages
		if ( strlen( get_option( 'wti_like_post_excluded_posts' ) ) > 0 ) {
			$excluded_posts = explode( ',', get_option( 'wti_like_post_excluded_posts' ) );
		}
		
		if ( $exclude_post == 1 && !in_array( $post_id, $excluded_posts ) ) {
			// Add this post/page id to the excluded list
			$excluded_posts[] = $post_id;
			
			if ( !empty( $excluded_posts ) ) {
				// Since there are already excluded posts/pages, add this as a comma separated value
				update_option( 'wti_like_post_excluded_posts', implode( ',', $excluded_posts ) );
			} else {
				// Since there is no old excluded post/page, add this directly
				update_option( 'wti_like_post_excluded_posts', $post_id );
			}
		} else if( !$exclude_post ) {
			// Check whether this id is already in the excluded list or not
			$key = array_search( $post_id, $excluded_posts );
			
			if ( $key !== false ) {
				// Since this is already in the list, so exluded this
				unset( $excluded_posts[$key] );
				
				// Update the excluded posts list
				update_option( 'wti_like_post_excluded_posts', implode( ',', $excluded_posts ) );
			}
		}	
	}
	
	// Add the post meta data if not added earlier
	$like_count = (int)get_post_meta( $post_id, '_wti_like_count', true );
	$unlike_count = (int)get_post_meta( $post_id, '_wti_unlike_count', true );
	$total_count = (int)get_post_meta( $post_id, '_wti_total_count', true );
	
	/*if($like_count == 0 && $unlike_count == 0) {
		// There have been no votes, so lets ad default value if set
		$min_like_count = (int)get_option('wti_like_post_min_like');
		$max_like_count = (int)get_option('wti_like_post_max_like');		
		
		// Count a value within the range
		$total_count = $like_count = rand($min_like_count, $max_like_count);
	}*/
	
	// "true" will only add the meta data if it has not been added
	add_post_meta( $post_id, '_wti_like_count', $like_count, true );
	add_post_meta( $post_id, '_wti_unlike_count', $unlike_count, true );
	add_post_meta( $post_id, '_wti_total_count', $total_count, true );
}

add_filter( 'manage_edit-post_columns', 'WtiAdminPostHeaderColumns', 1, 1 );
add_action( 'manage_posts_custom_column', 'WtiAdminPostDataRow', 1, 2 );
add_action( 'delete_post', 'WtiDeletePostMetaData', 10, 2 );

/**
 * Show like count column header in admin posts page
 * 
 * @param $columns array
 * @return $columns array
 */
function WtiAdminPostHeaderColumns($columns) {
	if (!isset($columns['like_count'])) {
		$columns['like_count'] = __('Like Count', 'wti-like-post');
	}
	
	if (!isset($columns['unlike_count'])) {
		$columns['unlike_count'] = __('Unike Count', 'wti-like-post');
	}
	
	return $columns;
}

/**
 * Show like count as a column in admin posts page
 * 
 * @param $column_name string
 * @param $post_id integer
 * @return string
 */
function WtiAdminPostDataRow($column_name, $post_id) {
	global $wpdb;
	
	switch($column_name) {
		case 'like_count':
			$like_count = GetWtiLikeCount( $post_id );
			echo str_replace( "+", "", $like_count );
			break;
		case 'unlike_count':
			$unlike_count = GetWtiUnlikeCount( $post_id );
			echo str_replace( "-", "", $unlike_count );
			break;
		default:
		    break;
	}
}

/**
 * Delete meta data such as like, unlike and total count
 * 
 * @param $post_id integer
 * @return void
 */
function WtiDeletePostMetaData($post_id)
{
	global $wpdb;
	$post_details = get_post($post_id);
	
	if ($post_details->post_type == "post" || $post_details->post_type == "page") {
		delete_post_meta($post_id, '_wti_like_count');
		delete_post_meta($post_id, '_wti_unlike_count');
		delete_post_meta($post_id, '_wti_total_count');
		
		$wpdb->query("DELETE FROM {$wpdb->prefix}wti_like_post WHERE post_id = " . $post_id);
	}
}

/**
 * Display a notice that can be dismissed
 *
 * @param none
 * @return void
 */
add_action('admin_notices', 'WtiAdminNotice');

function WtiAdminNotice() {
	global $pagenow, $wti_like_post_db_version;
	
     if ( isset( $_GET['hide_wti_like_post_notify_author'] ) && true == $_GET['hide_wti_like_post_notify_author'] ) {
		// Hide the notification
		update_option( 'wti_like_post_notify_author', 0 );
	} else if ( isset( $_GET['send_wti_like_post_notify_author'] ) && true == $_GET['send_wti_like_post_notify_author'] ) {
		// Check that the author has to be notified
		$notify_author = get_option( 'wti_like_post_notify_author', 1 );
		
		if ( $notify_author ) {
			// Not yet notified, so notify the author now
			$message = 'WTI Like Post PRO ' . $wti_like_post_db_version . ' is used on <a href="' . get_option( 'siteurl' ) . '">' . get_option( 'blogname' ) . '</a>.';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			
			$sent = wp_mail( 'support@webtechideas.com', 'WTI Like Post PRO ' . $wti_like_post_db_version . ' Used', $message, $headers );
			
			if ( $sent ) {
				update_option('wti_like_post_notify_author', 0);
				echo '<div class="updated"><p>Thanks for registering.</p></div>';
			}
		}
	} else if ( $pagenow == 'plugins.php' || ( isset( $_GET['page'] ) && ( $_GET['page'] == 'WtiLikePostAdminMenu'
		|| $_GET['page'] == 'wtilp-most-liked-posts' || $_GET['page'] == 'wtilp-features-support' ) ) ) {
		
		// Check that the author has to be notified
		$notify_author = get_option( 'wti_like_post_notify_author', 1 );
		
		if ( $notify_author ) {
			echo '<div class="updated"><p>';
			
			echo 'Please consider <strong><a href="' . esc_url( add_query_arg( 'send_wti_like_post_notify_author', 'true' ) ) . '">registering your use of WTI Like Post PRO</a></strong> ' .
				'to inform <a href="http://www.webtechideas.in" target="_blank">WebTechIdeas (plugin author)</a> that you are using it. This sends only your site name and URL so that they ' .
				'know where their plugin is being used, no other data is sent. <a href="' . esc_url( add_query_arg( 'hide_wti_like_post_notify_author', 'true' ) ) . '">Hide this message.</a>';
			
			echo '</p></div>';
		}
	}
}

/**
 * Save data from meta box
 * 
 * @param array
 * @param string
 * @return array
 */
function WtiLikePostSetPluginMeta( $links, $file ) {
	if ( strpos( $file, 'wti-like-post/wti_like_post.php' ) !== false ) {
		$new_links = array(
						'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=support@webtechideas.com&item_name=WTI%20Like%20Post&return=http://www.webtechideas.com/thanks/" target="_blank">' . __( 'Donate', 'wti-like-post' ) . '</a>',
						'<a href="http://support.webtechideas.com/forums/forum/wti-like-post-pro/" target="_blank">' . __( 'PRO Support Forum', 'wti-like-post' ) . '</a>',
					);
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

add_filter( 'plugin_row_meta', 'WtiLikePostSetPluginMeta', 10, 2 );