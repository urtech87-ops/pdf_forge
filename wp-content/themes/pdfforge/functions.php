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
 * Seed starter Tool CPT posts on theme activation (idempotent)
 * --------------------------------------------------------------------- */
function pdfforge_seed_tools() {
	if ( get_option( 'pdfforge_tools_seeded' ) ) return;

	$starter_tools = [
		[
			'title'       => 'Merge PDF',
			'slug'        => 'merge-pdf',
			'description' => 'Combine multiple PDFs into one file.',
			'emoji'       => '🔀',
			'category'    => 'Edit PDF',
		],
		[
			'title'       => 'Split PDF',
			'slug'        => 'split-pdf',
			'description' => 'Extract pages or split a PDF into many.',
			'emoji'       => '✂️',
			'category'    => 'Edit PDF',
		],
		[
			'title'       => 'Compress PDF',
			'slug'        => 'compress-pdf',
			'description' => 'Reduce file size without losing quality.',
			'emoji'       => '🗜️',
			'category'    => 'Edit PDF',
		],
	];

	foreach ( $starter_tools as $tool ) {
		// Skip if a post with this slug already exists.
		$existing = get_page_by_path( $tool['slug'], OBJECT, 'pdfforge_tool' );
		if ( $existing ) continue;

		$post_id = wp_insert_post( [
			'post_type'    => 'pdfforge_tool',
			'post_status'  => 'publish',
			'post_title'   => $tool['title'],
			'post_name'    => $tool['slug'],
			'post_content' => sprintf(
				'<h1>%s</h1><p>Coming soon — this tool is being built.</p>',
				esc_html( $tool['title'] )
			),
		] );

		if ( is_wp_error( $post_id ) || ! $post_id ) continue;

		update_post_meta( $post_id, '_tool_description', $tool['description'] );
		update_post_meta( $post_id, '_tool_icon_emoji',  $tool['emoji'] );
		update_post_meta( $post_id, '_tool_icon_image_id', 0 );
		update_post_meta( $post_id, '_tool_color_override', '' );

		$term = get_term_by( 'name', $tool['category'], 'pdfforge_category' );
		if ( $term ) {
			wp_set_post_terms( $post_id, [ $term->term_id ], 'pdfforge_category' );
		}
	}

	update_option( 'pdfforge_tools_seeded', true );
}
add_action( 'after_switch_theme', 'pdfforge_seed_tools' );

/* -----------------------------------------------------------------------
 * Color helper: color_override > first category color > #7c5cff
 * Used everywhere a tool colour is rendered on the front end.
 * --------------------------------------------------------------------- */
function pdfforge_get_tool_color( $tool_id ) {
	$override = get_post_meta( $tool_id, '_tool_color_override', true );
	if ( $override ) {
		return $override;
	}
	$terms = get_the_terms( $tool_id, 'pdfforge_category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$cat_color = get_term_meta( $terms[0]->term_id, 'color', true );
		if ( $cat_color ) {
			return $cat_color;
		}
	}
	return '#7c5cff';
}

/* -----------------------------------------------------------------------
 * Enqueue wp.media on Tool edit screen (needed for icon image picker)
 * --------------------------------------------------------------------- */
