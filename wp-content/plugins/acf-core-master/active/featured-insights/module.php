<?php
acf_register_block(array(
	'name'            => 'featured-insights',
	'title'           => __('Featured Insights'),
	'description'     => __('Displays three hand-picked insight posts.'),
	'render_template' => plugin_dir_path(__FILE__) . 'template.php',
	'category'        => 'acf-core-blocks',
	'mode'            => 'preview',
	'icon'            => 'media-document',
	'enqueue_style'   => plugin_dir_url(__FILE__) . 'style.css',
	'keywords'        => array('featured', 'insights', 'posts'),
	'supports'        => [
		'align'           => false,
		'anchor'          => true,
		'customClassName' => true,
	],
));

$acf_json_data = plugin_dir_path(__FILE__) . 'acf.json';
$custom_fields = $acf_json_data ? json_decode(file_get_contents($acf_json_data), true) : array();
foreach ($custom_fields as $custom_field) {
	acf_add_local_field_group($custom_field);
}