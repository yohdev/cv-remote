<?php
/**
 * Plugin Name: Custom Functions
 * Description: A simple container plugin for site-specific code such as custom post types, taxonomies, and tweaks...
 * Version:     1.0
 * Author:      Doc Hazzard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Re-add Customizer link for block themes
 */
add_action('admin_menu', function() {
	add_submenu_page(
		'themes.php',
		__('Customize'),
		__('Customize'),
		'edit_theme_options',
		'customize.php'
	);
}, 999);

// ─────────────────────────────────────────────────────────────
// TRACKING SCRIPTS — GTM & Google Ads (configurable via Settings)
// ─────────────────────────────────────────────────────────────

/**
 * Register settings page.
 */
function cf_tracking_register_settings() {
	add_options_page(
		'Tracking Scripts',
		'Tracking Scripts',
		'manage_options',
		'cf-tracking-scripts',
		'cf_tracking_settings_page'
	);
}
add_action( 'admin_menu', 'cf_tracking_register_settings' );

/**
 * Register settings fields.
 */
add_action( 'admin_init', function() {
	register_setting( 'cf_tracking_settings', 'custom_functions_gtm_id', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] );

	register_setting( 'cf_tracking_settings', 'custom_functions_gads_id', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	] );
} );

/**
 * Render the settings page.
 */
function cf_tracking_settings_page() {
	?>
	<div class="wrap">
		<h1>Tracking Scripts</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'cf_tracking_settings' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="custom_functions_gtm_id">GTM Container ID</label></th>
					<td>
						<input type="text" id="custom_functions_gtm_id" name="custom_functions_gtm_id"
							value="<?php echo esc_attr( get_option( 'custom_functions_gtm_id', '' ) ); ?>"
							class="regular-text" placeholder="GTM-XXXXXXX">
						<p class="description">Google Tag Manager container ID (e.g. GTM-XXXXXXX)</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="custom_functions_gads_id">Google Ads Conversion ID</label></th>
					<td>
						<input type="text" id="custom_functions_gads_id" name="custom_functions_gads_id"
							value="<?php echo esc_attr( get_option( 'custom_functions_gads_id', '' ) ); ?>"
							class="regular-text" placeholder="AW-XXXXXXXXXXX">
						<p class="description">Google Ads conversion ID (e.g. AW-XXXXXXXXXXX)</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Inject GTM script in <head>.
 */
add_action( 'wp_head', function() {
	$gtm_id = get_option( 'custom_functions_gtm_id', '' );
	if ( empty( $gtm_id ) || str_starts_with( $gtm_id, 'GTM-XXX' ) ) return;
	?>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
	<!-- End Google Tag Manager -->
	<?php
}, 1 );

/**
 * Inject GTM noscript iframe right after <body>.
 */
add_action( 'wp_body_open', function() {
	$gtm_id = get_option( 'custom_functions_gtm_id', '' );
	if ( empty( $gtm_id ) || str_starts_with( $gtm_id, 'GTM-XXX' ) ) return;
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}, 1 );

/**
 * Inject Google Ads gtag.js in <head>.
 */
add_action( 'wp_head', function() {
	$gads_id = get_option( 'custom_functions_gads_id', '' );
	if ( empty( $gads_id ) || str_starts_with( $gads_id, 'AW-XXX' ) ) return;
	?>
	<!-- Google Ads (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $gads_id ); ?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '<?php echo esc_js( $gads_id ); ?>');
	</script>
	<!-- End Google Ads -->
	<?php
}, 2 );

// Remove default WordPress block patterns
add_action( 'after_setup_theme', function() {
	remove_theme_support( 'core-block-patterns' );
} );

// ─────────────────────────────────────────────────────────────
// CUSTOM TAXONOMIES
// ─────────────────────────────────────────────────────────────

/**
 * Register "Role" taxonomy for blog post audience filtering.
 */
add_action( 'init', function() {
	register_taxonomy( 'role', 'post', [
		'labels' => [
			'name'              => 'Roles',
			'singular_name'     => 'Role',
			'search_items'      => 'Search Roles',
			'all_items'         => 'All Roles',
			'edit_item'         => 'Edit Role',
			'update_item'       => 'Update Role',
			'add_new_item'      => 'Add New Role',
			'new_item_name'     => 'New Role Name',
			'menu_name'         => 'Roles',
		],
		'public'            => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => 'role' ],
	] );

	// Auto-create the three default roles if they don't exist.
	$defaults = [ 'Physician', 'Administrator', 'Device Clinician' ];
	foreach ( $defaults as $role_name ) {
		if ( ! term_exists( $role_name, 'role' ) ) {
			wp_insert_term( $role_name, 'role' );
		}
	}
} );