function pdfforge_admin_enqueue_media() {
	$screen = get_current_screen();
	if ( $screen && 'pdfforge_tool' === $screen->post_type ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'pdfforge_admin_enqueue_media' );

/* -----------------------------------------------------------------------
 * Tool Details meta box
 * Fields: description, icon_emoji, icon_image (media), color_override
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
	$description    = get_post_meta( $post->ID, '_tool_description', true );
	$icon_emoji     = get_post_meta( $post->ID, '_tool_icon_emoji', true );
	$icon_image_id  = absint( get_post_meta( $post->ID, '_tool_icon_image_id', true ) );
	$color_override = get_post_meta( $post->ID, '_tool_color_override', true );
	$preview_url    = $icon_image_id ? wp_get_attachment_image_url( $icon_image_id, 'thumbnail' ) : '';
	?>
	<table class="form-table" style="width:100%">
		<tr>
			<th style="width:200px"><label for="tool_description"><?php esc_html_e( 'Description', 'pdfforge' ); ?></label></th>
			<td>
				<textarea name="tool_description" id="tool_description" rows="2" style="width:100%"><?php echo esc_textarea( $description ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One-line description shown on the homepage grid.', 'pdfforge' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="tool_icon_emoji"><?php esc_html_e( 'Icon emoji', 'pdfforge' ); ?></label></th>
			<td>
				<input type="text" name="tool_icon_emoji" id="tool_icon_emoji" value="<?php echo esc_attr( $icon_emoji ); ?>" style="width:80px">
				<p class="description"><?php esc_html_e( 'Single emoji used when no icon image is set.', 'pdfforge' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Icon image', 'pdfforge' ); ?></th>
			<td>
				<input type="hidden" name="tool_icon_image_id" id="pdfforge_icon_image_id" value="<?php echo esc_attr( $icon_image_id ?: '' ); ?>">
				<div id="pdfforge_icon_image_preview" style="margin-bottom:8px">
					<?php if ( $preview_url ) : ?>
						<img src="<?php echo esc_url( $preview_url ); ?>" style="max-width:100px;max-height:100px" alt="">
					<?php endif; ?>
				</div>
				<button type="button" class="button" id="pdfforge_icon_image_btn"><?php esc_html_e( 'Select image', 'pdfforge' ); ?></button>
				<button type="button" class="button" id="pdfforge_icon_image_remove" style="<?php echo $icon_image_id ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'pdfforge' ); ?></button>
				<p class="description"><?php esc_html_e( 'Image or SVG. Overrides the emoji field above.', 'pdfforge' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="tool_color_override"><?php esc_html_e( 'Color override', 'pdfforge' ); ?></label></th>
			<td>
				<input type="color" name="tool_color_override" id="tool_color_override" value="<?php echo esc_attr( $color_override ?: '#7c5cff' ); ?>">
				<label style="margin-left:8px">
					<input type="checkbox" name="tool_color_clear" id="tool_color_clear" value="1" <?php checked( ! $color_override ); ?>>
					<?php esc_html_e( 'Inherit from category (ignore colour picker)', 'pdfforge' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Leave "Inherit" checked to use the category colour. Uncheck to set a custom colour.', 'pdfforge' ); ?></p>
			</td>
		</tr>
	</table>
	<script>
	(function($) {
		var mediaUploader;
		$('#pdfforge_icon_image_btn').on('click', function(e) {
			e.preventDefault();
			if (mediaUploader) { mediaUploader.open(); return; }
			mediaUploader = wp.media({
				title: '<?php echo esc_js( __( 'Select Icon Image', 'pdfforge' ) ); ?>',
				button: { text: '<?php echo esc_js( __( 'Use this image', 'pdfforge' ) ); ?>' },
				multiple: false,
				library: { type: ['image'] }
			});
			mediaUploader.on('select', function() {
				var attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#pdfforge_icon_image_id').val(attachment.id);
				$('#pdfforge_icon_image_preview').html('<img src="' + attachment.url + '" style="max-width:100px;max-height:100px" alt="">');
				$('#pdfforge_icon_image_remove').show();
			});
			mediaUploader.open();
		});
		$('#pdfforge_icon_image_remove').on('click', function(e) {
			e.preventDefault();
			$('#pdfforge_icon_image_id').val('');
			$('#pdfforge_icon_image_preview').html('');
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}

function pdfforge_tool_meta_save( $post_id ) {
	if ( ! isset( $_POST['pdfforge_tool_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['pdfforge_tool_nonce'], 'pdfforge_tool_meta_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['tool_description'] ) ) {
		update_post_meta( $post_id, '_tool_description', sanitize_textarea_field( wp_unslash( $_POST['tool_description'] ) ) );
	}
	if ( isset( $_POST['tool_icon_emoji'] ) ) {
		update_post_meta( $post_id, '_tool_icon_emoji', sanitize_text_field( wp_unslash( $_POST['tool_icon_emoji'] ) ) );
	}
	$icon_image_id = isset( $_POST['tool_icon_image_id'] ) ? absint( $_POST['tool_icon_image_id'] ) : 0;
	update_post_meta( $post_id, '_tool_icon_image_id', $icon_image_id );

	// Empty string = inherit from category; only store a colour if the checkbox is unchecked
	if ( ! empty( $_POST['tool_color_clear'] ) ) {
		update_post_meta( $post_id, '_tool_color_override', '' );
	} elseif ( isset( $_POST['tool_color_override'] ) ) {
		update_post_meta( $post_id, '_tool_color_override', sanitize_hex_color( $_POST['tool_color_override'] ) );
	}
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
