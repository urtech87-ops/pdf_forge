<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="header-inner container">

		<!-- Logo -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php bloginfo( 'name' ); ?>">
			<span class="logo-tile" aria-hidden="true">
				<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect width="22" height="22" rx="6" fill="#7c5cff"/>
					<path d="M6 6h6a4 4 0 0 1 0 8H6V6Zm2 2v4h4a2 2 0 0 0 0-4H8Z" fill="#fff"/>
					<path d="M14 14h2v2h-2v-2Z" fill="#fff" opacity=".6"/>
				</svg>
			</span>
			<span class="logo-wordmark">PDF<strong>Forge</strong></span>
		</a>

		<!-- Desktop nav -->
		<nav class="desktop-nav" aria-label="<?php esc_attr_e( 'Primary', 'pdfforge' ); ?>">
			<ul class="nav-list">
				<li class="nav-item has-dropdown">
					<button class="nav-link dropdown-toggle" aria-expanded="false">
						<?php esc_html_e( 'PDF Tools', 'pdfforge' ); ?>
						<svg class="chevron" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
					<div class="dropdown-panel">
						<?php pdfforge_render_nav_dropdown(); ?>
					</div>
				</li>
				<li class="nav-item"><a class="nav-link" href="#"><?php esc_html_e( 'Blog', 'pdfforge' ); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#"><?php esc_html_e( 'Pricing', 'pdfforge' ); ?></a></li>
			</ul>
		</nav>

		<!-- Right controls -->
		<div class="header-actions">
			<button class="icon-btn theme-toggle" aria-label="<?php esc_attr_e( 'Toggle colour scheme', 'pdfforge' ); ?>">
				<svg class="icon-moon" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M15.5 11.5A7 7 0 1 1 6.5 2.5a5.5 5.5 0 0 0 9 9Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				<svg class="icon-sun" width="18" height="18" viewBox="0 0 18 18" fill="none" style="display:none"><circle cx="9" cy="9" r="3.5" stroke="currentColor" stroke-width="1.5"/><path d="M9 1v2M9 15v2M1 9h2M15 9h2M3.22 3.22l1.41 1.41M13.37 13.37l1.41 1.41M3.22 14.78l1.41-1.41M13.37 4.63l1.41-1.41" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
			</button>

			<div class="header-search">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M11 11l3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
				<input type="search" placeholder="<?php esc_attr_e( 'Search tools…', 'pdfforge' ); ?>" class="header-search-input">
			</div>

			<a href="#" class="btn btn-primary"><?php esc_html_e( 'Sign in', 'pdfforge' ); ?></a>
		</div>

		<!-- Mobile hamburger -->
		<button class="hamburger" aria-label="<?php esc_attr_e( 'Open menu', 'pdfforge' ); ?>" aria-expanded="false">
			<span></span><span></span><span></span>
		</button>
	</div>

	<!-- Mobile drawer -->
	<div class="mobile-drawer" aria-hidden="true">
		<nav>
			<?php pdfforge_render_mobile_nav(); ?>
		</nav>
	</div>
</header>
