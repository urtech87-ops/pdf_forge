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
