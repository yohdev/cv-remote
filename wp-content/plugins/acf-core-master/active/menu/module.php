<?php
// Create Block Attributes
acf_register_block( array(
   'name'            => 'menu',
   'title'           => __( 'Menu' ),
   'description'     => __( 'A responsive menu block with mobile hamburger navigation' ),
   'render_template' => plugin_dir_path( __FILE__ ) . 'template.php',
   'mode'            => 'preview',
   'category'        => 'acf-core-blocks',
   'multiple'        => true,
   'icon'            => 'menu',
   'keywords'        => array( 'menu', 'navigation', 'nav' ),
   'supports'        => [
      'mode'            => true,
      'align'           => true,
      'anchor'          => true,
      'customClassName' => true,
      'jsx'             => true,
   ]
) );


add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'menu_js',
        plugin_dir_url(__FILE__) . 'js/script.js',
        array('jquery'),
        '1.0',
        true
    );
});

// Frontend + Editor - menu CSS
add_action('enqueue_block_assets', function() {
    wp_enqueue_style(
        'menu_style',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );
});

// Read local acf.json
$acf_json_data = ( plugin_dir_path( __FILE__ ) . 'acf.json' );
$custom_fields = $acf_json_data ? json_decode( file_get_contents( $acf_json_data ), true ) : array();
foreach ( $custom_fields as $custom_field ) {
   acf_add_local_field_group( $custom_field );
}

// Populate menu select field with available WordPress menus
add_filter('acf/load_field/name=menu_select', 'populate_menu_select_field');
function populate_menu_select_field( $field ) {
    // Reset choices
    $field['choices'] = array();

    // Get all registered menus
    $menus = wp_get_nav_menus();

    if( $menus ) {
        foreach( $menus as $menu ) {
            $field['choices'][ $menu->term_id ] = $menu->name;
        }
    }

    return $field;
}
