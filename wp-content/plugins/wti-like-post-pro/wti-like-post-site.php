<?php
/**
 * Get the like output on site
 * @param array
 * @return string
 */
function GetWtiLikePost($arg = null) {
     global $wpdb, $wti_ip_address;
     $post_id = get_the_ID();
     $wti_like_post = '';
	$msg = '';
	
     // Get the posts ids where we do not need to show like functionality
     $allowed_posts = explode(",", get_option('wti_like_post_allowed_posts'));
     $excluded_posts = explode(",", get_option('wti_like_post_excluded_posts'));
	$allowed_categories = get_option('wti_like_post_allowed_categories');
	$excluded_categories = get_option('wti_like_post_excluded_categories');
	$excluded_sections = get_option('wti_like_post_excluded_sections');
	
	if(empty($allowed_categories)) {
		$allowed_categories = array();
	}
	
	if(empty($excluded_categories)) {
		$excluded_categories = array();
	}
	
	if(!empty($excluded_sections)) {
		// Checking for excluded section. if yes, then dont show the like/dislike option
		if((in_array('home', $excluded_sections) && is_home()) ||
				(in_array('archive', $excluded_sections) && is_archive()) ||
					(in_array('search', $excluded_sections) && is_search())) {
			return;
		}
	}
     
	$title_text = get_option('wti_like_post_title_text');
	$category = get_the_category();
	$excluded = false;
	
	if( count($category) > 0 ) {
		$cateory_ids = wp_list_pluck($category, 'term_id');
		
		if(count($excluded_categories) > 0) {
			if(count(array_intersect($cateory_ids, $excluded_categories)) > 0 && !in_array($post_id, $allowed_posts)) {
				$excluded = true;
			}
		} else if(count($allowed_categories) > 0) {
			if(count(array_intersect($cateory_ids, $allowed_categories)) == 0 || in_array($post_id, $excluded_posts)) {
				$excluded = true;
			}
		}
	}
	
	//if excluded category, then dont show the like/dislike option
	if($excluded) {
		return;
	}
	
	// Check for title text. if empty then have the default value
     if(empty($title_text)) {
		$title_text_like = __('Like', 'wti-like-post');
		$title_text_unlike = __('Unlike', 'wti-like-post');
     } else {
		$title_text = explode('/', get_option('wti_like_post_title_text'));
		$title_text_like = $title_text[0];
		$title_text_unlike = $title_text[1];
     }
	
	// Checking for excluded posts
     if(!in_array($post_id, $excluded_posts)) {
		// Get the nonce for security purpose and create the like and unlike urls
		$nonce = wp_create_nonce("wti_like_post_vote_nonce");
		
		// Check for voting settings
		$check_option = get_option('wti_like_post_check_option');
		$voting_period = get_option('wti_like_post_voting_period');
		
		$like_count = GetWtiLikeCount( $post_id );
		$unlike_count = GetWtiUnlikeCount( $post_id );
		$alignment = ("left" == get_option('wti_like_post_alignment')) ? 'align-left' : 'align-right';
		$show_dislike = get_option('wti_like_post_show_dislike');
		$liked_style = $unliked_style = (get_option('wti_like_post_voting_style') == "") ? 'style1' : get_option('wti_like_post_voting_style');
		$show_user_likes = get_option('wti_like_post_show_user_likes');
		$show_like_unlike_text = get_option('wti_like_post_show_like_unlike_text');
		
		// Get voted details
		$voted_result = HasWtiAlreadyVoted( $post_id, $check_option, $voting_period );
		$wti_has_voted = $voted_result['has_voted'];
		$voted_count = $voted_result['voted_count'];
		
		if ( $like_count != 0 || $unlike_count != 0 ) {
			if($wti_has_voted == 1) {
				$msg = get_option('wti_like_post_voted_message');
				
				// Active class if already voted
				if($voted_count > 0) {
					$liked_style .= '-active';
				} else if($voted_count < 0) {
					$unliked_style .= '-active';
				}
			}
		} else if($wti_has_voted == 0) {
			$msg = get_option('wti_like_post_default_message');
		}
		
		$wti_like_post .= "<div class='watch-action'>";
		$wti_like_post .= "<div class='watch-position " . $alignment . "'>";
		$wti_like_post .= "<div class='action-like'>";
		$wti_like_post .= "<a class='lbg-" . $liked_style . " like-" . $post_id . " jlk' data-task='like' data-post_id='" . $post_id . "' data-nonce='" . $nonce . "' rel='nofollow'>";
		
		if($show_like_unlike_text == 1) {
			$wti_like_post .= "<span class='wti-text'>" . get_option('wti_like_post_like_text') . "</span>";
		} else {
			$wti_like_post .= "<img src='" . plugins_url( 'images/pixel.gif' , __FILE__ ) . "' title='" . apply_filters( 'wti_like_post_like_title', $title_text_like, $post_id, $like_count ) . "' />";
		}
		
		$wti_like_post .= "<span class='lc-".$post_id." lc'>" . $like_count . "</span></a></div>";
		
		if($show_dislike) {
			$wti_like_post .= "<div class='action-unlike'>";
			$wti_like_post .= "<a class='unlbg-" . $unliked_style . " unlike-" . $post_id . " jlk' data-task='unlike' data-post_id='" . $post_id . "' data-nonce='" . $nonce . "' rel='nofollow'>";
			
			if($show_like_unlike_text == 1) {
				$wti_like_post .= "<span class='wti-text'>" . get_option('wti_like_post_unlike_text') . "</span>";
			} else {
				$wti_like_post .= "<img src='" . plugins_url( 'images/pixel.gif' , __FILE__ ) . "' title='" . apply_filters( 'wti_like_post_unlike_title', $title_text_unlike, $post_id, $unlike_count ) . "' />";
			}
			
			$wti_like_post .= "<span class='unlc-" . $post_id . " unlc'>" . $unlike_count . "</span></a></div> ";
		}
		
		$wti_like_post .= "</div> ";
          $wti_like_post .= "<div class='status-" . $post_id . " status " . $alignment . "'>" . apply_filters( 'wti_like_post_load_message', $msg, $post_id, $like_count, $unlike_count ) . "</div>";
		$wti_like_post .= "</div><div class='wti-clear'></div>";
		
		if ($show_user_likes) {
			$wti_like_post .= "<div class='wti-user-likes wti-likes-" . $post_id . "'>";
			$wti_like_post .= GetWtiUserLikes($post_id);
			$wti_like_post .= "</div>";
		}
     }
     
     if ($arg == 'put') {
		return apply_filters( 'get_wti_like_post', $wti_like_post );
     } else {
		echo apply_filters( 'get_wti_like_post', $wti_like_post );
     }
}

