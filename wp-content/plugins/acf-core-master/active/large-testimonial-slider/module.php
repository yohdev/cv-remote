<?php
// Register Block
acf_register_block(array(
    'name'             => 'large-testimonial-slider',
    'title'            => __('Large Testimonial Slider'),
    'description'      => __('A peek-style infinite carousel that displays testimonials from the Testimonial CPT. Shows 2 cards + peek on desktop, 1 + peek on mobile.'),
    'render_template'  => plugin_dir_path(__FILE__) . 'template.php',
    'category'         => 'acf-core-blocks',
    'mode'             => 'preview',
    'icon'             => 'format-quote',
    'enqueue_style'    => plugin_dir_url(__FILE__) . 'style.css',
    'enqueue_script'   => plugin_dir_url(__FILE__) . 'script.js',
    'keywords'         => array('testimonial', 'carousel', 'slider', 'quotes', 'reviews'),
    'supports'         => [
        'align'           => false,
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
