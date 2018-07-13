<?php
/**
 * Template Name: Recently Liked Posts Page Template
 *
 * Description: Twenty Twelve loves the no-sidebar look as much as
 * you do. Use this page template to remove the sidebar from any page.
 *
 * Tip: to remove the sidebar from all posts and pages simply remove
 * any active widgets from the Main Sidebar area, and the sidebar will
 * disappear everywhere.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
		<?php
		// Query to get recently liked posts
		global $post;
		$result = $wpdb->get_results(
								"SELECT P.* FROM $wpdb->posts P JOIN {$wpdb->prefix}wti_like_post L
								ON P.ID = L.post_id	WHERE P.post_status = 'publish' AND value > 0
								ORDER BY date_time DESC"
							);
		if ( count ( $result ) > 0 ) :
			?>
			<?php foreach ( $result as $post ): ?>
				<?php setup_postdata($post); ?>
				<article>
					<header class="entry-header">
						<h1 class="entry-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h1>
					</header>
					<div class="entry-content"><?php the_content(); ?></div>
					<?php GetWtiLikePost(); ?>
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
	</div><!-- #primary -->

<?php get_sidebar( ); ?>
<?php get_footer(); ?>