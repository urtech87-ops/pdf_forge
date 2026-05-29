	<footer class="site-footer">
		<div class="footer-inner container">

			<!-- Brand column -->
			<div class="footer-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php bloginfo( 'name' ); ?>">
					<span class="logo-tile" aria-hidden="true">
						<svg width="20" height="20" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect width="22" height="22" rx="6" fill="#7c5cff"/>
							<path d="M6 6h6a4 4 0 0 1 0 8H6V6Zm2 2v4h4a2 2 0 0 0 0-4H8Z" fill="#fff"/>
						</svg>
					</span>
					<span class="logo-wordmark">PDF<strong>Forge</strong></span>
				</a>
				<p class="footer-blurb">
					<?php esc_html_e( 'Free PDF tools that work entirely in your browser. No uploads. No sign-up. No limits.', 'pdfforge' ); ?>
				</p>
			</div>

			<!-- Navigate column -->
			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Navigate', 'pdfforge' ); ?></h3>
				<ul class="footer-links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'pdfforge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'All Tools', 'pdfforge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Blog', 'pdfforge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Pricing', 'pdfforge' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Privacy Policy', 'pdfforge' ); ?></a></li>
				</ul>
			</div>

			<!-- Tools column -->
			<div class="footer-col">
				<h3 class="footer-heading"><?php esc_html_e( 'Tools', 'pdfforge' ); ?></h3>
				<ul class="footer-links">
					<?php pdfforge_render_footer_tools(); ?>
				</ul>
			</div>

		</div>

		<!-- Copyright bar -->
		<div class="footer-bar">
			<div class="container">
				<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> PDFForge. <?php esc_html_e( 'All rights reserved.', 'pdfforge' ); ?></p>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
