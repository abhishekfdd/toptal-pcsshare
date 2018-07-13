<?php get_header(); ?>

  <div class="row"><!-- Start Page Content -->
    <div class="col-lg-6 col-lg-offset-3">
      <?php
        global $wp_query, $wpdb;

        $page_id = $wpdb->get_var( $wp_query->request );
        $post_status = get_post_status( $page_id );

        if ( $post_status == 'trash' ) {
            echo '<p class="intro">The post you commented on has been deleted</p>';
        } else {
            echo '<p class="intro">Could not find what you are looking for.</p>';
        }
    ?>
    </div>
  </div><!-- EO Page Content -->

<?php get_footer(); ?>
