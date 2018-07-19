<?php get_header(); ?>

	<?php pcs_posts_show_update_modal(); ?>
	<?php pcs_posts_show_delete_modal(); ?>

	<?php pcs_comments_show_update_modal(); ?>
	<?php pcs_comments_show_delete_modal(); ?>

	<!-- Start Page Content -->
	<div class="row">

		<div class="col-lg-6 col-lg-offset-3">

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<h2 class="breadcrumbs"><?php pcs_show_category_breadcrumbs( get_the_category()[0], false ); ?></h2>
				<input type="hidden" value="<?php echo pcs_get_last_breadcrumb_link( pcs_get_category_breadcrumbs( get_the_category()[0] ) ); ?>" id="pcs-current-location">

				<article class="user-post" id="post-<?php echo get_the_ID(); ?>" data-link="<?php the_permalink(); ?>" data-post-id="<?php echo get_the_ID(); ?>" data-description="<?php echo get_the_content(); ?>">
					<div class="row">
					  <div class="col-xs-12">
						  <header class="post-title">
							  <h3>
								  <span class="post-excerpt-title"><?php echo get_the_title(); ?></span>
                  <?php if ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
									  <span><button class="btn btn-default btn-update-post" data-toggle="modal" data-target=".update-post-modal"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button></span>
									  <span><?php pcs_posts_show_delete_button(); ?></span>
                  <?php endif; ?>
							  </h3>
							  <h4 class="post-excerpt-link"><?php pcs_post_excerpt_link( get_the_ID() ) ?></h4>
						  </header>
					  </div>
						<div class="col-xs-12">
							<hr class="m-t-15 m-b-15"/>
							<div class="row">
								<div class="col-xs-12 col-md-6">
									<div class="get-wti-like-post-wrapper">
										<p>Was this treatment effective for you?</p>
                    <?php GetWtiLikePost(); ?>
									</div>
								</div>
								<div class="col-xs-12 col-md-6">
									<footer class="post-excerpt-footer">
                    <?php $author = get_the_author_meta( 'user_nicename' ); ?>
										<p class="text-right">Submitted by <?php echo ( empty($author) || 'anonymous' == $author ? 'Anonymous' : get_the_author_posts_link() ); ?></p>
										<div class="clearfix"></div>
									</footer>
								</div>
							</div>
						</div>
					</div>



				</article>

				<?php if ( pcs_has_flash( 'comment.success.anonymous' ) ) : ?>
					<p class="bg-success"><?php echo pcs_get_flash( 'comment.success.anonymous' ); ?></p>
				<?php endif; ?>

				<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>

			<?php endwhile; endif; ?>

		</div>

		<div class="people-who-liked hidden">
			<ul>
				<?php pcs_users_who_liked_post( get_the_ID() ) ?>
			</ul>
		</div>

		<div class="people-who-disliked hidden">
			<ul>
				<?php pcs_users_who_disliked_post( get_the_ID() ) ?>
			</ul>
		</div>

	  </div><!-- EO Page Content -->

	</div>

<?php get_footer(); ?>
