<?php
// Register Block
acf_register_block(array(
    'name'            => 'copy-permalink',
    'title'           => __('Copy Permalink'),
    'description'     => __('Displays a link icon button that copies the post permalink to the clipboard.'),
    'render_template' => plugin_dir_path(__FILE__) . 'template.php',
    'category'        => 'acf-core-blocks',
    'mode'            => 'preview',
    'icon'            => 'admin-links',
    'enqueue_style'   => plugin_dir_url(__FILE__) . 'style.css',
    'enqueue_script'  => plugin_dir_url(__FILE__) . 'script.js',
    'keywords'        => array('copy', 'permalink', 'link', 'share', 'clipboard'),
    'supports'        => [
        'align'           => false,
        'anchor'          => true,
        'customClassName' => true,
    ],
));

// Load ACF fields from local acf.json
$acf_json_data = plugin_dir_path(__FILE__) . 'acf.json';
$custom_fields = $acf_json_data ? json_decode(file_get_contents($acf_json_data), true) : array();
foreach ($custom_fields as $custom_field) {
    acf_add_local_field_group($custom_field);
}
