<?php
	pcs_posts_show_update_modal();
	pcs_posts_show_delete_modal();

	$query = pcs_get_posts_by_user( bp_displayed_user_id() );
?>

<input type="hidden" value="<?php echo pcs_get_current_url(); ?>" id="pcs-current-location">

<?php if ( $query->have_posts() ) : ?>

	<?php while ( $query->have_posts() ) : $query->the_post(); ?>

		<?php $post_id = get_the_ID(); ?>

		<article class="user-post post-excerpt" id="post-<?php echo $post_id; ?>" data-link="<?php the_permalink(); ?>" data-post-id="<?php echo $post_id; ?>" data-description="<?php echo esc_html( get_the_content() ) ?>">
			<h2 class="post-excerpt-title"><?php the_title(); ?></h2>
			<h4 class="post-excerpt-link"><?php pcs_post_excerpt_link( $post_id ) ?></h4>
			<?php GetWtiLikePost(); ?>
			<footer class="post-excerpt-footer">
				<?php
					if ( pcs_is_displayed_user_author() ) {
						pcs_posts_show_update_button();
						pcs_posts_show_delete_button();
					}

					$author = get_the_author_meta( 'user_nicename' );
				?>
				<span class="pull-right">Submitted by <?php echo ( empty($author) || 'anonymous' == $author ? 'Anonymous' : get_the_author_posts_link() ); ?></span>
				<div class="clearfix"></div>
			</footer>

			<div class="people-who-liked hidden">
				<ul>
					<?php pcs_users_who_liked_post( $post_id ) ?>
				</ul>
			</div>
			<div class="people-who-disliked hidden">
				<ul>
					<?php pcs_users_who_disliked_post( $post_id ) ?>
				</ul>
			</div>
		</article>

	<?php endwhile; ?>

	<?php wp_reset_postdata(); ?>

<?php else : ?>
	<p>There are no posts by this user. :(</p>
<?php endif; ?>