/**
 * Show the like content
 * @param $content string
 * @param $param string
 * @return string
 */
function PutWtiLikePost($content) {
	
	// Show like/unlike buttons for selected posts
	$post_types_to_use = array();
	$post_type_to_use = trim(get_option('wti_like_post_post_types'));
	
	// If no post type is set, then use post by default
	if(empty($post_type_to_use)) {
		$post_types_to_use = array('post');
	} else {
		$post_types_to_use = explode(',', $post_type_to_use);
	}
	
	if (!is_feed() && in_array(get_post_type(), $post_types_to_use)) {
		$wti_like_post_content = GetWtiLikePost('put');
		$wti_like_post_position = get_option('wti_like_post_position');
		
		if (strpos($content, "[wtilp_buttons]") !== FALSE) {
			$content = str_replace('[wtilp_buttons]', $wti_like_post_content, $content);
		} else {
			if ($wti_like_post_position == 'top') {
				$content = $wti_like_post_content . $content;
			} elseif ($wti_like_post_position == 'bottom') {
				$content = $content . $wti_like_post_content;
			} else {
				$content = $wti_like_post_content . $content . $wti_like_post_content;
			}
		}
	}
	
     return $content;
}

/**
 * Filter for showing the like/unlike with post excerpts
 * @param string $excerpt
 * @return string
 */
function ExcerptWtiLikePost($excerpt) {
     if (!is_feed()) {
		$wti_like_post_excerpt = GetWtiLikePost('put');
		$wti_like_post_position = get_option('wti_like_post_position');
		
		if ($wti_like_post_position == 'top') {
			$excerpt = $wti_like_post_excerpt . $excerpt;
		} elseif ($wti_like_post_position == 'bottom') {
			$excerpt = $excerpt . $wti_like_post_excerpt;
		} else {
			$excerpt = $wti_like_post_excerpt . $excerpt . $wti_like_post_excerpt;
		}
     }

     return $excerpt;
}

/**
 * Get already voted message
 * @param $post_id integer
 * @return string
 */
