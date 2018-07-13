<?php get_header(); ?>

    <!-- Start Page Content -->
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <?php while ( have_posts() ) : the_post(); ?>
                <h2 class="text-center"><?php the_title(); ?></h2>
                <?php the_content(); ?>
            <?php endwhile; ?>
        </div>
    </div>
    <!-- EO Page Content -->

<?php get_footer(); ?>
