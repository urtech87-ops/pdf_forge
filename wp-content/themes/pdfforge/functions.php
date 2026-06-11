<?php
defined( 'ABSPATH' ) || exit;

/* -----------------------------------------------------------------------
 * Theme setup
 * --------------------------------------------------------------------- */
function pdfforge_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo' );

	register_nav_menus( [
		'primary' => __( 'Primary Menu', 'pdfforge' ),
		'footer'  => __( 'Footer Menu', 'pdfforge' ),
	] );
}
add_action( 'after_setup_theme', 'pdfforge_setup' );

/* -----------------------------------------------------------------------
 * Enqueue assets
 * --------------------------------------------------------------------- */
function pdfforge_assets() {
	wp_enqueue_style(
		'pdfforge-main',
		get_template_directory_uri() . '/assets/css/main.css',
		[],
		'1.0.0'
	);
	wp_enqueue_script(
		'pdfforge-main',
		get_template_directory_uri() . '/assets/js/main.js',
		[],
		'1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'pdfforge_assets' );

/* -----------------------------------------------------------------------
 * Enqueue tool-specific assets — only on their individual tool pages
 * --------------------------------------------------------------------- */
function pdfforge_tool_assets() {
	if ( ! is_singular( 'pdfforge_tool' ) ) return;

	$slug = get_post_field( 'post_name', get_the_ID() );
	$dir  = get_template_directory_uri();
	$path = get_template_directory();

	if ( 'merge-pdf' === $slug ) {
		wp_enqueue_script(
			'pdf-lib',
			$dir . '/assets/js/lib/pdf-lib.min.js',
			[],
			'1.17.1',
			true
		);
		wp_enqueue_script(
			'sortablejs',
			$dir . '/assets/js/lib/Sortable.min.js',
			[],
			'1.15.6',
			true
		);
		wp_enqueue_script(
			'pdfforge-merge-pdf',
			$dir . '/assets/js/tools/merge-pdf.js',
			[ 'pdf-lib', 'sortablejs' ],
			'1.0.0',
			true
		);
		wp_enqueue_style(
			'pdfforge-merge-pdf',
			$dir . '/assets/css/tools/merge-pdf.css',
			[],
			'1.0.0'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'pdfforge_tool_assets' );

/* -----------------------------------------------------------------------
 * "Tool" Custom Post Type
 * --------------------------------------------------------------------- */
function pdfforge_register_cpt() {
	$labels = [
		'name'               => __( 'Tools', 'pdfforge' ),
		'singular_name'      => __( 'Tool', 'pdfforge' ),
		'add_new_item'       => __( 'Add New Tool', 'pdfforge' ),
		'edit_item'          => __( 'Edit Tool', 'pdfforge' ),
		'menu_name'          => __( 'PDF Tools', 'pdfforge' ),
	];
	register_post_type( 'pdfforge_tool', [
		'labels'       => $labels,
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-pdf',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
		'rewrite'      => [ 'slug' => 'tools' ],
	] );
}
add_action( 'init', 'pdfforge_register_cpt' );

/* -----------------------------------------------------------------------
 * "Category" Taxonomy
 * --------------------------------------------------------------------- */
function pdfforge_register_taxonomy() {
	$labels = [
		'name'          => __( 'Tool Categories', 'pdfforge' ),
		'singular_name' => __( 'Tool Category', 'pdfforge' ),
		'menu_name'     => __( 'Categories', 'pdfforge' ),
	];
	register_taxonomy( 'pdfforge_category', 'pdfforge_tool', [
		'labels'            => $labels,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => 'tool-category' ],
	] );
}
add_action( 'init', 'pdfforge_register_taxonomy' );

/* -----------------------------------------------------------------------
 * Seed starter categories on theme activation
 * --------------------------------------------------------------------- */
function pdfforge_seed_categories() {
	if ( get_option( 'pdfforge_categories_seeded' ) ) return;

	$categories = [
		[
			'name'     => 'Convert PDF',
			'color'    => '#4f7eff',
			'subtitle' => 'PDF to Word, Excel, JPG & more',
		],
		[
			'name'     => 'Edit PDF',
			'color'    => '#ff6b6b',
			'subtitle' => 'Merge, split, compress, rotate',
		],
		[
			'name'     => 'Organise PDF',
			'color'    => '#26c28e',
			'subtitle' => 'Reorder, delete, and extract pages',
		],
		[
			'name'     => 'Secure PDF',
			'color'    => '#f7a440',
			'subtitle' => 'Password protect and unlock PDFs',
		],
	];

	foreach ( $categories as $cat ) {
		if ( term_exists( $cat['name'], 'pdfforge_category' ) ) continue;
		$result = wp_insert_term( $cat['name'], 'pdfforge_category' );
		if ( is_wp_error( $result ) ) continue;
		$term_id = $result['term_id'];
		update_term_meta( $term_id, 'color', $cat['color'] );
		update_term_meta( $term_id, 'subtitle', $cat['subtitle'] );
		update_term_meta( $term_id, 'featured_tool_name', '' );
		update_term_meta( $term_id, 'featured_tool_url', '' );
	}

	update_option( 'pdfforge_categories_seeded', true );
}
add_action( 'after_switch_theme', 'pdfforge_seed_categories' );

/* -----------------------------------------------------------------------
 * Custom term-meta fields for Category taxonomy
 * --------------------------------------------------------------------- */
function pdfforge_category_add_fields( $taxonomy ) {
	wp_nonce_field( 'pdfforge_cat_meta_save', 'pdfforge_cat_nonce' );
	?>
	<div class="form-field">
		<label for="pdfforge_cat_subtitle"><?php esc_html_e( 'Subtitle', 'pdfforge' ); ?></label>
		<input type="text" name="pdfforge_cat_subtitle" id="pdfforge_cat_subtitle" value="">
	</div>
	<div class="form-field">
		<label for="pdfforge_cat_color"><?php esc_html_e( 'Card colour', 'pdfforge' ); ?></label>
		<input type="color" name="pdfforge_cat_color" id="pdfforge_cat_color" value="#7c5cff">
	</div>
	<div class="form-field">
		<label for="pdfforge_cat_featured_tool_name"><?php esc_html_e( 'Featured tool name', 'pdfforge' ); ?></label>
		<input type="text" name="pdfforge_cat_featured_tool_name" id="pdfforge_cat_featured_tool_name" value="">
	</div>
	<div class="form-field">
		<label for="pdfforge_cat_featured_tool_url"><?php esc_html_e( 'Featured tool URL', 'pdfforge' ); ?></label>
		<input type="url" name="pdfforge_cat_featured_tool_url" id="pdfforge_cat_featured_tool_url" value="">
	</div>
	<?php
}
add_action( 'pdfforge_category_add_form_fields', 'pdfforge_category_add_fields' );

function pdfforge_category_edit_fields( $term, $taxonomy ) {
	$subtitle            = get_term_meta( $term->term_id, 'subtitle', true );
	$color               = get_term_meta( $term->term_id, 'color', true ) ?: '#7c5cff';
	$featured_tool_name  = get_term_meta( $term->term_id, 'featured_tool_name', true );
	$featured_tool_url   = get_term_meta( $term->term_id, 'featured_tool_url', true );
	wp_nonce_field( 'pdfforge_cat_meta_save', 'pdfforge_cat_nonce' );
	?>
	<tr class="form-field">
		<th><label for="pdfforge_cat_subtitle"><?php esc_html_e( 'Subtitle', 'pdfforge' ); ?></label></th>
		<td><input type="text" name="pdfforge_cat_subtitle" id="pdfforge_cat_subtitle" value="<?php echo esc_attr( $subtitle ); ?>"></td>
	</tr>
	<tr class="form-field">
		<th><label for="pdfforge_cat_color"><?php esc_html_e( 'Card colour', 'pdfforge' ); ?></label></th>
		<td><input type="color" name="pdfforge_cat_color" id="pdfforge_cat_color" value="<?php echo esc_attr( $color ); ?>"></td>
	</tr>
	<tr class="form-field">
		<th><label for="pdfforge_cat_featured_tool_name"><?php esc_html_e( 'Featured tool name', 'pdfforge' ); ?></label></th>
		<td><input type="text" name="pdfforge_cat_featured_tool_name" id="pdfforge_cat_featured_tool_name" value="<?php echo esc_attr( $featured_tool_name ); ?>"></td>
	</tr>
	<tr class="form-field">
		<th><label for="pdfforge_cat_featured_tool_url"><?php esc_html_e( 'Featured tool URL', 'pdfforge' ); ?></label></th>
		<td><input type="url" name="pdfforge_cat_featured_tool_url" id="pdfforge_cat_featured_tool_url" value="<?php echo esc_attr( $featured_tool_url ); ?>"></td>
	</tr>
	<?php
}
add_action( 'pdfforge_category_edit_form_fields', 'pdfforge_category_edit_fields', 10, 2 );

function pdfforge_category_save_meta( $term_id ) {
	if ( ! isset( $_POST['pdfforge_cat_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['pdfforge_cat_nonce'], 'pdfforge_cat_meta_save' ) ) return;
	if ( ! current_user_can( 'manage_categories' ) ) return;

	if ( isset( $_POST['pdfforge_cat_subtitle'] ) ) {
		update_term_meta( $term_id, 'subtitle', sanitize_text_field( $_POST['pdfforge_cat_subtitle'] ) );
	}
	if ( isset( $_POST['pdfforge_cat_color'] ) ) {
		update_term_meta( $term_id, 'color', sanitize_hex_color( $_POST['pdfforge_cat_color'] ) );
	}
	if ( isset( $_POST['pdfforge_cat_featured_tool_name'] ) ) {
		update_term_meta( $term_id, 'featured_tool_name', sanitize_text_field( $_POST['pdfforge_cat_featured_tool_name'] ) );
	}
	if ( isset( $_POST['pdfforge_cat_featured_tool_url'] ) ) {
		update_term_meta( $term_id, 'featured_tool_url', esc_url_raw( $_POST['pdfforge_cat_featured_tool_url'] ) );
	}
}
add_action( 'created_pdfforge_category', 'pdfforge_category_save_meta' );
add_action( 'edited_pdfforge_category', 'pdfforge_category_save_meta' );

/* -----------------------------------------------------------------------
 * Custom meta fields for Tool CPT (icon colour, featured flag)
 * --------------------------------------------------------------------- */
function pdfforge_tool_meta_boxes() {
	add_meta_box(
		'pdfforge_tool_meta',
		__( 'Tool Details', 'pdfforge' ),
		'pdfforge_tool_meta_callback',
		'pdfforge_tool',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'pdfforge_tool_meta_boxes' );

function pdfforge_tool_meta_callback( $post ) {
	wp_nonce_field( 'pdfforge_tool_meta_save', 'pdfforge_tool_nonce' );
	$icon_color  = get_post_meta( $post->ID, '_tool_icon_color', true ) ?: '#7c5cff';
	$icon_symbol = get_post_meta( $post->ID, '_tool_icon_symbol', true ) ?: '📄';
	$featured    = get_post_meta( $post->ID, '_tool_featured', true );
	?>
	<p>
		<label><?php esc_html_e( 'Icon colour', 'pdfforge' ); ?><br>
		<input type="color" name="tool_icon_color" value="<?php echo esc_attr( $icon_color ); ?>"></label>
	</p>
	<p>
		<label><?php esc_html_e( 'Icon symbol / emoji', 'pdfforge' ); ?><br>
		<input type="text" name="tool_icon_symbol" value="<?php echo esc_attr( $icon_symbol ); ?>" style="width:80px"></label>
	</p>
	<p>
		<label>
		<input type="checkbox" name="tool_featured" value="1" <?php checked( $featured, '1' ); ?>>
		<?php esc_html_e( 'Show in "Popular Tools"', 'pdfforge' ); ?>
		</label>
	</p>
	<?php
}

function pdfforge_tool_meta_save( $post_id ) {
	if ( ! isset( $_POST['pdfforge_tool_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['pdfforge_tool_nonce'], 'pdfforge_tool_meta_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['tool_icon_color'] ) ) {
		update_post_meta( $post_id, '_tool_icon_color', sanitize_hex_color( $_POST['tool_icon_color'] ) );
	}
	if ( isset( $_POST['tool_icon_symbol'] ) ) {
		update_post_meta( $post_id, '_tool_icon_symbol', sanitize_text_field( $_POST['tool_icon_symbol'] ) );
	}
	update_post_meta( $post_id, '_tool_featured', isset( $_POST['tool_featured'] ) ? '1' : '0' );
}
add_action( 'save_post_pdfforge_tool', 'pdfforge_tool_meta_save' );

/* -----------------------------------------------------------------------
 * Homepage Settings — admin page + Settings API
 * --------------------------------------------------------------------- */
function pdfforge_homepage_settings_menu() {
	add_theme_page(
		__( 'Homepage Settings', 'pdfforge' ),
		__( 'Homepage Settings', 'pdfforge' ),
		'manage_options',
		'pdfforge-homepage-settings',
		'pdfforge_homepage_settings_page'
	);
}
add_action( 'admin_menu', 'pdfforge_homepage_settings_menu' );

function pdfforge_homepage_settings_init() {
	register_setting(
		'pdfforge_homepage',
		'pdfforge_homepage',
		[ 'sanitize_callback' => 'pdfforge_sanitize_homepage_options' ]
	);

	// Hero section
	add_settings_section( 'pdfforge_hero_section', __( 'Hero Text', 'pdfforge' ), '__return_false', 'pdfforge-homepage-settings' );

	$hero_fields = [
		'hero_badge'    => [ __( 'Badge text', 'pdfforge' ),    'text' ],
		'hero_line1'    => [ __( 'Headline line 1', 'pdfforge' ), 'text' ],
		'hero_line2'    => [ __( 'Headline line 2 (purple)', 'pdfforge' ), 'text' ],
		'hero_subtitle' => [ __( 'Subtitle', 'pdfforge' ),      'textarea' ],
	];
	foreach ( $hero_fields as $key => $info ) {
		add_settings_field(
			$key,
			$info[0],
			'pdfforge_homepage_field_cb',
			'pdfforge-homepage-settings',
			'pdfforge_hero_section',
			[ 'key' => $key, 'type' => $info[1] ]
		);
	}

	// Stats section
	add_settings_section( 'pdfforge_stats_section', __( 'Stats', 'pdfforge' ), '__return_false', 'pdfforge-homepage-settings' );

	for ( $i = 1; $i <= 4; $i++ ) {
		add_settings_field(
			"stat_{$i}_number",
			/* translators: %d: stat position number */
			sprintf( __( 'Stat %d number', 'pdfforge' ), $i ),
			'pdfforge_homepage_field_cb',
			'pdfforge-homepage-settings',
			'pdfforge_stats_section',
			[ 'key' => "stat_{$i}_number", 'type' => 'text' ]
		);
		add_settings_field(
			"stat_{$i}_label",
			sprintf( __( 'Stat %d label', 'pdfforge' ), $i ),
			'pdfforge_homepage_field_cb',
			'pdfforge-homepage-settings',
			'pdfforge_stats_section',
			[ 'key' => "stat_{$i}_label", 'type' => 'text' ]
		);
	}
}
add_action( 'admin_init', 'pdfforge_homepage_settings_init' );

function pdfforge_homepage_field_cb( $args ) {
	$options = get_option( 'pdfforge_homepage', [] );
	$key     = $args['key'];
	$value   = isset( $options[ $key ] ) ? $options[ $key ] : '';

	if ( 'textarea' === $args['type'] ) {
		printf(
			'<textarea name="pdfforge_homepage[%s]" rows="3" cols="60" class="large-text">%s</textarea>',
			esc_attr( $key ),
			esc_textarea( $value )
		);
	} else {
		printf(
			'<input type="text" name="pdfforge_homepage[%s]" value="%s" class="regular-text">',
			esc_attr( $key ),
			esc_attr( $value )
		);
	}
}

function pdfforge_sanitize_homepage_options( $input ) {
	$clean = [];
	$text_fields = [
		'hero_badge', 'hero_line1', 'hero_line2',
		'stat_1_number', 'stat_1_label',
		'stat_2_number', 'stat_2_label',
		'stat_3_number', 'stat_3_label',
		'stat_4_number', 'stat_4_label',
	];
	foreach ( $text_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$clean[ $field ] = sanitize_text_field( $input[ $field ] );
		}
	}
	if ( isset( $input['hero_subtitle'] ) ) {
		$clean['hero_subtitle'] = sanitize_textarea_field( $input['hero_subtitle'] );
	}
	return $clean;
}

function pdfforge_homepage_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage Settings', 'pdfforge' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'pdfforge_homepage' );
			do_settings_sections( 'pdfforge-homepage-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/* -----------------------------------------------------------------------
 * Seed starter tools (Tool CPT) on theme activation
 * Version-keyed: bump PDFFORGE_TOOLS_SEED_VERSION to re-seed all posts.
 * --------------------------------------------------------------------- */
define( 'PDFFORGE_TOOLS_SEED_VERSION', '1.1' );

function pdfforge_seed_tools() {
	if ( get_option( 'pdfforge_tools_seeded' ) === PDFFORGE_TOOLS_SEED_VERSION ) return;

	$merge_pdf_content = '
<p>Merge PDF lets you combine two or more PDF documents into a single file in seconds — without installing any software. Everything runs directly in your browser using your device\'s own processing power, so your files are never uploaded to a server and never leave your device.</p>

<p>Because processing happens locally, Merge PDF is completely private. There\'s no account required, no file size cap imposed by a server, and nothing stored in the cloud. Whether you\'re joining a contract with its exhibit, combining scanned pages, or assembling a multi-chapter report, the merged result is ready to download the moment processing is done.</p>

<h2>Frequently Asked Questions</h2>

<h3>Can I merge more than 10 PDFs?</h3>
<p>Yes. There is no hard limit on the number of files. You can add as many PDFs as you like, though very large collections will use more of your device\'s memory. If your browser becomes sluggish, try merging in smaller batches.</p>

<h3>Do my files leave my device?</h3>
<p>No. All processing is done locally in your browser using pdf-lib, a JavaScript library that runs entirely on your device. Your PDFs are never uploaded to PDFForge servers or any third party.</p>

<h3>Is there a file size limit?</h3>
<p>There is no server-side size limit because no server is involved. The practical limit is your browser\'s available memory. We show a warning when a single file exceeds 50 MB or your total selection exceeds 100 MB, as very large files can slow down or freeze the browser tab.</p>

<h3>Does the page order match what I uploaded?</h3>
<p>Yes. Pages appear in the merged PDF in exactly the order shown in the file list. You can drag the handles to reorder files before clicking Merge, and the output will reflect that order precisely.</p>
';

	$tools = [
		[
			'title'    => 'Merge PDF',
			'slug'     => 'merge-pdf',
			'content'  => $merge_pdf_content,
			'color'    => '#ff6b6b',
			'symbol'   => '🔀',
			'category' => 'Edit PDF',
			'featured' => '1',
		],
		[
			'title'    => 'Split PDF',
			'slug'     => 'split-pdf',
			'content'  => '<p>Coming soon — check back shortly.</p>',
			'color'    => '#4f7eff',
			'symbol'   => '✂️',
			'category' => 'Edit PDF',
			'featured' => '1',
		],
		[
			'title'    => 'Compress PDF',
			'slug'     => 'compress-pdf',
			'content'  => '<p>Coming soon — check back shortly.</p>',
			'color'    => '#26c28e',
			'symbol'   => '🗜️',
			'category' => 'Edit PDF',
			'featured' => '1',
		],
	];

	foreach ( $tools as $tool ) {
		$existing = get_page_by_path( $tool['slug'], OBJECT, 'pdfforge_tool' );

		$post_data = [
			'post_title'   => $tool['title'],
			'post_name'    => $tool['slug'],
			'post_content' => $tool['content'],
			'post_status'  => 'publish',
			'post_type'    => 'pdfforge_tool',
		];

		if ( $existing ) {
			$post_data['ID'] = $existing->ID;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $post_id ) || ! $post_id ) continue;

		update_post_meta( $post_id, '_tool_icon_color', $tool['color'] );
		update_post_meta( $post_id, '_tool_icon_symbol', $tool['symbol'] );
		update_post_meta( $post_id, '_tool_featured', $tool['featured'] );

		// Assign to category taxonomy
		$term = get_term_by( 'name', $tool['category'], 'pdfforge_category' );
		if ( $term ) {
			wp_set_post_terms( $post_id, [ $term->term_id ], 'pdfforge_category' );
		}
	}

	update_option( 'pdfforge_tools_seeded', PDFFORGE_TOOLS_SEED_VERSION );
}
add_action( 'after_switch_theme', 'pdfforge_seed_tools' );

/**
 * Helper: get a single homepage option with a fallback default.
 */
function pdfforge_homepage_option( $key, $default = '' ) {
	$options = get_option( 'pdfforge_homepage', [] );
	$value   = isset( $options[ $key ] ) ? $options[ $key ] : '';
	return ( '' !== $value ) ? $value : $default;
}
