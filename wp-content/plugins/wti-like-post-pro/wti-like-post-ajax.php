<?php
/**
 * Updating all post like, unlike, total meta counts
 * @param no-param
 * @return json data
 */
function WtiLikePostUpdateMeta()
{
	// Check for valid access
	if ( !wp_verify_nonce( $_REQUEST['nonce'], "wti_like_post_update_nonce" ) ) {
		$error = 1;
		$msg = __( 'Invalid access', 'wti-like-post' );
		$result = array(
					'msg' => $msg,
					'error' => $error
				);
	} else {
		$count = WtiUpdateLikeMetaData();
		$result = array(
					'msg' => $count . __( ' like/unlike/total post metadata updated successfully', 'wti-like-post' ),
					'error' => 0
				);
	}
	
	// Check for method of processing the data
	if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
		header('content-type: application/json; charset=utf-8');
		header('access-control-allow-origin: *');
		
		echo json_encode( $result );
	} else {
		header( "location:" . $_SERVER["HTTP_REFERER"] );
	}
	
	exit;
}

/**
 * Processing like/unlike voting activity
 * @param no-param
 * @return json data
 */
function WtiLikePostProcessVote()
{
	global $wpdb, $wti_ip_address;
	
	$post_id = (int)$_REQUEST['post_id'];
	$task = $_REQUEST['task'];
	$can_vote = $can_change_mind = false;
	$voted_id = 0;
	
	// Check for valid access
	if ( !wp_verify_nonce( $_REQUEST['nonce'], 'wti_like_post_vote_nonce' ) ) {
		$error = 1;
		$msg = __( 'Invalid access', 'wti-like-post' );
		
		$show_user_likes = get_option( 'wti_like_post_show_user_likes' );
		$user_likes = null;
		
		if ( $show_user_likes ) {
			$user_likes = GetWtiUserLikes( $post_id, $show_user_likes );
		}
		
		// Get like/dislike count
		$wti_like_count = GetWtiLikeCount( $post_id );
		$wti_unlike_count = GetWtiUnlikeCount( $post_id );
		$wti_total_count = GetWtiTotalCount( $post_id );
		
		$check_option = get_option( 'wti_like_post_check_option' );
		$voting_period = get_option( 'wti_like_post_voting_period' );
	} else {
		// Get setting data
		$is_logged_in = is_user_logged_in();
		$login_required = get_option( 'wti_like_post_login_required' );
		$check_option = get_option( 'wti_like_post_check_option' );
		$datetime_now = date( 'Y-m-d H:i:s' );
		
		// Get the voting period to check for revoting
		$voting_period = get_option( 'wti_like_post_voting_period' );
		$disallow_author_voting = get_option( 'wti_like_post_disallow_author_voting' );
		
		// Get user details
		$current_user = wp_get_current_user();
		$user_id = (int)$current_user->ID;
		
		if ( ( $login_required && !$is_logged_in ) || ( $check_option == 1  && !$is_logged_in ) ) {
			// User needs to login to vote but has not logged in
			$error = 1;
			$msg = get_option( 'wti_like_post_login_message' );
		} else {
			$cookie_value = microtime(true) . rand( 1, 99999 );
			
			// Check whether user has already voted or not
			$voted_result = HasWtiAlreadyVoted( $post_id, $check_option, $voting_period, $get_voted_id = true );
			$has_already_voted = $voted_result['has_voted'];
			$voted_id = $voted_result['voted_id'];
			$voted_count = $voted_result['voted_count'];
			
			// Get the post details
			$post_data = get_post($post_id);
			
			if ( $disallow_author_voting && $user_id == $post_data->post_author ) {
				// Author should not be allowed to vote his/her own post.
				$error = 1;
				$msg = get_option( 'wti_like_post_author_disallowed_message' );
			} else {
				if( "once" == $voting_period && $has_already_voted == 1 ) {
					// Check for change of mind
					if ( ( $voted_count >= 0 && $task == 'unlike' ) || ( $voted_count <= 0 && $task == 'like' ) ) {
						$can_change_mind = true;
					} else {
						// User can vote only once and has already voted.
						$error = 1;
						$msg = get_option( 'wti_like_post_voted_message' );
					}
				} elseif ( 0 == $voting_period ) {
					// User can vote as many times as he want
					$can_vote = true;
				} else {
					if ( !$has_already_voted ) {
						// Never voted befor so can vote
						$can_vote = true;
					} else {
						// Get the last date when the user had voted
						$last_voted_date = GetWtiLastVotedDate( $post_id, $check_option, $user_id );
						
						// Get the next voted date when user can vote
						$next_vote_date = GetWtiNextVoteDate( $last_voted_date, $voting_period );
						
						if ( $next_vote_date > $datetime_now ) {
							// Check for change of mind
							if ( ( $voted_count >= 0 && $task == 'unlike' ) || ( $voted_count <= 0 && $task == 'like' ) ) {
								$can_change_mind = true;
							} else {
								$revote_duration = strtotime( $next_vote_date ) - strtotime( $datetime_now );
								
								if( $revote_duration > 86400 ) {
									// In terms of days
									$revote_message = ceil($revote_duration / (3600 * 24)) . __( 'day(s)', 'wti-like-post' );
								} else if ( $revote_duration > 3600 ) {
									// In terms of hour and minute
									$revote_hours = (int)( $revote_duration / 3600 );
									$revote_mins = (int)( ( $revote_duration % 3600 ) / 60 );
									$revote_message = $revote_hours . __( 'hour(s)', 'wti-like-post' ) . ' ' . $revote_mins . __( 'minute(s)', 'wti-like-post' );
								} else {
									// In terms of minutes
									$revote_mins = (int)( $revote_duration / 60 );
									$revote_message = $revote_mins . __( 'minute(s)', 'wti-like-post' );
								}
								
								$error = 1;
								$msg = __( 'You can vote after', 'wti-like-post' ) . ' ' . $revote_message;
							}
						} else {
							$can_vote = true;
						}
					}
				}
			}
		}
		
		if ( $can_vote ) {
			if ( $task == "like" ) {
				if ( $has_already_voted > 0 ) {
					switch ( $check_option ) {
						case '2':
							// Cookies
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value + 1, ";
							$query .= "ip = '$wti_ip_address', ";
							$query .= "user_id = $user_id, ";
							$query .= "date_time = '" . $datetime_now . "', ";
							$query .= "cookie_value = '" . $cookie_value . "' ";
							$query .= "WHERE post_id = '" . $post_id . "' AND ";
							$query .= "cookie_value = '" . $_COOKIE["wtilp_count_{$post_id}"] . "'";
							break;
						case '1':
							// User Id
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value + 1, ";
							$query .= "ip = '$wti_ip_address', ";
							$query .= "cookie_value = '$cookie_value', ";
							$query .= "date_time = '" . $datetime_now . "' ";
							$query .= "WHERE id = '" . $voted_id . "'";
							break;
						case '0':
						default:
							// IP
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value + 1, ";
							$query .= "user_id = $user_id, ";
							$query .= "cookie_value = '$cookie_value', ";
							$query .= "date_time = '" . $datetime_now . "' ";
							$query .= "WHERE id = '" . $voted_id . "'";
							break;
					}
				} else {
					if ( $voted_id > 0 ) {
						$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
						$query .= "value = value + 1, ";
						$query .= "user_id = $user_id, ";
						$query .= "ip = '$wti_ip_address', ";
						$query .= "cookie_value = '$cookie_value', ";
						$query .= "date_time = '" . $datetime_now . "' ";
						$query .= "WHERE id = '" . $voted_id . "'";
					} else {
						$query = "INSERT INTO {$wpdb->prefix}wti_like_post SET ";
						$query .= "post_id = '" . $post_id . "', ";
						$query .= "value = '1', ";
						$query .= "user_id = $user_id, ";
						$query .= "date_time = '" . $datetime_now . "', ";
						$query .= "ip = '$wti_ip_address', ";
						$query .= "cookie_value = '$cookie_value'";
					}
				}
			} else {
				if ( $has_already_voted > 0 ) {
					switch ( $check_option ) {
						case '2':
							// Cookies
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value - 1, ";
							$query .= "ip = '$wti_ip_address', ";
							$query .= "user_id = $user_id, ";
							$query .= "date_time = '" . $datetime_now . "', ";
							$query .= "cookie_value = '" . $cookie_value . "' ";
							$query .= "WHERE post_id = '" . $post_id . "' AND ";
							$query .= "cookie_value = '" . $_COOKIE["wtilp_count_{$post_id}"] . "'";
							break;
						case '1':
							// User Id
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value - 1, ";
							$query .= "ip = '$wti_ip_address', ";
							$query .= "cookie_value = '$cookie_value', ";
							$query .= "date_time = '" . $datetime_now . "' ";
							$query .= "WHERE id = '" . $voted_id . "'";
							break;
						case '0':
						default:
							// IP
							$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
							$query .= "value = value - 1, ";
							$query .= "user_id = $user_id, ";
							$query .= "cookie_value = '$cookie_value', ";
							$query .= "date_time = '" . $datetime_now . "' ";
							$query .= "WHERE id = '" . $voted_id . "'";
							break;
					}
				} else {
					if ( $voted_id > 0 ) {
						$query = "UPDATE {$wpdb->prefix}wti_like_post SET ";
						$query .= "value = value - 1, ";
						$query .= "user_id = $user_id, ";
						$query .= "ip = '$wti_ip_address', ";
						$query .= "cookie_value = '$cookie_value', ";
						$query .= "date_time = '" . $datetime_now . "' ";
						$query .= "WHERE id = '" . $voted_id . "'";
					} else {
						$query = "INSERT INTO {$wpdb->prefix}wti_like_post SET ";
						$query .= "post_id = '" . $post_id . "', ";
						$query .= "value = '-1', ";
						$query .= "user_id = $user_id, ";
						$query .= "date_time = '" . $datetime_now . "', ";
						$query .= "ip = '$wti_ip_address', ";
						$query .= "cookie_value = '$cookie_value'";
					}
					
					//WtiLikePostAddLikeData($post_id, $value, $user_id, $date_time, $wti_ip_address, $cookie_value);
				}
			}

			$success = $wpdb->query( $query );
			
			if ( $success ) {
				// Update the last voted time for the post
				update_post_meta( $post_id, '_wti_last_voted_time', $datetime_now );
		
				$error = 0;
				$msg = get_option( 'wti_like_post_thank_message' );
				
				// Check for buddypress integration
				$bp_like_activity = get_option( 'wti_like_post_bp_like_activity' );
				
				// Integrate with buddypress if installed
				if ( function_exists( 'bp_is_active' ) && bp_is_active( 'activity' ) && $bp_like_activity > 0 ) {
					if ( $user_id > 0 ) {
						// Record this on the user's profile
						$from_user_link = bp_core_get_userlink( $user_id );
						$post_link = "<a href='" . get_permalink( $post_id ) . "'>" . $post_data->post_title . "</a>";
						$primary_link = bp_core_get_userlink( $user_id, false, true );
						
						if ( ( $bp_like_activity == 1 || $bp_like_activity == 3 ) && $task == 'like' ) {
							$activity_action = sprintf( __( '%s liked %s', 'wti-like-post' ), $from_user_link, $post_link );
							WtiLikePostAddBpActivity( $user_id, $activity_action, $primary_link, __( 'liked', 'wti-like-post' ) . ' ' . $post_link );
						} elseif ( ( $bp_like_activity == 2 || $bp_like_activity == 3 ) && $task == 'unlike' ) {
							$activity_action = sprintf( __( '%s disliked %s', 'wti-like-post' ), $from_user_link, $post_link );
							WtiLikePostAddBpActivity( $user_id, $activity_action, $primary_link, __( 'disliked', 'wti-like-post' ) . ' ' . $post_link );
						}
					}
				}
				
				// Do the action
				do_action( 'wti_like_post_vote_action', $post_id, $wti_ip_address, $user_id, $task, $msg, $error );
				
				// Remove all the entries with 0 values
				//$wpdb->query("DELETE FROM {$wpdb->prefix}wti_like_post WHERE `value` = 0");
				
				// Set the cookie for 1 year
				//setcookie("wtilp_count_{$post_id}", $cookie_value, time() + 3600 * 24 * 365);
			} else {
				$error = 1;
				$msg = __( 'Could not process your vote.', 'wti-like-post' );
			}
		}
		
		// Allow user to cancel voting
		if ( $can_change_mind ) {
			$query = "DELETE FROM {$wpdb->prefix}wti_like_post WHERE id = '" . $voted_id . "'";
			
			if ( $wpdb->query( $query ) ) {
				$error = 0;
				$msg = __( 'Your vote was cancelled.', 'wti-like-post' );
			} else {
				$error = 1;
				$msg = __( 'Could not cancel your vote.', 'wti-like-post' );
			}
		}
		
		$show_user_likes = get_option( 'wti_like_post_show_user_likes' );
		$user_likes = null;
		
		if ( $show_user_likes ) {
			$user_likes = GetWtiUserLikes( $post_id, $show_user_likes );
		}
		
		$options = get_option( 'wti_most_liked_posts' );
		$number = $options['number'];
		$show_count = $options['show_count'];
		
		// Get like/dislike count
		$wti_like_count = GetWtiLikeCount( $post_id );
		$wti_unlike_count = GetWtiUnlikeCount( $post_id );
		
		// Update post meta
		update_post_meta( $post_id, '_wti_like_count', (int)str_replace( '+', '', $wti_like_count ) );
		update_post_meta( $post_id, '_wti_unlike_count', (int)str_replace( '-', '', $wti_unlike_count ) );
		
		$wti_total_count = GetWtiTotalCount( $post_id );
		update_post_meta( $post_id, '_wti_total_count', $wti_total_count );
	}
	
	// Check own vote count
	$voted_result = HasWtiAlreadyVoted( $post_id, $check_option, $voting_period );
	$voted_count = $voted_result['voted_count'];

	// Create the complete response
	$result = array(
				"msg" 		=> apply_filters( 'wti_like_post_ajax_message', $msg, $error ),
				"error" 		=> $error,
				"like" 		=> $wti_like_count,
				"unlike" 		=> $wti_unlike_count,
				"total" 		=> $wti_total_count,
				"own_count"	=> $voted_count,
				"users"		=> $user_likes
			);
	
	// Check for method of processing the data
	if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
		header('content-type: application/json; charset=utf-8');
		header('access-control-allow-origin: *');
		
		echo json_encode( $result );
	} else {
		header( 'location:' . $_SERVER['HTTP_REFERER'] );
	}
	
	exit;
}