<?php
// Register Block
acf_register_block(array(
    'name'            => 'careers-block',
    'title'           => __('Careers Block'),
    'description'     => __('A grid of open career positions selected via a relationship field.'),
    'render_template' => plugin_dir_path(__FILE__) . 'template.php',
    'category'        => 'acf-core-blocks',
    'mode'            => 'preview',
    'icon'            => 'businessman',
    'enqueue_style'   => plugin_dir_url(__FILE__) . 'style.css',
    'keywords'        => array('careers', 'jobs', 'positions', 'hiring'),
    'supports'        => [
        'align'           => ['wide', 'full'],
        'align_content'   => true,
        'anchor'          => true,
        'customClassName' => true,
        'jsx'             => false,
    ],
));

// Load ACF fields from local acf.json
$acf_json_data = plugin_dir_path(__FILE__) . 'acf.json';
$custom_fields = $acf_json_data ? json_decode(file_get_contents($acf_json_data), true) : array();
foreach ($custom_fields as $custom_field) {
    acf_add_local_field_group($custom_field);
}
