<?php
// Register Block
acf_register_block(array(
    'name'            => 'insights-filter',
    'title'           => __('Insights Filter'),
    'description'     => __('Filter bar with role checkboxes and search for the blog archive.'),
    'render_template' => plugin_dir_path(__FILE__) . 'template.php',
    'category'        => 'acf-core-blocks',
    'mode'            => 'preview',
    'icon'            => 'filter',
    'enqueue_style'   => plugin_dir_url(__FILE__) . 'style.css',
    'enqueue_script'  => plugin_dir_url(__FILE__) . 'script.js',
    'keywords'        => array('insights', 'filter', 'blog', 'search', 'roles'),
    'supports'        => [
        'align'           => true,
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

// ─────────────────────────────────────────────────────────────
// Filter the main blog query based on URL parameters
// ─────────────────────────────────────────────────────────────

add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only apply on blog archive pages
    if (!$query->is_home() && !$query->is_post_type_archive('post') && !$query->is_category() && !$query->is_tag()) {
        return;
    }

    // Filter by role taxonomy
    if (!empty($_GET['role'])) {
        $roles = array_map('sanitize_text_field', (array) $_GET['role']);
        $query->set('tax_query', [
            [
                'taxonomy' => 'role',
                'field'    => 'slug',
                'terms'    => $roles,
            ],
        ]);
    }

    // Filter by search
    if (!empty($_GET['ins_search'])) {
        $query->set('s', sanitize_text_field($_GET['ins_search']));
    }
});
