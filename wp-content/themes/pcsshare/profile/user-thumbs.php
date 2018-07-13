<?php $posts_thumbed_by_user = pcs_get_posts_liked_by_user( bp_displayed_user_id() ); ?>

<?php if ( count( $posts_thumbed_by_user ) > 0 ): ?>

	<?php $query = new WP_Query([ 'post__in' => $posts_thumbed_by_user ]); ?>
	<?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>

		<article class="post-excerpt" data-link="<?php the_permalink(); ?>">
			<h2 class="post-excerpt-title"><?php the_title(); ?></h2>
			<h4 class="post-excerpt-link"><?php pcs_post_excerpt_link(get_the_ID()) ?></h4>
			<?php GetWtiLikePost(); ?>
			<footer class="post-excerpt-footer">
				<?php $author = get_the_author_meta('user_nicename'); ?>
				<span class="pull-right">Submitted by <?php echo(empty($author) || 'anonymous' == $author ? 'Anonymous' : get_the_author_posts_link()); ?></span>
				<div class="clearfix"></div>
			</footer>
		</article>
		<div class="people-who-liked hidden">
			<ul>
				<?php pcs_users_who_liked_post(get_the_ID()) ?>
			</ul>
		</div>
		<div class="people-who-disliked hidden">
			<ul>
				<?php pcs_users_who_disliked_post(get_the_ID()) ?>
			</ul>
		</div>

	<?php endwhile; endif; ?>

<?php else: ?>
	This user did not like or dislike any posts.
<?php endif; ?>
