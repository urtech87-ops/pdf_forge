<?php
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/template-parts/sample-data.php';
get_header();
?>

<main id="main" class="site-main">

	<!-- ================================================================
	     HERO
	     ============================================================== -->
	<section class="hero">
		<div class="container hero-inner">

			<span class="hero-pill"><?php echo esc_html( pdfforge_homepage_option( 'hero_badge', __( 'No sign-up needed', 'pdfforge' ) ) ); ?></span>

			<h1 class="hero-title">
				<span class="hero-line-1"><?php echo esc_html( pdfforge_homepage_option( 'hero_line1', __( 'Every PDF tool you need,', 'pdfforge' ) ) ); ?></span>
				<span class="hero-line-2"><?php echo esc_html( pdfforge_homepage_option( 'hero_line2', __( 'completely free.', 'pdfforge' ) ) ); ?></span>
			</h1>

			<p class="hero-subtitle">
				<?php echo esc_html( pdfforge_homepage_option( 'hero_subtitle', __( 'Merge, split, compress, convert — all in your browser. Your files never leave your device.', 'pdfforge' ) ) ); ?>
			</p>

			<div class="hero-search-wrap">
				<div class="hero-search">
					<svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
						<circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
						<path d="M13 13l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
					</svg>
					<input type="search" placeholder="<?php esc_attr_e( 'Search 31+ free tools…', 'pdfforge' ); ?>" class="hero-search-input" id="heroSearch">
				</div>
				<!-- Mobile only -->
				<button class="btn btn-primary btn-block mobile-only" onclick="document.getElementById('heroSearch').focus()">
					<?php esc_html_e( 'Search tools', 'pdfforge' ); ?>
				</button>
			</div>

		</div>
	</section>

	<!-- ================================================================
	     CATEGORY CARDS
	     ============================================================== -->
	<section class="category-section">
		<div class="container">
			<div class="category-grid">
				<?php foreach ( pdfforge_sample_categories() as $cat ) : ?>
				<div class="category-card" style="--cat-color:<?php echo esc_attr( $cat['color'] ); ?>">
					<span class="cat-badge"><?php echo esc_html( $cat['tool_count'] ); ?> tools</span>
					<h2 class="cat-title"><?php echo esc_html( $cat['title'] ); ?></h2>
					<p class="cat-subtitle"><?php echo esc_html( $cat['subtitle'] ); ?></p>
					<a href="<?php echo esc_url( $cat['featured_url'] ); ?>" class="cat-featured-link">
						<?php echo esc_html( $cat['featured'] ); ?> &rarr;
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ================================================================
	     STATS
	     ============================================================== -->
	<section class="stats-section">
		<div class="container">
			<div class="stats-grid">
				<?php foreach ( pdfforge_sample_stats() as $stat ) : ?>
				<div class="stat-item">
					<span class="stat-number"><?php echo esc_html( $stat['number'] ); ?></span>
					<span class="stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ================================================================
	     POPULAR TOOLS
	     ============================================================== -->
	<section class="tools-section">
		<div class="container">
			<div class="tools-header">
				<h2 class="tools-heading"><?php esc_html_e( 'Popular PDF Tools', 'pdfforge' ); ?></h2>
			</div>

			<!-- Category filter tabs -->
			<?php
			$categories = array_unique( array_column( pdfforge_sample_tools(), 'category' ) );
			?>
			<div class="filter-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter by category', 'pdfforge' ); ?>">
				<button class="filter-tab active" role="tab" aria-selected="true" data-filter="all">
					<?php esc_html_e( 'All', 'pdfforge' ); ?>
				</button>
				<?php foreach ( $categories as $cat ) : ?>
				<button class="filter-tab" role="tab" aria-selected="false" data-filter="<?php echo esc_attr( $cat ); ?>">
					<?php echo esc_html( $cat ); ?>
				</button>
				<?php endforeach; ?>
			</div>

			<!-- Tool cards grid -->
			<div class="tools-grid" id="toolsGrid">
				<?php foreach ( pdfforge_sample_tools() as $tool ) : ?>
				<a href="<?php echo esc_url( $tool['url'] ); ?>"
				   class="tool-card"
				   data-category="<?php echo esc_attr( $tool['category'] ); ?>">
					<span class="tool-icon" style="background:<?php echo esc_attr( $tool['color'] ); ?>" aria-hidden="true">
						<?php echo esc_html( $tool['symbol'] ); ?>
					</span>
					<div class="tool-info">
						<span class="tool-name"><?php echo esc_html( $tool['name'] ); ?></span>
						<span class="tool-desc"><?php echo esc_html( $tool['description'] ); ?></span>
					</div>
				</a>
				<?php endforeach; ?>
			</div>

			<div class="tools-footer">
				<a href="#" class="btn btn-outline"><?php esc_html_e( 'All tools', 'pdfforge' ); ?></a>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
