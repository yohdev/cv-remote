<?php
// Register Constitution Block
acf_register_block(array(
	'name'              => 'constitution_block',
	'title'             => __('U.S. Constitution'),
	'description'       => __('Display the complete U.S. Constitution with navigation'),
	'render_template'   => plugin_dir_path( __FILE__ ) . 'template.php',
	'category'          => 'acf-core-blocks',
	'mode'              => 'preview',
	'icon'              => 'book-alt',
	'enqueue_style'     => plugin_dir_url( __FILE__ ). 'style.css',
	'enqueue_script'    => plugin_dir_url( __FILE__ ). 'script.js',
	'keywords'          => array( 'constitution', 'law', 'document', 'history' ),
	'supports'          => [
		'align'             => true,
		'anchor'            => true,
		'customClassName'   => true,
		'jsx'               => true,
	]
));

// Load ACF fields
$acf_json_data = plugin_dir_path( __FILE__ ) . 'acf.json';
$custom_fields = $acf_json_data ? json_decode( file_get_contents( $acf_json_data ), true ) : array();
foreach ( $custom_fields as $custom_field ) {
	acf_add_local_field_group( $custom_field );
}