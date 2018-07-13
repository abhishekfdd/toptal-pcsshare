<?php
/**
 * The template for displaying Author Archive pages.
 *
 * Used to display list of posts liked by an author.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

// Get the author object
$author = get_user_by( 'slug', get_query_var( 'author_name' ) );

// Get the like count by the author
$like_count = $wpdb->get_var("SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post
						WHERE user_id = {$author->ID} AND value > 0");

echo (int)$like_count;
?>
<div style="clear:both;">
	<h2><?php printf( __( 'Posts liked by %s', 'wti-like-post' ), get_the_author() ); ?></h2>
	<?php
	if (count($like_ids) > 0) :
		/*echo '<ul>';
		foreach ($like_ids as $like_id) :
			$post_details = get_post($like_id);
			echo '<li style="padding:5px 0px;">';
			echo '<a href="' . get_permalink($like_id) . '">' . $post_details->post_title . '</a>';
			echo '</li>';
		endforeach;
		echo '</ul>';*/
		echo '<table>';
		foreach ($like_ids as $like_data) :
			$post_details = get_post($like_data->post_id);
			echo '<tr>';
			echo '<td><a href="' . get_permalink($like_data->post_id) . '">' . $post_details->post_title . '</a></td>';
			echo '<td>' . $like_data->value . '</td>';
			echo '</tr>';
		endforeach;
		echo '</ul>';
	else : ?>
		<p><?php _e( 'Nothing Found', 'wti-like-post' ); ?></p>
	<?php
	endif;
	?>
	<div style="clear:both;"></div><br />
</div>