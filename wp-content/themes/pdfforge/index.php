<?php
// Fallback template — only used if no more specific template matches.
get_header();
?>
<main class="site-main container" style="padding-top:4rem;padding-bottom:4rem;">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; else : ?>
		<p><?php esc_html_e( 'No content found.', 'pdfforge' ); ?></p>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
