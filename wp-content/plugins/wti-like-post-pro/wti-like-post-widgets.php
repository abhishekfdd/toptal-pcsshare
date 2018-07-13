<?php
/**
 * Class for Most Liked Posts widget
 * @package wordpress
 * @subpackage wtilikepost
 */
class WtiMostLikedPostsWidget extends WP_Widget
{
	/**
	 * Constructor for setting up the plugin and the related language file
	 */
     function WtiMostLikedPostsWidget() {
	     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
          $widget_ops = array('description' => __('Widget to display most liked/unliked posts for a given time range.', 'wti-like-post'));
          parent::WP_Widget(false, $name = __('Most Liked/Unliked Posts', 'wti-like-post'), $widget_ops);
     }

     /**
	 * For attaching the parameters with the widget
	 */
     function widget($args, $instance) {
          global $WtiMostLikedPosts;
          $WtiMostLikedPosts->widget($args, $instance); 
     }
	
	/**
	 * For updating the widget parameters
	 */
     function update($new_instance, $old_instance) {
          if($new_instance['title'] == ''){
               $new_instance['title'] = __('Most Liked Posts', 'wti-like-post');
          }
		
		if($new_instance['counts_based_on'] == ''){
               $new_instance['counts_based_on'] = 1;
          }
		
		if($new_instance['category'] == ''){
               $new_instance['category'] = 0;
          }
		
		if(empty($new_instance['vote_type'])){
               $new_instance['vote_type'] = 'liked';
          }
		
		if(empty($new_instance['post_type'])){
               $new_instance['post_type'] = 'post';
          }
		
		if($new_instance['time_range'] == ''){
               $new_instance['time_range'] = 'all';
          }
		
		if(!isset($new_instance['show_excerpt'])){
               $new_instance['show_excerpt'] = 0;
          }
		
		if(!isset($new_instance['show_thumbnail'])){
               $new_instance['show_thumbnail'] = 0;
          }
		
		if(!isset($new_instance['show_categories'])){
               $new_instance['show_categories'] = 0;
          }
		
		if(!isset($new_instance['show_tags'])){
               $new_instance['show_tags'] = 0;
          }
		
		if(!isset($new_instance['show_author'])){
               $new_instance['show_author'] = 0;
          }
		
		// If none of the title, excerpt and thumbnail has been selected, then show title by default
		if(empty($new_instance['show_title']) && empty($new_instance['show_thumbnail']) && empty($new_instance['show_excerpt'])){
               $new_instance['show_title'] = 1;
          }
		
		// If thumbnail option is selected but thumbnail size is not specified, then set thumbnail default size to 100
		if($new_instance['show_thumbnail'] && empty($new_instance['thumbnail_size'])){
               $new_instance['thumbnail_size'] = 100;
          }
		
          return $new_instance;
     }
	
