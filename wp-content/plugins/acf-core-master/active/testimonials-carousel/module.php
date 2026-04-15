<?php
// Register Block
acf_register_block(array(
    'name'            => 'testimonials-carousel',
    'title'           => __('Testimonials Carousel'),
    'description'     => __('A carousel of testimonial quotes with author name, company, and photo.'),
    'render_template' => plugin_dir_path(__FILE__) . 'template.php',
    'category'        => 'acf-core-blocks',
    'mode'            => 'preview',
    'icon'            => 'format-quote',
    'enqueue_style'   => plugin_dir_url(__FILE__) . 'style.css',
    'enqueue_script'  => plugin_dir_url(__FILE__) . 'script.js',
    'keywords'        => array('testimonials', 'carousel', 'quotes', 'reviews'),
    'supports'        => [
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
