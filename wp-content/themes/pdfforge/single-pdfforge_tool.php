<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$slug          = get_post_field( 'post_name', get_the_ID() );
	$tool_template = get_template_directory() . '/template-parts/tools/' . sanitize_file_name( $slug ) . '.php';
	?>
	<main class="container pdfforge-single-tool" style="padding-top:2rem;padding-bottom:4rem;">

		<h1 class="pdfforge-single-tool__title" style="margin-bottom:1.5rem;">
			<?php the_title(); ?>
		</h1>

		<?php if ( file_exists( $tool_template ) ) : ?>
			<?php include $tool_template; ?>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<div class="pdfforge-single-tool__content entry-content" style="margin-top:3rem;">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	</main>
	<?php

endwhile;

get_footer();
