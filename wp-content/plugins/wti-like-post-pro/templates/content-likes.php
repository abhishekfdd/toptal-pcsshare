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
	
global $post;	

// Get the author object
$author = get_user_by( 'slug', get_query_var( 'author_name' ) );

// Get the post ids liked by the author
$result = $wpdb->get_results(
					"SELECT P.* FROM $wpdb->posts P JOIN {$wpdb->prefix}wti_like_post L
					ON P.ID = L.post_id	WHERE L.user_id = {$author->ID} AND value > 0
					GROUP BY L.post_id ORDER BY SUM(L.value) DESC"
				);
?>
<div style="clear:both;">
	<h2><?php echo __( 'Favorite Posts', 'wti-like-post' ); ?></h2>
	<div class="site-content">
		<?php
		if ( count ( $result ) > 0 ) :
			?>
			<?php foreach($result as $post): ?>
				<?php setup_postdata($post); ?>
				<article>
					<header class="entry-header">
						<h1 class="entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h1>
					</header>
					<div class="entry-content"><?php the_content(); ?></div>
				</article>
			<?php endforeach; ?>
			<?php
		else :
			?>
			<p><?php _e( 'Nothing Found', 'wti-like-post' ); ?></p>
			<?php
		endif;
		?>	
	</div><!-- #content -->
</div>