function GetWtiVotedMessage($post_id) {
     global $wpdb;
	$wti_voted_message = null;
	
	$voted_result = HasWtiAlreadyVoted( $post_id );
	$wti_has_voted = $voted_result['has_voted'];
	
	if($wti_has_voted == 1) {
		$wti_voted_message = get_option('wti_like_post_voted_message');
     } else if($wti_has_voted == 0) {
		$wti_voted_message = get_option('wti_like_post_default_message');
	}
     
     return $wti_voted_message;
}

add_shortcode('most_liked_posts', 'WtiMostLikedPostsShortcode');

/**
 * Most liked posts shortcode
 * @param $args array
 * @return string
 */
function WtiMostLikedPostsShortcode($args) {
     global $wpdb;
     $most_liked_post = '';
     
     // Getting the most liked posts
     $query = "SELECT post_id, SUM(value) AS like_count, post_title, post_author FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
     $query .= "WHERE L.post_id = P.ID AND post_status = 'publish'";
	
	// Get the posts for specific post type
	if(isset($args['post_type']) && !empty($args['post_type'])) {
		$post_type = trim($args['post_type']);
		$query .= " AND P.post_type IN ('" . str_replace(",", "','", $post_type) . "')";
	}

	if(isset($args['vote_type'])) {
		// Check to see whether like or unlike posts need to be shown
		if($args['vote_type'] == 'unliked') {
			$query .= " AND value < 0";
			$order_by = "ORDER BY like_count ASC, post_title ASC";
		} elseif($args['vote_type'] == 'total')  {
			$order_by = " ORDER BY like_count DESC, post_title ASC";
		} else {
			$query .= " AND value > 0";
			$order_by = "ORDER BY like_count DESC, post_title ASC";
		}
	} else {
		$query .= " AND value > 0";
		$order_by = "ORDER BY like_count DESC, post_title ASC";
	}
	
	if(isset($args['category']) && !empty($args['category'])) {
		// Get the posts for specific category if selected
		$cat_posts = array();
		$category_posts = get_posts(
							array(
								'category' => $args['category'],
								'numberposts' => -1
							)
						);
		
		if(count($category_posts) > 0) {
			foreach($category_posts as $category_post) {
				$cat_posts[] = $category_post->ID;
			}
		}
	
		if(count($cat_posts) > 0) {
			$query .= " AND P.ID IN (" . implode(',', $cat_posts) . ")";
		}
	}
	
	// Gets posts liked for selected time range
	if (isset($args['time']) && $args['time'] != 'all') {
		$last_date = GetWtiLastDate($args['time']);
		$query .= " AND date_time >= '$last_date'";
	}
	
	$limit = isset($args['limit']) ? $args['limit'] : 10;
	$query .= " GROUP BY post_id $order_by LIMIT $limit";
     $posts = $wpdb->get_results($query);

     if (count($posts) > 0) {
		$most_liked_post .= '<table class="most-liked-posts-table">';
		$most_liked_post .= '<tr>';
		$most_liked_post .= '<th>' . __('Title', 'wti-like-post') . '</th>';
		$most_liked_post .= '<th>' . __('Like Count', 'wti-like-post') . '</th>';
		
		if (isset($args['author']) && $args['author'] == 'yes') {
			$most_liked_post .= '<th>' . __('Author', 'wti-like-post') . '</th>';
		}
		
		$most_liked_post .= '</tr>';
	  
          foreach ($posts as $post) {
               $post_title = stripslashes($post->post_title);
               $permalink = get_permalink($post->post_id);
               $like_count = $post->like_count;
               
               $most_liked_post .= '<tr>';
			$most_liked_post .= '<td><a href="' . $permalink . '" title="' . $post_title . '">' . $post_title . '</td>';
               $most_liked_post .= '<td>' . ((isset($args['vote_type']) && $args['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . '</td>';
			
			if (isset($args['author']) && $args['author'] == 'yes') {
				$most_liked_post .= '<td><a href="' . get_author_posts_url($post->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post->post_author) . '</a></td>';
			}

               $most_liked_post .= '</tr>';
          }
	  
		$most_liked_post .= '</table>';
     } else {
		if(isset($args['vote_type']) && $args['vote_type'] == 'unliked') {
			$most_liked_post .= '<p>' . __('Nothing disliked yet.', 'wti-like-post') . '</p>';
		} else {
			$most_liked_post .= '<p>' . __('Nothing liked yet.', 'wti-like-post') . '</p>';
		}
     }
     
     return $most_liked_post;
}

add_shortcode('my_most_liked_posts', 'WtiMyMostLikedPostsShortcode');

/**
 * Most liked posts shortcode
 * @param $args array
 * @return string
 */
function WtiMyMostLikedPostsShortcode($args) {
     global $wpdb, $current_user;
     $most_liked_post = '';
     
	if ($current_user->ID == 0) {
		$most_liked_post .= '<p>' . __('Please login to see the posts you like.', 'wti-like-post') . '</p>';
	} else {
		// Getting the most liked posts
		$query = "SELECT post_id, SUM(value) AS like_count, post_title, post_author FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
		$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND user_id = {$current_user->ID}";

		// Get the posts for specific post type
		if(isset($args['post_type']) && !empty($args['post_type'])) {
			$post_type = trim($args['post_type']);
			$query .= " AND P.post_type IN ('" . str_replace(",", "','", $post_type) . "')";
		}

		if(isset($args['vote_type'])) {
			// Check to see whether like or unlike posts need to be shown
			if($args['vote_type'] == 'unliked') {
				$query .= " AND value < 0";
				$order_by = "ORDER BY like_count ASC, post_title ASC";
			} elseif($args['vote_type'] == 'total')  {
				$order_by = " ORDER BY like_count DESC, post_title ASC";
			} else {
				$query .= " AND value > 0";
				$order_by = "ORDER BY like_count DESC, post_title ASC";
			}
		} else {
			$query .= " AND value > 0";
			$order_by = "ORDER BY like_count DESC, post_title ASC";
		}
		
		if(isset($args['category']) && !empty($args['category'])) {
			// Get the posts for specific category if selected
			$cat_posts = array();
			$category_posts = get_posts(
								array(
									'category' => $args['category'],
									'numberposts' => -1
								)
							);
			
			if(count($category_posts) > 0) {
				foreach($category_posts as $category_post) {
					$cat_posts[] = $category_post->ID;
				}
			}
		
			if(count($cat_posts) > 0) {
				$query .= " AND P.ID IN (" . implode(',', $cat_posts) . ")";
			}
		}
		
		// Gets posts liked for selected time range
		if (isset($args['time']) && $args['time'] != 'all') {
			$last_date = GetWtiLastDate($args['time']);
			$query .= " AND date_time >= '$last_date'";
		}
		
		$limit = isset($args['limit']) ? $args['limit'] : 10;
		$query .= " GROUP BY post_id $order_by LIMIT $limit";
		$posts = $wpdb->get_results($query);
	 
		if (count($posts) > 0) {
			$most_liked_post .= '<table class="my-most-liked-posts-table">';
			$most_liked_post .= '<tr>';
			$most_liked_post .= '<th>' . __('Title', 'wti-like-post') . '</th>';
			$most_liked_post .= '<th>' . __('Like Count', 'wti-like-post') . '</th>';
			
			if (isset($args['author']) && $args['author'] == 'yes') {
				$most_liked_post .= '<th>' . __('Author', 'wti-like-post') . '</th>';
			}
			
			$most_liked_post .= '</tr>';
		  
			foreach ($posts as $post) {
				$post_title = stripslashes($post->post_title);
				$permalink = get_permalink($post->post_id);
				$like_count = $post->like_count;
				
				$most_liked_post .= '<tr>';
				$most_liked_post .= '<td><a href="' . $permalink . '" title="' . $post_title . '">' . $post_title . '</td>';
				$most_liked_post .= '<td>' . ((isset($args['vote_type']) && $args['vote_type'] == 'total') ? $like_count : str_replace('-', '', $like_count)) . '</td>';
				
				if (isset($args['author']) && $args['author'] == 'yes') {
					$most_liked_post .= '<td><a href="' . get_author_posts_url($post->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post->post_author) . '</a></td>';
				}
	
				$most_liked_post .= '</tr>';
			}
		  
			$most_liked_post .= '</table>';
		} else {
			if(isset($args['vote_type']) && $args['vote_type'] == 'unliked') {
				$most_liked_post .= '<p>' . __('Nothing disliked yet.', 'wti-like-post') . '</p>';
			} else {
				$most_liked_post .= '<p>' . __('Nothing liked yet.', 'wti-like-post') . '</p>';
			}
		}
	}
     
     return $most_liked_post;
}

add_shortcode('recently_liked_posts', 'WtiRecentlyLikedPostsShortcode');

/**
 * Get recently liked posts shortcode
 * @param $args array
 * @return string
 */
function WtiRecentlyLikedPostsShortcode($args) {
     global $wpdb;
	$where = '';
     $recently_liked_post = '';
     
     if(isset($args['limit']) && $args['limit']) {
		$limit = $args['limit'];
     } else {
		$limit = 10;
     }
	
	$show_excluded_posts = get_option('wti_like_post_show_on_widget');
	$excluded_post_ids = explode(',', get_option('wti_like_post_excluded_posts'));
	
	if(!$show_excluded_posts && count($excluded_post_ids) > 0) {
		$where = "AND post_id NOT IN (" . get_option('wti_like_post_excluded_posts') . ")";
	}
	
	$recent_ids = $wpdb->get_col("SELECT DISTINCT(post_id) FROM `{$wpdb->prefix}wti_like_post` $where ORDER BY date_time DESC");
		
	if(count($recent_ids) > 0) {
		$where = "AND post_id IN(" . implode(",", $recent_ids) . ")";
	}
	
	// Getting the most liked posts
	$query = "SELECT post_id, SUM(value) AS like_count, post_title, post_author FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
	$query .= "WHERE L.post_id = P.ID AND post_status = 'publish'";
	
	// Get the posts for specific post type
	if(isset($args['post_type']) && !empty($args['post_type'])) {
		$post_type = trim($args['post_type']);
		$query .= " AND P.post_type IN ('" . str_replace(",", "','", $post_type) . "')";
	}
	
	$query .= " AND value > 0 $where GROUP BY post_id ORDER BY date_time DESC LIMIT $limit";

	$posts = $wpdb->get_results($query);

     if(count($posts) > 0) {
		$recently_liked_post .= '<table class="recently-liked-posts-table">';
		$recently_liked_post .= '<tr>';
		$recently_liked_post .= '<th>' . __('Title', 'wti-like-post') . '</th>';
			
		if (isset($args['author']) && $args['author'] == 'yes') {
			$recently_liked_post .= '<th>' . __('Author', 'wti-like-post') . '</th>';
		}
		
		$recently_liked_post .= '</tr>';
	  
          foreach ($posts as $post) {
               $post_title = stripslashes($post->post_title);
               $permalink = get_permalink($post->post_id);
               
               $recently_liked_post .= '<tr>';
			$recently_liked_post .= '<td><a href="' . $permalink . '" title="' . $post_title . '">' . $post_title . '</a></td>';
				
			if (isset($args['author']) && $args['author'] == 'yes') {
				$recently_liked_post .= '<td><a href="' . get_author_posts_url($post->post_author) . '" target="_blank">' . get_the_author_meta('user_nicename', $post->post_author) . '</a></td>';
			}

               $recently_liked_post .= '</tr>';
          }
	  
		$recently_liked_post .= '</table>';
     } else {
		$recently_liked_post .= '<p>' . __('Nothing liked yet.', 'wti-like-post') . '</p>';
     }
     
     return $recently_liked_post;
}

/**
 * Add the javascript for the plugin
 * @param no-param
 * @return string
 */
function WtiLikePostEnqueueScripts() {
	$style = (get_option('wti_like_post_voting_style') == "") ? 'style1' : get_option('wti_like_post_voting_style');
	$redirect_url = get_option('wti_like_post_redirect_url');
	
	wp_register_script( 'wti_like_post_script', plugins_url( 'js/wti-like-post.js', __FILE__ ), array('jquery') );
	wp_localize_script( 'wti_like_post_script', 'wtilp', array(
												    'ajax_url' => admin_url( 'admin-ajax.php' ),
												    'redirect_url' => $redirect_url,
												    'style' => $style
												)
				    );
  
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'wti_like_post_script' );
}

/**
 * Add the stylesheet for the plugin
 * @param no-param
 * @return string
 */
function WtiLikePostAddHeaderLinks() {
     echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( 'css/wti-like-post.css' , __FILE__ ) . '" media="screen" />'."\n";
}

/*add_shortcode('wtilp_buttons', 'ShortGetWtiLikePost');

function ShortGetWtiLikePost($arg)
{
	return GetWtiLikePost('put');
}*/