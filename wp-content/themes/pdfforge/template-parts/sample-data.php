<?php
/**
 * Centralised sample data — replaced section-by-section with live DB queries
 * once each section is wired to the admin.
 */

function pdfforge_get_categories() {
	$terms = get_terms( [
		'taxonomy'   => 'pdfforge_category',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) return [];

	$categories = [];
	foreach ( $terms as $term ) {
		$color              = get_term_meta( $term->term_id, 'color', true ) ?: '#7c5cff';
		$subtitle           = get_term_meta( $term->term_id, 'subtitle', true );
		$featured_tool_name = get_term_meta( $term->term_id, 'featured_tool_name', true );
		$featured_tool_url  = get_term_meta( $term->term_id, 'featured_tool_url', true ) ?: '#';

		$categories[] = [
			'title'        => $term->name,
			'subtitle'     => $subtitle,
			'tool_count'   => (int) $term->count,
			'color'        => $color,
			'featured'     => $featured_tool_name,
			'featured_url' => $featured_tool_url,
		];
	}
	return $categories;
}

// Thin wrapper so front-page.php still works without edits in this step.
function pdfforge_sample_categories() {
	return pdfforge_get_categories();
}

function pdfforge_get_stats() {
	$defaults = [
		[ 'number' => '31+',  'label' => 'Free tools' ],
		[ 'number' => '4M+',  'label' => 'Files processed' ],
		[ 'number' => '180+', 'label' => 'Countries served' ],
		[ 'number' => '100%', 'label' => 'Browser-based' ],
	];
	$stats = [];
	for ( $i = 1; $i <= 4; $i++ ) {
		$idx    = $i - 1;
		$number = pdfforge_homepage_option( "stat_{$i}_number", $defaults[ $idx ]['number'] );
		$label  = pdfforge_homepage_option( "stat_{$i}_label",  $defaults[ $idx ]['label'] );
		$stats[] = [ 'number' => $number, 'label' => $label ];
	}
	return $stats;
}

// Thin wrapper so front-page.php keeps working without edits in this step.
function pdfforge_sample_stats() {
	return pdfforge_get_stats();
}

function pdfforge_sample_tools() {
	return [
		[ 'name' => 'Merge PDF',     'description' => 'Combine multiple PDFs into one file.',           'color' => '#ff6b6b', 'symbol' => '🔀', 'category' => 'Edit PDF',     'url' => '#' ],
		[ 'name' => 'Split PDF',     'description' => 'Extract pages or split a PDF into many.',        'color' => '#4f7eff', 'symbol' => '✂️', 'category' => 'Edit PDF',     'url' => '#' ],
		[ 'name' => 'Compress PDF',  'description' => 'Reduce file size without losing quality.',        'color' => '#26c28e', 'symbol' => '🗜️', 'category' => 'Edit PDF',     'url' => '#' ],
		[ 'name' => 'PDF to Word',   'description' => 'Convert PDF files to editable Word documents.',  'color' => '#4f7eff', 'symbol' => '📝', 'category' => 'Convert PDF',  'url' => '#' ],
		[ 'name' => 'PDF to JPG',    'description' => 'Turn each PDF page into a JPG image.',           'color' => '#f7a440', 'symbol' => '🖼️', 'category' => 'Convert PDF',  'url' => '#' ],
		[ 'name' => 'Word to PDF',   'description' => 'Convert Word documents to PDF in seconds.',      'color' => '#26c28e', 'symbol' => '📄', 'category' => 'Convert PDF',  'url' => '#' ],
		[ 'name' => 'Rotate PDF',    'description' => 'Rotate one or all pages in any direction.',      'color' => '#ff6b6b', 'symbol' => '🔄', 'category' => 'Organise PDF', 'url' => '#' ],
		[ 'name' => 'Protect PDF',   'description' => 'Add a password to keep your PDF secure.',        'color' => '#7c5cff', 'symbol' => '🔒', 'category' => 'Secure PDF',   'url' => '#' ],
		[ 'name' => 'Unlock PDF',    'description' => 'Remove password protection from a PDF.',         'color' => '#f7a440', 'symbol' => '🔓', 'category' => 'Secure PDF',   'url' => '#' ],
	];
}

function pdfforge_sample_nav_tools() {
	$tools = pdfforge_sample_tools();
	$by_cat = [];
	foreach ( $tools as $t ) {
		$by_cat[ $t['category'] ][] = $t;
	}
	return $by_cat;
}

/* -----------------------------------------------------------------------
 * Render helpers (called from header/footer/front-page)
 * --------------------------------------------------------------------- */
function pdfforge_render_nav_dropdown() {
	$by_cat = pdfforge_sample_nav_tools();
	echo '<div class="dropdown-grid">';
	foreach ( $by_cat as $cat => $tools ) {
		echo '<div class="dropdown-section">';
		echo '<p class="dropdown-cat-title">' . esc_html( $cat ) . '</p>';
		echo '<ul>';
		foreach ( $tools as $t ) {
			printf(
				'<li><a href="%s"><span class="dd-icon" style="background:%s">%s</span>%s</a></li>',
				esc_url( $t['url'] ),
				esc_attr( $t['color'] ),
				esc_html( $t['symbol'] ),
				esc_html( $t['name'] )
			);
		}
		echo '</ul></div>';
	}
	echo '</div>';
}

function pdfforge_render_mobile_nav() {
	$by_cat = pdfforge_sample_nav_tools();
	foreach ( $by_cat as $cat => $tools ) {
		echo '<p class="mobile-nav-cat">' . esc_html( $cat ) . '</p>';
		echo '<ul class="mobile-nav-list">';
		foreach ( $tools as $t ) {
			printf(
				'<li><a href="%s">%s</a></li>',
				esc_url( $t['url'] ),
				esc_html( $t['name'] )
			);
		}
		echo '</ul>';
	}
}

function pdfforge_render_footer_tools() {
	$tools = pdfforge_sample_tools();
	$shown = array_slice( $tools, 0, 6 );
	foreach ( $shown as $t ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $t['url'] ), esc_html( $t['name'] ) );
	}
}
