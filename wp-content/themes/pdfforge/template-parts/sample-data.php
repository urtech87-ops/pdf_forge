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

/**
 * Live query of the Tool CPT.
 *
 * Accepted $args keys:
 *   category (string) — pdfforge_category slug to filter by
 *   limit    (int)    — max posts (-1 = all)
 *
 * Returned array keys per tool:
 *   name, description, color, symbol (emoji or placeholder),
 *   icon_url (image URL or empty string), category (term name), url
 */
function pdfforge_get_tools( $args = [] ) {
	$category_slug = isset( $args['category'] ) ? (string) $args['category'] : '';
	$limit         = isset( $args['limit'] )    ? (int)    $args['limit']    : -1;

	$query_args = [
		'post_type'      => 'pdfforge_tool',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	];

	if ( $category_slug ) {
		$query_args['tax_query'] = [ [
			'taxonomy' => 'pdfforge_category',
			'field'    => 'slug',
			'terms'    => $category_slug,
		] ];
	}

	$query = new WP_Query( $query_args );
	$tools = [];

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();

			$icon_image_id = absint( get_post_meta( $post_id, '_tool_icon_image_id', true ) );
			$icon_url      = $icon_image_id ? wp_get_attachment_image_url( $icon_image_id, 'thumbnail' ) : '';

			$terms    = get_the_terms( $post_id, 'pdfforge_category' );
			$cat_name = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

			$color = pdfforge_get_tool_color( $post_id );

			$tools[] = [
				'name'        => get_the_title(),
				'description' => get_post_meta( $post_id, '_tool_description', true ),
				'color'       => $color,
				'symbol'      => get_post_meta( $post_id, '_tool_icon_emoji', true ) ?: '📄',
				'icon_url'    => $icon_url ?: '',
				'category'    => $cat_name,
				'url'         => get_permalink(),
			];
		}
		wp_reset_postdata();
	}

	return $tools;
}

// Thin wrapper — keeps existing callers working unchanged.
function pdfforge_sample_tools() {
	return pdfforge_get_tools();
}

function pdfforge_sample_nav_tools() {
	$tools  = pdfforge_get_tools();
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
