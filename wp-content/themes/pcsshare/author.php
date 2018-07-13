<?php get_header(); ?>

    <!-- Start Page Content -->
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="row">
                <div class="col-lg-4">
                    <h2><?php echo get_query_var('author_name'); ?></h2>
                </div>
                <div class="col-lg-8 content-container">
                    <h2><a href="<?php printf( '%s%s', site_url( '/members/' ), get_query_var('author_name') ) ?>">Visit member profile</a></h2>
                    <?php 
                        $query = pcs_get_posts_by_user( get_the_author_meta( 'ID' ) );

                        if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); 
                    ?>
                        <article class="user-post post-excerpt" id="post-<?php echo the_ID(); ?>" data-link="<?php the_permalink(); ?>" data-post-id="<?php the_ID(); ?>" data-description="<?php the_content(); ?>">
                            <h2 class="post-excerpt-title"><?php the_title(); ?></h2>
                            <h4 class="post-excerpt-link"><?php pcs_post_excerpt_link( get_the_ID() ) ?></h4>
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
                        </article>
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
                    <?php endwhile; else : ?>
                        <p>There are no posts by this user. :(</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- EO Page Content -->

</div>

<?php get_footer(); ?>