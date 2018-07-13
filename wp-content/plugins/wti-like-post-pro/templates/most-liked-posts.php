<?php
/**
 * Template Name: Most Liked Posts
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
		// Get the current page
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		
		// Query to get all posts sorted by like count after a specific date
		$the_query = new WP_Query(
					array(
						'post_type' => 'post',
						'posts_per_page' => get_option('posts_per_page'),
						'paged' => $paged,
						'meta_key' => '_wti_like_count',
						'orderby' => 'meta_value_num',
						'order' => 'DESC'
					)
				);
		
		if ( $the_query->have_posts() ) :
			?>
			<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>
			
			<nav role="navigation" class="navigation" id="nav-below">
				<div class="nav-previous alignleft">
					<?php previous_posts_link('&laquo; Newer posts'); ?>
				</div>
				
				<div class="nav-next alignright">
					<?php next_posts_link( 'Older posts &raquo;', $the_query->max_num_pages ); ?>
				</div>
			</nav>
			
			<?php
			/* Restore original Post Data */
			wp_reset_postdata();
		else :
			get_template_part( 'content', 'none' );
		endif;
		?>
		
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar( ); ?>
<?php get_footer(); ?>