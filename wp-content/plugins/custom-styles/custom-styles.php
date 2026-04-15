<?php
/**
 * Plugin Name: Custom Styles
 * Description: Enqueue a global stylesheet for your entire site. Add your CSS in <plugin>/assets/css/custom-styles.css.
 * Version: 1.0.0
 * Author: Wildfire Ideas
 * License: GPLv2 or later
 * Text Domain: custom-styles
 *
 * @package Custom_Styles
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

define( 'CUSTOM_STYLES_VERSION', '1.0.0' );
define( 'CUSTOM_STYLES_PLUGIN_FILE', __FILE__ );
define( 'CUSTOM_STYLES_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CUSTOM_STYLES_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );



/**
 * Enqueue the global stylesheet on the front end.
 */
function custom_styles_enqueue_styles() {
	$handle   = 'custom-styles';
	$css_path = 'custom-styles.css';

	// Use filemtime for cache-busting during development.
	$version = CUSTOM_STYLES_VERSION;
	$abs_css = CUSTOM_STYLES_PLUGIN_DIR_PATH . $css_path;
	if ( file_exists( $abs_css ) ) {
		$version = filemtime( $abs_css );
	}

	wp_enqueue_style(
		$handle,
		CUSTOM_STYLES_PLUGIN_DIR_URL . $css_path,
		array(),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'custom_styles_enqueue_styles', 20 );


/**
 * (Optional) Also load in the block editor so you see styles while editing.
 */
function custom_styles_editor_assets() {
	$handle   = 'custom-styles-editor';
	$css_path = 'custom-styles.css';

	$version = CUSTOM_STYLES_VERSION;
	$abs_css = CUSTOM_STYLES_PLUGIN_DIR_PATH . $css_path;
	if ( file_exists( $abs_css ) ) {
		$version = filemtime( $abs_css );
	}

	wp_enqueue_style(
		$handle,
		CUSTOM_STYLES_PLUGIN_DIR_URL . $css_path,
		array(),
		$version
	);
}
add_action( 'enqueue_block_editor_assets', 'custom_styles_editor_assets', 20 );

