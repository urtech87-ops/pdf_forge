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

/**
 * Helper: get a single homepage option with a fallback default.
 */
function pdfforge_homepage_option( $key, $default = '' ) {
	$options = get_option( 'pdfforge_homepage', [] );
	$value   = isset( $options[ $key ] ) ? $options[ $key ] : '';
	return ( '' !== $value ) ? $value : $default;
}