// ─────────────────────────────────────────────────────────────
// CUSTOM POST TYPES
// ─────────────────────────────────────────────────────────────

/**
 * Register Testimonials custom post type.
 */
add_action( 'init', function() {
	$labels = [
		'name'                  => 'Testimonials',
		'singular_name'         => 'Testimonial',
		'add_new'               => 'Add New',
		'add_new_item'          => 'Add New Testimonial',
		'edit_item'             => 'Edit Testimonial',
		'new_item'              => 'New Testimonial',
		'view_item'             => 'View Testimonial',
		'view_items'            => 'View Testimonials',
		'search_items'          => 'Search Testimonials',
		'not_found'             => 'No testimonials found.',
		'not_found_in_trash'    => 'No testimonials found in Trash.',
		'all_items'             => 'All Testimonials',
		'archives'              => 'Testimonial Archives',
		'insert_into_item'      => 'Insert into testimonial',
		'uploaded_to_this_item' => 'Uploaded to this testimonial',
		'menu_name'             => 'Testimonials',
	];

	register_post_type( 'testimonial', [
		'labels'       => $labels,
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'menu_icon'    => 'dashicons-format-quote',
		'rewrite'      => [ 'slug' => 'testimonials' ],
	] );

	register_post_meta( 'testimonial', 'testimonial_company', [
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	] );

	register_post_meta( 'testimonial', 'testimonial_position', [
		'show_in_rest'  => true,
		'single'        => true,
		'type'          => 'string',
		'sanitize_callback' => 'sanitize_text_field',
	] );
} );

/**
 * Add Testimonial Details meta box.
 */
add_action( 'add_meta_boxes', function() {
	add_meta_box(
		'testimonial_details',
		'Testimonial Details',
		'cf_testimonial_meta_box_render',
		'testimonial',
		'side',
		'default'
	);
} );

/**
 * Render the Testimonial Details meta box.
 */
function cf_testimonial_meta_box_render( $post ) {
	wp_nonce_field( 'cf_testimonial_meta', 'cf_testimonial_meta_nonce' );
	$company  = get_post_meta( $post->ID, 'testimonial_company', true );
	$position = get_post_meta( $post->ID, 'testimonial_position', true );
	?>
	<p>
		<label for="testimonial_company"><strong>Company Name</strong></label><br>
		<input type="text" id="testimonial_company" name="testimonial_company"
			value="<?php echo esc_attr( $company ); ?>" class="widefat">
	</p>
	<p>
		<label for="testimonial_position"><strong>Position</strong></label><br>
		<input type="text" id="testimonial_position" name="testimonial_position"
			value="<?php echo esc_attr( $position ); ?>" class="widefat">
	</p>
	<?php
}

/**
 * Save Testimonial Details meta box fields.
 */