	/**
	 * For showing the widget parameters in a form
	 */
     function form($instance) {
		// Access the global parameters
          global $WtiMostLikedPosts;
		
		/**
		* Define the array of defaults
		*/ 
		$defaults = array(
					'title' => __('Most Liked Posts', 'wti-like-post'),
					'counts_based_on' => 1,
					'vote_type' => 'liked',
					'number' => 10,
					'post_type' => 'post',
					'category' => '',
					'time_range' => 'all',
					'show_title' => 1,
					'show_excerpt' => 0,
					'show_count' => 1,
					'show_author' => 0,
					'show_categories' => 0,
					'show_tags' => 0,
					'show_thumbnail' => 0,
					'thumbnail_size' => '100'
				);
		
		$instance = wp_parse_args( $instance, $defaults );
		extract( $instance, EXTR_SKIP );
          ?>
		<p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wti-like-post'); ?>:<br />
               <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'];?>" /></label>
          </p>

		<p>
               <label><?php _e('Show counts based on', 'wti-like-post'); ?>:</label><br />
			<input type="radio" id="<?php echo $this->get_field_id('counts_based_on'); ?>_all" name="<?php echo $this->get_field_name('counts_based_on'); ?>" value="1"
							<?php if($instance['counts_based_on'] == '1' || $instance['counts_based_on'] == '') echo 'checked="checked"'; ?> /> <?php _e('All votes', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('counts_based_on'); ?>_my" name="<?php echo $this->get_field_name('counts_based_on'); ?>" value="2"
							<?php if($instance['counts_based_on'] == '2') echo 'checked="checked"'; ?>  /> <?php _e('My votes', 'wti-like-post'); ?>
          </p>

		<p>
               <label><?php _e('Vote type to show', 'wti-like-post'); ?>:</label><br />
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>" name="<?php echo $this->get_field_name('vote_type'); ?>" value="liked"
							<?php if($instance['vote_type'] == 'liked') echo 'checked="checked"'; ?> /> <?php _e('Liked', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>_unlike" name="<?php echo $this->get_field_name('vote_type'); ?>" value="unliked"
							<?php if($instance['vote_type'] == 'unliked') echo 'checked="checked"'; ?>  /> <?php _e('Unliked', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>_total" name="<?php echo $this->get_field_name('vote_type'); ?>" value="total"
							<?php if($instance['vote_type'] == 'total') echo 'checked="checked"'; ?>  /> <?php _e('Total', 'wti-like-post'); ?>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 50px;" value="<?php echo $instance['number'];?>" /></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type to use (comma separated)', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" style="width: 200px;" value="<?php echo $instance['post_type'];?>" /></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category', 'wti-like-post'); ?>:<br />
			<select name="<?php echo $this->get_field_name('category'); ?>[]" id="<?php echo $this->get_field_id('category'); ?>" multiple="multiple" size="4" style="height:auto !important;">
				<option value="0"><?php _e('All Categories', 'wti-like-post'); ?></option>
			<?php
			// Get available categories
			$categories=  get_categories();
			
			foreach ($categories as $category) {
				$selected = in_array($category->cat_ID, $instance['category']) ? 'selected="selected"' : '';
				$option  = '<option value="' . $category->cat_ID . '" ' . $selected . '>';
				$option .= $category->cat_name;
				$option .= ' (' . $category->category_count . ')';
				$option .= '</option>';
				echo $option;
			}
			?>
               </select>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('time_range'); ?>"><?php _e('Time range', 'wti-like-post'); ?>:<br />
			<select name="<?php echo $this->get_field_name('time_range'); ?>" id="<?php echo $this->get_field_id('time_range'); ?>">
				<?php
				$voting_period = $instance['time_range'];
				?>
				<option value="all" <?php if ("all" == $voting_period) echo "selected='selected'"; ?>><?php echo __('All time', 'wti-like-post'); ?></option>
				<option value="1h" <?php if ("1h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one hour', 'wti-like-post'); ?></option>
				<option value="2h" <?php if ("2h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two hours', 'wti-like-post'); ?></option>
				<option value="3h" <?php if ("3h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three hours', 'wti-like-post'); ?></option>												
				<option value="4h" <?php if ("4h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last four hours', 'wti-like-post'); ?></option>
				<option value="6h" <?php if ("6h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last six hours', 'wti-like-post'); ?></option>
				<option value="8h" <?php if ("8h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last eight hours', 'wti-like-post'); ?></option>
				<option value="12h" <?php if ("12h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last twelve hours', 'wti-like-post'); ?></option>
				<option value="1" <?php if ("1" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one day', 'wti-like-post'); ?></option>
				<option value="2" <?php if ("2" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two days', 'wti-like-post'); ?></option>
				<option value="3" <?php if ("3" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three days', 'wti-like-post'); ?></option>
				<option value="7" <?php if ("7" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one week', 'wti-like-post'); ?></option>
				<option value="14" <?php if ("14" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two weeks', 'wti-like-post'); ?></option>
				<option value="21" <?php if ("21" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three weeks', 'wti-like-post'); ?></option>
				<option value="1m" <?php if ("1m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one month', 'wti-like-post'); ?></option>
				<option value="2m" <?php if ("2m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two months', 'wti-like-post'); ?></option>
				<option value="3m" <?php if ("3m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three months', 'wti-like-post'); ?></option>
				<option value="6m" <?php if ("6m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last six Months', 'wti-like-post'); ?></option>
				<option value="1y" <?php if ("1y" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one Year', 'wti-like-post'); ?></option>
			</select>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_title'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php if($instance['show_title'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show title', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" value="1" <?php if($instance['show_excerpt'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show excerpt', 'wti-like-post'); ?></label>
          </p>

		<p>
			<label for="<?php echo $this->get_field_id('show_count'); ?>_no"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_no" name="<?php echo $this->get_field_name('show_count'); ?>" value="0" <?php if($instance['show_count'] == '0') echo 'checked="checked"'; ?> /> <?php echo __('Don\'t show vote count', 'wti-like-post'); ?></label><br />
			<label for="<?php echo $this->get_field_id('show_count'); ?>_title"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_title" name="<?php echo $this->get_field_name('show_count'); ?>" value="1" <?php if($instance['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show vote count after post title', 'wti-like-post'); ?></label><br />
			<label for="<?php echo $this->get_field_id('show_count'); ?>_excerpt"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_excerpt" name="<?php echo $this->get_field_name('show_count'); ?>" value="2" <?php if($instance['show_count'] == '2') echo 'checked="checked"'; ?> /> <?php echo __('Show vote count after post excerpt', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_author'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" value="1" <?php if($instance['show_author'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show author', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_categories'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_categories'); ?>" name="<?php echo $this->get_field_name('show_categories'); ?>" value="1" <?php if($instance['show_categories'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show categories', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_tags'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_tags'); ?>" name="<?php echo $this->get_field_name('show_tags'); ?>" value="1" <?php if($instance['show_tags'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show tags', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>" value="1" <?php if($instance['show_thumbnail'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show thumbnail', 'wti-like-post'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php echo __('Thumbnail size', 'wti-like-post'); ?> <input type="text" id="<?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name('thumbnail_size'); ?>" value="<?php echo $instance['thumbnail_size']?>" <?php if($instance['thumbnail_size'] == '1') echo 'checked="checked"'; ?> size='5' /></label>
          </p>

		<input type="hidden" id="wti-most-submit" name="wti-submit" value="1" />	   
          <?php
     }
}

/**
 * Class for Most Liked Posts
 * @package wordpress
 * @subpackage wtilikepost
 */
class WtiMostLikedPosts
{
	/**
	 * Constructor to call the initialization function
	 */
     function WtiMostLikedPosts() {
          add_action( 'widgets_init', array(&$this, 'init') );
     }
	
	/**
	 * Widget initialization function to register the widget
	 */
     function init() {
          register_widget("WtiMostLikedPostsWidget");
     }
     
	/**
	 * To render the widget
	 */
     function widget($args, $instance = array() ) {
		global $wpdb, $current_user;
		extract($args);
		
		$where = '';
		$cat_posts = array();
		$category = array();
		$all_ids = array();
		$excluded_post_ids = array();
		
		// Get all the parameters
		$title = $instance['title'];
		$counts_based_on = $instance['counts_based_on'];
		$show_count = $instance['show_count'];
		$post_type = trim($instance['post_type']);
		$time_range = $instance['time_range'];
		$show_title = (int)$instance['show_title'];
		$show_excerpt = (int)$instance['show_excerpt'];
		$show_thumbnail = (int)$instance['show_thumbnail'];
		$show_categories = (int)$instance['show_categories'];
		$show_tags = (int)$instance['show_tags'];
		$show_author = (int)$instance['show_author'];
		$thumbnail_size = (int)$instance['thumbnail_size'];

		// Prepare the most liked posts query
		$query = "SELECT post_id, SUM(value) AS like_count FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish'";
		
		// Get the posts for specific post type
		if(!empty($post_type)) {
			$query .= " AND P.post_type IN ('" . str_replace(",", "','", $post_type) . "')";
		}
		
		// Check for any specific category
		if(!empty($instance['category']) && $instance['category'] != 0) {
			$category = $instance['category'];
		}
		
		// Get the posts for specific categories if selected
		if(count($category)) {
			$category_posts = get_posts(
								array(
									'category' => implode(',', $category),
									'numberposts' => -1
								)
							);
			
			if(count($category_posts) > 0) {
				foreach($category_posts as $category_post) {
					$cat_posts[] = $category_post->ID;
				}
			}
		}
		
		// Get the excluded posts as per setting
		$show_excluded_posts = get_option('wti_like_post_show_on_widget');
		$excluded_posts = get_option('wti_like_post_excluded_posts');

		if($excluded_posts) {
			$excluded_post_ids = explode(',', $excluded_posts);
		}

		if(!$show_excluded_posts && count($excluded_post_ids) > 0 && count($cat_posts) > 0) {
			// Get the posts which are not excluded from the category
			$selected_posts = array_diff($cat_posts, $excluded_post_ids);
			
			if(count($selected_posts)) {
				$where = " AND post_id IN (" . implode(',', $selected_posts) . ")";
			}
		} elseif(!$show_excluded_posts && count($excluded_post_ids) > 0) {
			$where = " AND post_id NOT IN (" . $excluded_posts . ")";
		} elseif(count($cat_posts) > 0) {
			$where = " AND post_id IN (" . implode(',', $cat_posts) . ")";
		}

		if(isset($time_range) && $time_range != 'all') {
			$last_date = GetWtiLastDate($time_range);
			$where .= " AND date_time >= '$last_date'";
		}
		
		if($counts_based_on == 2) {
			// If set to show my liked/unliked posts
			$query .= " AND user_id = {$current_user->ID}";
		}
		
		if((int)$instance['number'] > 0) {
			$limit = "LIMIT " . (int)$instance['number'];
		}
		
		// Get which type of like is required
		if($instance['vote_type'] == 'unliked') {
			$query .= " AND value < 0 $where GROUP BY post_id ORDER BY like_count ASC, post_title $limit";
		} else if($instance['vote_type'] == 'total') {
			$query .= " $where GROUP BY post_id ORDER BY like_count DESC, post_title $limit";
		} else {
			$query .= " AND value > 0 $where GROUP BY post_id ORDER BY like_count DESC, post_title $limit";
		}

		$posts = $wpdb->get_results($query);

		// Create the widget
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="wti-most-liked-posts">';
		
		if(count($posts) > 0) {
			foreach ($posts as $post) {
				$post_data = get_post($post->post_id);
				$post_title = $post_data->post_title;
				$post_excerpt = $post_data->post_excerpt;
				$permalink = get_permalink($post->post_id);
				$like_count = $post->like_count;

				$widget_data .= '<li class="wti_widget_li">';
				
				// Show post title
				if($show_title && !empty($post_title)) {
					if($show_count == 1) {
						$widget_data .= '<a href="' . $permalink . '" title="' . $post_title . '">' . $post_data->post_title . '</a>';
						$widget_data .= '<span class="wti_widget_count">(' . (($instance['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . ')</span>';
					} else {
						$widget_data .= '<a href="' . $permalink . '" title="' . $post_title . '">' . $post_data->post_title . '</a>';
					}
				}
				
				// Show post thumbnail
				if($show_thumbnail && has_post_thumbnail($post->post_id)) {
					$widget_data .= '<div class="wti_widget_thumb"><a href="' . $permalink . '" title="' . $post_title . '">' . get_the_post_thumbnail($post->post_id, array($thumbnail_size, $thumbnail_size)) . '</a></div>';
				}
				
				// Show post excerpt
				if($show_excerpt && !empty($post_excerpt)) {
					if($show_count == 2) {
						$widget_data .= '<p>' . $post_data->post_excerpt;
						$widget_data .= '<span class="wti_widget_count">(' . (($instance['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . ')</span>';
						$widget_data .= '</p>';
					} else {
						$widget_data .= '<p>' . $post_data->post_excerpt . '</p>';
					}
				}
				
				// Show author if set
				if($show_author) {
					$widget_data .= '<span class="wti_widget_author">' . __('Author', 'wti-like-post') . ': <a href="' . get_author_posts_url($post_data->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post_data->post_author) . '</a></span>';
				}
				
				if($show_categories) {
					// Get all categories for this post
					$post_categories = wp_get_post_categories( $post->post_id );
					$cats = array();
	
					foreach($post_categories as $cat_id) {
						$cat = get_category( $cat_id );
						$cats[] = '<a href="' . esc_url(get_category_link( $cat_id )) . '">' . $cat->name . '</a>';
					}
					
					// Show the post categories if any
					if(count($cats) > 0) {
						$widget_data .= '<span class="wti_widget_category">' . __('Category', 'wti-like-post') . ': ' . implode(', ', $cats) . '</span>';
					}
				}
				
				// Show tags if set
				if($show_tags) {
					// Get all tags for this post
					$tag_ids = wp_get_post_tags( $post->post_id, array( 'fields' => 'ids' ) );
					$tags = array();
					
					foreach($tag_ids as $tag_id) {
						$tag = get_tag($tag_id);
						$tags[] = '<a href="' . esc_url(get_tag_link( $tag_id )) . '">' . $tag->name . '</a>';
					}
					
					if(count($tag_ids) > 0) {
						$widget_data .= '<span class="wti_widget_tags">' . __('Tags', 'wti-like-post') . ': ' . implode(', ', $tags) . '</span>';
					}
				}
				
				//$widget_data .= GetWtiLikePost('put');
				$widget_data .= '</li>';
			}
		} else {
			$widget_data .= '<li>';
			
			if ($instance['vote_type'] == 'unliked') {
				$widget_data .= __('Nothing disliked yet.', 'wti-like-post');
			} else {
				$widget_data .= __('Nothing liked yet.', 'wti-like-post');
			}
			
			$widget_data .= '</li>';
		}
   
		$widget_data .= '</ul>';
		$widget_data .= $after_widget;

		echo $widget_data;
     }
}

// Create the object
$WtiMostLikedPosts = new WtiMostLikedPosts();

/**
 * Class for Recently Liked Posts widget
 * @package wordpress
 * @subpackage wtilikepost
 */
class WtiRecentlyLikedPostsWidget extends WP_Widget
{
	/**
	 * Constructor for setting up the plugin and the related language file
	 */
	function WtiRecentlyLikedPostsWidget() {
	     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
          $widget_ops = array('description' => __('Widget to show recently liked posts.', 'wti-like-post'));
          parent::WP_Widget(false, $name = __('Recently Liked Posts', 'wti-like-post'), $widget_ops);
     }

     /**
	 * For attaching the parameters with the widget
	 */
     function widget($args, $instance) {
          global $WtiRecentlyLikedPosts;
          $WtiRecentlyLikedPosts->widget($args, $instance); 
     }
	
	/**
	 * For updating the widget parameters
	 */    
     function update($new_instance, $old_instance) {         
          if($new_instance['title'] == ''){
               $new_instance['title'] = __('Recently Liked Posts', 'wti-like-post');
          }
		
		if(empty($new_instance['post_type'])){
               $new_instance['post_type'] = 'post';
          }
         
  		if($new_instance['time_range'] == ''){
               $new_instance['time_range'] = 'all';
          }
		
		if(!isset($new_instance['show_excerpt'])){
               $new_instance['show_excerpt'] = 0;
          }
		
		if(!isset($new_instance['show_categories'])){
               $new_instance['show_categories'] = 0;
          }
		
		if(!isset($new_instance['show_tags'])){
               $new_instance['show_tags'] = 0;
          }
		
		if(!isset($new_instance['show_author'])){
               $new_instance['show_author'] = 0;
          }
		
		if(!isset($new_instance['show_thumbnail'])){
               $new_instance['show_thumbnail'] = 0;
          }
		
		if(empty($new_instance['show_title']) && empty($new_instance['show_thumbnail']) && empty($new_instance['show_excerpt'])){
               $new_instance['show_title'] = 1;
          }
		
		if(empty($new_instance['thumbnail_size'])){
               $new_instance['thumbnail_size'] = 100;
          }
         
          return $new_instance;
     }

	/**
	 * For showing the widget parameters in a form
	 */
     function form($instance)
     {
          global $WtiRecentlyLikedPosts;
		
		/**
		* Define the array of defaults
		*/ 
		$defaults = array(
					'title' => __('Recently Liked Posts', 'wti-like-post'),
					'number' => 10,
					'post_type' => 'post',
					'show_title' => 1,
					'show_excerpt' => 0,
					'show_author' => 0,
					'show_categories' => 0,
					'show_tags' => 0,
					'show_thumbnail' => 0,
					'thumbnail_size' => '100'
				);
		
		$instance = wp_parse_args( $instance, $defaults );
		extract( $instance, EXTR_SKIP );
          ?>
		<p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wti-like-post'); ?>:<br />
               <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'];?>" /></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of entries to show', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 50px;" value="<?php echo $instance['number'];?>" /></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post type to use (comma separated)', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" style="width: 200px;" value="<?php echo $instance['post_type'];?>" /></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_title'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php if($instance['show_title'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show title', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" value="1" <?php if($instance['show_excerpt'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show excerpt', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_author'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" value="1" <?php if($instance['show_author'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show author', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_categories'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_categories'); ?>" name="<?php echo $this->get_field_name('show_categories'); ?>" value="1" <?php if($instance['show_categories'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show categories', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_tags'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_tags'); ?>" name="<?php echo $this->get_field_name('show_tags'); ?>" value="1" <?php if($instance['show_tags'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show tags', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>" value="1" <?php if($instance['show_thumbnail'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show thumbnail', 'wti-like-post'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php echo __('Thumbnail size', 'wti-like-post'); ?> <input type="text" id="<?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name('thumbnail_size'); ?>" value="<?php echo $instance['thumbnail_size']?>" <?php if($instance['thumbnail_size'] == '1') echo 'checked="checked"'; ?> size='5' /></label>
          </p>

		<input type="hidden" id="wti-recent-submit" name="wti-submit" value="1" />	   
          <?php
     }
}

class WtiRecentlyLikedPosts
{
     function WtiRecentlyLikedPosts(){
          add_action( 'widgets_init', array(&$this, 'init') );
     }
    
     function init(){
          register_widget("WtiRecentlyLikedPostsWidget");
     }
     
     function widget($args, $instance = array() ){
		global $wpdb;
		extract($args);

		$cat_posts = array();
		$category = array();
		$excluded_post_ids = array();
		$title = trim($instance['title']);
		$post_type = trim($instance['post_type']);
		$number = (int)$instance['number'];
		$show_title = (int)$instance['show_title'];
		$show_excerpt = (int)$instance['show_excerpt'];
		$show_categories = (int)$instance['show_categories'];
		$show_tags = (int)$instance['show_tags'];
		$show_author = (int)$instance['show_author'];
		$show_thumbnail = (int)$instance['show_thumbnail'];
		$thumbnail_size = (int)$instance['thumbnail_size'];
		
		// Getting the most liked posts
		$query = "SELECT post_id, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND value > 0";
		
		// Get the posts for specific post type
		if(!empty($post_type)) {
			$query .= " AND P.post_type IN ('" . str_replace(",", "','", $post_type) . "')";
		}
	
		$show_excluded_posts = get_option('wti_like_post_show_on_widget');
		$excluded_posts = trim(get_option('wti_like_post_excluded_posts'));
		
		if($excluded_posts) {
			$excluded_post_ids = explode(',', $excluded_posts);
		}
		
		// Create the conditions
		if(!$show_excluded_posts && count($excluded_post_ids) > 0) {
			$query .= " AND post_id NOT IN (" . get_option('wti_like_post_excluded_posts') . ")";
		}
		
		if($number == 0) {
			$number = 10;
		}
		
		$query .= " GROUP BY post_id ORDER BY date_time DESC LIMIT $number";
		
		$posts = $wpdb->get_results($query);

		// Construct the widget
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="wti-most-liked-posts wti-user-liked-posts">';
		
		if(count($posts) > 0) {
			foreach ($posts as $post) {
				$post_data = get_post($post->post_id);
				$post_title = $post_data->post_title;
				$post_excerpt = $post_data->post_excerpt;
				$permalink = get_permalink($post->post_id);
				//$like_count = $post->like_count;
			
				$widget_data .= '<li class="wti_widget_li">';
				
				if($show_title && !empty($post_title)) {
					$widget_data .= '<a href="' . $permalink . '" title="' . $post_title . '">' . $post_data->post_title . '</a>';
				}
				
				if($show_thumbnail && has_post_thumbnail($post->post_id)) {
					$widget_data .= '<div class="wti_widget_thumb"><a href="' . $permalink . '" title="' . $post_title . '">' . get_the_post_thumbnail($post->post_id, array($thumbnail_size, $thumbnail_size)) . '</a></div>';
				}
				
				if($show_excerpt && !empty($post_excerpt)) {
					$widget_data .= '<p>' . $post_data->post_excerpt . '</p>';
				}

				// Show author if set
				if($show_author) {
					$widget_data .= '<span class="wti_widget_author">' . __('Author', 'wti-like-post') . ': <a href="' . get_author_posts_url($post_data->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post_data->post_author) . '</a></span>';
				}
				
				if($show_categories) {
					// Get all categories for this post
					$post_categories = wp_get_post_categories( $post->post_id );
					$cats = array();
	
					foreach($post_categories as $cat_id) {
						$cat = get_category( $cat_id );
						$cats[] = '<a href="' . esc_url(get_category_link( $cat_id )) . '">' . $cat->name . '</a>';
					}
					
					// Show the post categories if any
					if(count($cats) > 0) {
						$widget_data .= '<span class="wti_widget_category">' . __('Category', 'wti-like-post') . ': ' . implode(', ', $cats) . '</span>';
					}
				}
				
				// Show tags if set
				if($show_tags) {
					// Get all tags for this post
					$tag_ids = wp_get_post_tags( $post->post_id, array( 'fields' => 'ids' ) );
					$tags = array();
					
					foreach($tag_ids as $tag_id) {
						$tag = get_tag($tag_id);
						$tags[] = '<a href="' . esc_url(get_tag_link( $tag_id )) . '">' . $tag->name . '</a>';
					}
					
					if(count($tag_ids) > 0) {
						$widget_data .= '<span class="wti_widget_tags">' . __('Tags', 'wti-like-post') . ': ' . implode(', ', $tags) . '</span>';
					}
				}
				
				$widget_data .= '</li>';
			}
		} else {
			$widget_data .= '<li>';
			$widget_data .= __('Nothing liked yet.', 'wti-like-post');
			$widget_data .= '</li>';
		}

		$widget_data .= '</ul>';
		$widget_data .= $after_widget;
   
		echo $widget_data;
     }
}

$WtiRecentlyLikedPosts = new WtiRecentlyLikedPosts();

class WtiMostLikedCategoryPostsWidget extends WP_Widget
{
	/**
	 * Constructor for setting up the plugin and the related language file
	 */
	function WtiMostLikedCategoryPostsWidget() {
	     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
          $widget_ops = array('description' => __('Widget to display most liked/unliked posts for a given category on category page.', 'wti-like-post'));
          parent::WP_Widget(false, $name = __('Most Liked/Unliked Category Posts', 'wti-like-post'), $widget_ops);
     }

     /**
	 * For attaching the parameters with the widget
	 */
     function widget($args, $instance) {
          global $WtiMostLikedCategoryPosts;
          $WtiMostLikedCategoryPosts->widget($args, $instance); 
     }
    
	/**
	 * For updating the widget parameters
	 */
     function update($new_instance, $old_instance) {
          if($new_instance['title'] == ''){
               $new_instance['title'] = __('Most Liked Category Posts', 'wti-like-post');
          }
		
		if($new_instance['counts_based_on'] == ''){
               $new_instance['counts_based_on'] = 1;
          }
		
		if(empty($new_instance['vote_type'])){
               $new_instance['vote_type'] = 'liked';
          }
		
		if($new_instance['time_range'] == ''){
               $new_instance['time_range'] = 'all';
          }

		if($new_instance['show_count'] == ''){
               $new_instance['show_count'] = 0;
          }
		
		if(!isset($new_instance['show_excerpt'])){
               $new_instance['show_excerpt'] = 0;
          }
		
		if(!isset($new_instance['show_thumbnail'])){
               $new_instance['show_thumbnail'] = 0;
          }
		
		if(!isset($new_instance['show_categories'])){
               $new_instance['show_categories'] = 0;
          }
		
		if(!isset($new_instance['show_tags'])){
               $new_instance['show_tags'] = 0;
          }
		
		if(!isset($new_instance['show_author'])){
               $new_instance['show_author'] = 0;
          }
		
		if(empty($new_instance['show_title']) && empty($new_instance['show_thumbnail']) && empty($new_instance['show_excerpt'])){
               $new_instance['show_title'] = 1;
          }
		
		if(empty($new_instance['thumbnail_size'])){
               $new_instance['thumbnail_size'] = 100;
          }

          return $new_instance;
     }

	/**
	 * For showing the widget parameters in a form
	 */
     function form($instance) {
		// Access the global parameters
          global $WtiMostLikedCategoryPosts;
		
		/**
		* Define the array of defaults
		*/ 
		$defaults = array(
					'title' => __('Most Liked Category Posts', 'wti-like-post'),
					'counts_based_on' => 1,
					'vote_type' => 'liked',
					'number' => 10,
					'time_range' => 'all',
					'show_title' => 1,
					'show_excerpt' => 0,
					'show_count' => 1,
					'show_author' => 0,
					'show_tags' => 0,
					'show_thumbnail' => 0,
					'thumbnail_size' => '100'
				);
		
		$instance = wp_parse_args( $instance, $defaults );
		extract( $instance, EXTR_SKIP );
		?>
		<p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wti-like-post'); ?>:<br />
               <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title'];?>" /></label>
          </p>

		<p>
               <label><?php _e('Show counts based on', 'wti-like-post'); ?>:</label><br />
			<input type="radio" id="<?php echo $this->get_field_id('counts_based_on'); ?>_all" name="<?php echo $this->get_field_name('counts_based_on'); ?>" value="1"
							<?php if($instance['counts_based_on'] == '1' || $instance['counts_based_on'] == '') echo 'checked="checked"'; ?> /> <?php _e('All votes', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('counts_based_on'); ?>_my" name="<?php echo $this->get_field_name('counts_based_on'); ?>" value="2"
							<?php if($instance['counts_based_on'] == '2') echo 'checked="checked"'; ?>  /> <?php _e('My votes', 'wti-like-post'); ?>
          </p>

		<p>
               <label><?php _e('Vote type to show', 'wti-like-post'); ?>:</label><br />
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>" name="<?php echo $this->get_field_name('vote_type'); ?>" value="liked"
							<?php if($instance['vote_type'] == 'liked') echo 'checked="checked"'; ?> /> <?php _e('Liked', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>_unlike" name="<?php echo $this->get_field_name('vote_type'); ?>" value="unliked"
							<?php if($instance['vote_type'] == 'unliked') echo 'checked="checked"'; ?>  /> <?php _e('Unliked', 'wti-like-post'); ?>
			<input type="radio" id="<?php echo $this->get_field_id('vote_type'); ?>_total" name="<?php echo $this->get_field_name('vote_type'); ?>" value="total"
							<?php if($instance['vote_type'] == 'total') echo 'checked="checked"'; ?>  /> <?php _e('Total', 'wti-like-post'); ?>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show', 'wti-like-post'); ?>:<br />
               <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" style="width: 50px;" value="<?php echo $instance['number'];?>" /></label>
          </p>
		
		<p>
               <label for="<?php echo $this->get_field_id('time_range'); ?>"><?php _e('Time range', 'wti-like-post'); ?>:<br />
			<select name="<?php echo $this->get_field_name('time_range'); ?>" id="<?php echo $this->get_field_id('time_range'); ?>">
				<?php
				$voting_period = $instance['time_range'];
				?>
				<option value="all" <?php if ("all" == $voting_period) echo "selected='selected'"; ?>><?php echo __('All time', 'wti-like-post'); ?></option>
				<option value="1h" <?php if ("1h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one hour', 'wti-like-post'); ?></option>
				<option value="2h" <?php if ("2h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two hours', 'wti-like-post'); ?></option>
				<option value="3h" <?php if ("3h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three hours', 'wti-like-post'); ?></option>												
				<option value="4h" <?php if ("4h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last four hours', 'wti-like-post'); ?></option>
				<option value="6h" <?php if ("6h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last six hours', 'wti-like-post'); ?></option>
				<option value="8h" <?php if ("8h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last eight hours', 'wti-like-post'); ?></option>
				<option value="12h" <?php if ("12h" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last twelve hours', 'wti-like-post'); ?></option>
				<option value="1" <?php if ("1" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one day', 'wti-like-post'); ?></option>
				<option value="2" <?php if ("2" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two days', 'wti-like-post'); ?></option>
				<option value="3" <?php if ("3" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three days', 'wti-like-post'); ?></option>
				<option value="7" <?php if ("7" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one week', 'wti-like-post'); ?></option>
				<option value="14" <?php if ("14" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two weeks', 'wti-like-post'); ?></option>
				<option value="21" <?php if ("21" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three weeks', 'wti-like-post'); ?></option>
				<option value="1m" <?php if ("1m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one month', 'wti-like-post'); ?></option>
				<option value="2m" <?php if ("2m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last two months', 'wti-like-post'); ?></option>
				<option value="3m" <?php if ("3m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last three months', 'wti-like-post'); ?></option>
				<option value="6m" <?php if ("6m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last six Months', 'wti-like-post'); ?></option>
				<option value="1y" <?php if ("1y" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Last one Year', 'wti-like-post'); ?></option>
			</select>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_title'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" value="1" <?php if($instance['show_title'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show title', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" value="1" <?php if($instance['show_excerpt'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show excerpt', 'wti-like-post'); ?></label>
          </p>

		<p>
			<label for="<?php echo $this->get_field_id('show_count'); ?>_no"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_no" name="<?php echo $this->get_field_name('show_count'); ?>" value="0" <?php if($instance['show_count'] == '0') echo 'checked="checked"'; ?> /> <?php echo __('Don\'t show vote count', 'wti-like-post'); ?></label><br />
			<label for="<?php echo $this->get_field_id('show_count'); ?>_title"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_title" name="<?php echo $this->get_field_name('show_count'); ?>" value="1" <?php if($instance['show_count'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show vote count after post title', 'wti-like-post'); ?></label><br />
			<label for="<?php echo $this->get_field_id('show_count'); ?>_excerpt"><input type="radio" id="<?php echo $this->get_field_id('show_count'); ?>_excerpt" name="<?php echo $this->get_field_name('show_count'); ?>" value="2" <?php if($instance['show_count'] == '2') echo 'checked="checked"'; ?> /> <?php echo __('Show vote count after post excerpt', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_author'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" value="1" <?php if($instance['show_author'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show author', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_tags'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_tags'); ?>" name="<?php echo $this->get_field_name('show_tags'); ?>" value="1" <?php if($instance['show_tags'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show tags', 'wti-like-post'); ?></label>
          </p>

		<p>
               <label for="<?php echo $this->get_field_id('show_thumbnail'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('show_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_thumbnail'); ?>" value="1" <?php if($instance['show_thumbnail'] == '1') echo 'checked="checked"'; ?> /> <?php echo __('Show thumbnail', 'wti-like-post'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php echo __('Thumbnail size', 'wti-like-post'); ?> <input type="text" id="<?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name('thumbnail_size'); ?>" value="<?php echo $instance['thumbnail_size'];?>" <?php if($instance['thumbnail_size'] == '1') echo 'checked="checked"'; ?> size='5' /></label>
          </p>
		
		<input type="hidden" id="wti-most-submit" name="wti-submit" value="1" />	   
          <?php
     }
}

class WtiMostLikedCategoryPosts
{
	/**
	 * Constructor to call the initialization function
	 */
	function WtiMostLikedCategoryPosts() {
          add_action( 'widgets_init', array(&$this, 'init') );
     }
    
	/**
	 * Widget initialization function to register the widget
	 */
     function init() {
          register_widget("WtiMostLikedCategoryPostsWidget");
     }
     
	/**
	 * To render the widget
	 */
     function widget($args, $instance = array()) {
		global $wpdb, $current_user;
		extract($args);
		$title = $instance['title'];
	
		$widget_data  = $before_widget;
		$widget_data .= $before_title . $title . $after_title;
		$widget_data .= '<ul class="wti-most-liked-posts">';
		
		if (is_category()) {
			$where = '';
			$cat_posts = array();
			$excluded_post_ids = array();
			$show_count = $instance['show_count'];
			$category = get_query_var('cat');
			$time_range = $instance['time_range'];
			$show_title = (int)$instance['show_title'];
			$show_excerpt = (int)$instance['show_excerpt'];
			$show_thumbnail = (int)$instance['show_thumbnail'];
			$thumbnail_size = $instance['thumbnail_size'];
			$show_tags = (int)$instance['show_tags'];
			$show_author = (int)$instance['show_author'];
			
			if((int)$instance['number'] > 0) {
				$limit = "LIMIT " . (int)$instance['number'];
			}
			
			// Get the posts for specific categories if selected
			if($category) {
				$category_posts = get_posts(
									array(
										'category' => $category,
										'numberposts' => -1
									)
								);
				
				if(count($category_posts) > 0) {
					foreach($category_posts as $category_post) {
						$cat_posts[] = $category_post->ID;
					}
				}
			}
			
			// Get the excluded posts as per setting
			$show_excluded_posts = get_option('wti_like_post_show_on_widget');
			$excluded_posts = get_option('wti_like_post_excluded_posts');

			if($excluded_posts) {
				$excluded_post_ids = explode(',', $excluded_posts);
			}
	
			if(!$show_excluded_posts && count($excluded_post_ids) > 0 && count($cat_posts) > 0) {
				// Get the posts which are not excluded from the category
				$selected_posts = array_diff($cat_posts, $excluded_post_ids);
				
				if(count($selected_posts)) {
					$where = "AND post_id IN (" . implode(',', $selected_posts) . ")";
				}
			} elseif(!$show_excluded_posts && count($excluded_post_ids) > 0) {
				$where = "AND post_id NOT IN (" . $excluded_posts . ")";
			} elseif(count($cat_posts) > 0) {
				$where = "AND post_id IN (" . implode(',', $cat_posts) . ")";
			}
	
			if(isset($time_range) && $time_range != 'all') {
				$last_date = GetWtiLastDate($time_range);
				$where .= " AND date_time >= '$last_date'";
			}
			
			// Getting the most liked posts
			$query = "SELECT post_id, SUM(value) AS like_count, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
			$query .= "WHERE L.post_id = P.ID AND post_status = 'publish'";
			
			if($instance['vote_type'] == 'unliked') {
				$query .= " AND value < 0 $where GROUP BY post_id ORDER BY like_count ASC, post_title $limit";
			} else if($instance['vote_type'] == 'total') {
				$query .= " $where GROUP BY post_id ORDER BY like_count DESC, post_title $limit";
			} else {
				$query .= " AND value > 0 $where GROUP BY post_id ORDER BY like_count DESC, post_title $limit";
			}
			
			$posts = $wpdb->get_results($query);
			
			if(count($posts) > 0) {
				foreach ($posts as $post) {
					$post_data = get_post($post->post_id);
					$post_title = $post_data->post_title;
					$post_excerpt = $post_data->post_excerpt;
					$permalink = get_permalink($post->post_id);
					$like_count = $post->like_count;
					
					$widget_data .= '<li class="wti_widget_li">';

					// Show post title
					if($show_title && !empty($post_title)) {
						if($show_count == 1) {
							$widget_data .= '<a href="' . $permalink . '" title="' . $post_title . '">' . $post_data->post_title . '</a>';
							$widget_data .= '<span class="wti_widget_count">(' . (($instance['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . ')</span>';
						} else {
							$widget_data .= '<a href="' . $permalink . '" title="' . $post_title . '">' . $post_data->post_title . '</a>';
						}
					}
					
					// Show post thumbnail
					if($show_thumbnail && has_post_thumbnail($post->post_id)) {
						$widget_data .= '<div class="wti_widget_thumb"><a href="' . $permalink . '" title="' . $post_title . '">' . get_the_post_thumbnail($post->post_id, array($thumbnail_size, $thumbnail_size)) . '</a></div>';
					}
					
					// Show post excerpt
					if($show_excerpt && !empty($post_excerpt)) {
						if($show_count == 2) {
							$widget_data .= '<p>' . $post_data->post_excerpt;
							$widget_data .= '<span class="wti_widget_count">(' . (($instance['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . ')</span>';
							$widget_data .= '</p>';
						} else {
							$widget_data .= '<p>' . $post_data->post_excerpt . '</p>';
						}
					}
					
					// Show author if set
					if($show_author) {
						$widget_data .= '<span class="wti_widget_author">' . __('Author', 'wti-like-post') . ': <a href="' . get_author_posts_url($post_data->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post_data->post_author) . '</a></span>';
					}
					
					// Show tags if set
					if($show_tags) {
						// Get all tags for this post
						$tag_ids = wp_get_post_tags( $post->post_id, array( 'fields' => 'ids' ) );
						$tags = array();
						
						foreach($tag_ids as $tag_id) {
							$tag = get_tag($tag_id);
							$tags[] = '<a href="' . esc_url(get_tag_link( $tag_id )) . '">' . $tag->name . '</a>';
						}
						
						if(count($tag_ids) > 0) {
							$widget_data .= '<span class="wti_widget_tags">' . __('Tags', 'wti-like-post') . ': ' . implode(', ', $tags) . '</span>';
						}
					}
					
					$widget_data .= '</li>';
				}
			} else {
				$widget_data .= '<li>';
				
				if ($instance['vote_type'] == 'unliked') {
					$widget_data .= __('Nothing disliked yet.', 'wti-like-post');
				} else {				
					$widget_data .= __('Nothing liked yet.', 'wti-like-post');
				}
				
				$widget_data .= '</li>';
			}
		} else {
			$widget_data .= '<li>' . __('No category posts available.', 'wti-like-post') . '</li>';
		}
   
		$widget_data .= '</ul>';
		$widget_data .= $after_widget;
   
		echo $widget_data;
     }
}

$WtiMostLikedCategoryPosts = new WtiMostLikedCategoryPosts();
?>