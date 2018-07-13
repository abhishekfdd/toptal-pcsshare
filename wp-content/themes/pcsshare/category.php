<?php get_header(); ?>

    <?php pcs_posts_show_create_modal(); ?>

    <?php $category = pcs_get_current_category(); ?>

    <!-- Start Page Content -->
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <h2 class="breadcrumbs"><?php pcs_show_category_breadcrumbs( $category ); ?></h2>
            <div class="row">
                <div class="col-lg-3">
                    <?php pcs_posts_show_create_button(); ?>
                    <?php pcs_show_subcategories_list( pcs_get_parent_category( $category ), $category ); ?>
                </div>
                <div class="col-lg-9 content-container post-excerpt-container">
                    <?php if (pcs_has_flash('create.error')) : ?>
                        <div class="bg-danger bs-callout bs-callout-warning">
                            <p><?php echo pcs_get_flash('create.error'); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (pcs_has_flash('create.success')) : ?>
                        <div class="bg-info bs-callout bs-callout-warning">
                            <p><?php echo pcs_get_flash('create.success'); ?></p>
                            <?php if (pcs_has_flash('create.success.anonymous')) : ?>
                                <p><?php echo pcs_get_flash('create.success.anonymous'); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); $post_id = get_the_ID(); ?>
                        <article class="post-excerpt" data-link="<?php the_permalink(); ?>">
                            <h2 class="post-excerpt-title"><?php the_title(); ?></h2>
                            <h4 class="post-excerpt-link"><?php pcs_post_excerpt_link( $post_id ) ?></h4>
                            <?php GetWtiLikePost(); ?>
                            <footer class="post-excerpt-footer">
								<?php $author = get_the_author_meta( 'user_nicename' ); ?>
								<span class="pull-right">Submitted by <?php echo ( empty($author) || 'anonymous' == $author ? 'Anonymous' : get_the_author_posts_link() ); ?></span>
                                <div class="clearfix"></div>
                            </footer>
                        </article>
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
                    <?php endwhile; else : ?>
                        <p>There are currently no posts in this category. :(</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- EO Page Content -->

    </div>

<?php get_footer(); ?>