add_action( 'save_post_testimonial', function( $post_id ) {
	if ( ! isset( $_POST['cf_testimonial_meta_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['cf_testimonial_meta_nonce'], 'cf_testimonial_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['testimonial_company'] ) ) {
		update_post_meta( $post_id, 'testimonial_company', sanitize_text_field( $_POST['testimonial_company'] ) );
	}
	if ( isset( $_POST['testimonial_position'] ) ) {
		update_post_meta( $post_id, 'testimonial_position', sanitize_text_field( $_POST['testimonial_position'] ) );
	}
} );


/**
 * Register "Careers" custom post type
 */
add_action( 'init', function () {
	register_post_type( 'careers', [
		'labels' => [
			'name'               => __( 'Careers' ),
			'singular_name'      => __( 'Career' ),
			'add_new'            => __( 'Add New' ),
			'add_new_item'       => __( 'Add New Career' ),
			'edit_item'          => __( 'Edit Career' ),
			'new_item'           => __( 'New Career' ),
			'view_item'          => __( 'View Career' ),
			'search_items'       => __( 'Search Careers' ),
			'not_found'          => __( 'No careers found' ),
			'not_found_in_trash' => __( 'No careers found in Trash' ),
			'all_items'          => __( 'All Careers' ),
		],
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-businessman',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
		'taxonomies'   => [ 'career_category' ],
		'rewrite'      => [ 'slug' => 'careers' ],
	] );
} );

/**
 * Register "Career Categories" taxonomy for Careers CPT
 */
add_action( 'init', function () {
	register_taxonomy( 'career_category', 'careers', [
		'labels' => [
			'name'              => __( 'Career Categories' ),
			'singular_name'     => __( 'Career Category' ),
			'search_items'      => __( 'Search Career Categories' ),
			'all_items'         => __( 'All Career Categories' ),
			'parent_item'       => __( 'Parent Career Category' ),
			'parent_item_colon' => __( 'Parent Career Category:' ),
			'edit_item'         => __( 'Edit Career Category' ),
			'update_item'       => __( 'Update Career Category' ),
			'add_new_item'      => __( 'Add New Career Category' ),
			'new_item_name'     => __( 'New Career Category Name' ),
			'menu_name'         => __( 'Categories' ),
		],
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => [ 'slug' => 'career-category' ],
	] );
} );




// CVRS Color Palette + Navigation Block Styles
add_filter( 'wp_theme_json_data_theme', function( $theme_json ) {
	$theme_json->update_with( [
		'version'  => 2,
		'settings' => [
			'color' => [
				'palette' => [
					[ 'name' => 'Action Base',  'slug' => 'action-base',  'color' => '#00508d' ],
					[ 'name' => 'Action Light', 'slug' => 'action-light', 'color' => '#e2edf6' ],
					[ 'name' => 'Accent',       'slug' => 'accent',       'color' => '#2987cf' ],
					[ 'name' => 'Heading',      'slug' => 'heading',      'color' => '#141414' ],
					[ 'name' => 'Text',         'slug' => 'text',         'color' => '#525A6B' ],
					[ 'name' => 'Body',         'slug' => 'body',         'color' => '#333335' ],
					[ 'name' => 'Deep Navy',    'slug' => 'deep-navy',    'color' => '#03003c' ],
					[ 'name' => 'White',        'slug' => 'white',        'color' => '#ffffff' ],
					[ 'name' => 'Orange Accent','slug' => 'orange-accent','color' => '#DB775F' ],
					[ 'name' => 'Footer Blue',  'slug' => 'footer-blue',  'color' => '#001A2D' ],
					[ 'name' => 'Color Surface Primary','slug' => 'color-surface-primary','color' => '#F1F6FB' ],
				],
			],
		],
		'styles' => [
			'blocks' => [
				'core/navigation' => [
					'typography' => [
						'fontWeight' => '700',
						'textTransform' => 'uppercase',
					],
					'elements' => [
						'link' => [
							'color' => [ 'text' => '#03003c' ],
							'typography' => [ 'textDecoration' => 'none' ],
							':hover' => [
								'color' => [ 'text' => '#00508d' ],
								'typography' => [ 'textDecoration' => 'none' ],
							],
						],
					],
				],
			],
		],
	] );
	return $theme_json;
} );

// ─────────────────────────────────────────────────────────────
// NAVIGATION — CSS & JS for styling the Nav block
// ─────────────────────────────────────────────────────────────

/**
 * Enqueue navigation CSS & JS on the frontend.
 */
add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'cvrs-navigation',
		plugin_dir_url( __FILE__ ) . 'css/navigation.css',
		[],
		'1.0'
	);

	wp_enqueue_script(
		'cvrs-navigation',
		plugin_dir_url( __FILE__ ) . 'js/navigation.js',
		[],
		'1.0',
		true
	);
} );

// ─────────────────────────────────────────────────────────────
// DEFAULT FEATURED IMAGE FALLBACK
// ─────────────────────────────────────────────────────────────

add_filter( 'post_thumbnail_html', function( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( ! empty( $html ) ) {
		return $html;
	}

	$fallback_url = plugin_dir_url( __DIR__ ) . 'acf-core-master/active/insights-carousel/placeholder.jpg';
	$css_class    = isset( $attr['class'] ) ? $attr['class'] : 'attachment-post-thumbnail wp-post-image';

	return sprintf(
		'<img src="%s" alt="" class="%s" loading="lazy" />',
		esc_url( $fallback_url ),
		esc_attr( $css_class )
	);
}, 10, 5 );