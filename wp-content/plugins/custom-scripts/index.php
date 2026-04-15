<?php
/**
 * Plugin Name: Site Scripts
 * Description: Vanilla JS plugin
 * Version: 1.0
 * Author: Scott Saunders
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class WP_FadeIn_Plugin {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		// Enqueue your JavaScript
		wp_enqueue_script( 'scripts-scripts', plugin_dir_url( __FILE__ ) . 'script.js', '1.0', true );

		// Enqueue your CSS
		wp_enqueue_style( 'scripts-styles', plugin_dir_url( __FILE__ ) . 'style.css', array(), wp_get_theme()->get( 'Version' ) );
	}
}

new WP_FadeIn_Plugin();