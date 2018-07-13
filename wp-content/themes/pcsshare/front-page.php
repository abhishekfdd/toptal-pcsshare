<?php get_header(); ?>

	<!-- Start Slider -->
	<div class="row">
		<div class="col-lg-12 slider">
			<?php pcs_show_posts_slider(); ?>
		</div>
	</div>
	<!--EO Slider -->

	<!-- Start Page Content -->
	<div class="row">
		<div class="col-lg-12">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="intro">
					<?php the_content(); ?>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
	<!-- EO Page Content -->

<?php get_footer(); ?>
