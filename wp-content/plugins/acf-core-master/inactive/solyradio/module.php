<?php


// Create SolyRadio Player Block
acf_register_block(array(
	'name'				=> 'solyradio',
	'title'				=> __('SolyRadio Player'),
	'description'		=> __('A Plyr-based audio player for SolyRadio playlists'),
	'render_template'	=> plugin_dir_path( __FILE__ ) . 'template.php',
	'category'			=> 'acf-core-blocks',
	'mode'				=> 'preview',
	'icon'				=> 'playlist-audio',
	'keywords'			=> array( 'audio', 'player', 'playlist', 'solyradio', 'plyr' ),
	'enqueue_assets'	=> function() {
		// Enqueue Plyr CSS
		wp_enqueue_style('plyr', 'https://cdn.plyr.io/3.7.8/plyr.css', [], '3.7.8');
		
		// Enqueue custom SolyRadio styles
		wp_enqueue_style('solyradio-styles', plugin_dir_url( __FILE__ ) . 'style.css', ['plyr'], '1.0');
		
		// Enqueue Plyr JS
		wp_enqueue_script('plyr', 'https://cdn.plyr.io/3.7.8/plyr.polyfilled.js', [], '3.7.8', true);
	},
	'supports'		=> [
		'align'			=> array('wide', 'full'),
		'anchor'		=> true,
		'customClassName'	=> true,
		'jsx' 			=> true,
	]
));

// Read local acf.json
$acf_json_data = ( plugin_dir_path( __FILE__ ) . 'acf.json' );
$custom_fields = $acf_json_data ? json_decode( file_get_contents( $acf_json_data ), true ) : array();
foreach ( $custom_fields as $custom_field ) {
   acf_add_local_field_group( $custom_field );
}