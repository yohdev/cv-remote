<?php
// Create Block Attributes
acf_register_block( array(
   'name'            => 'guest-notice',
   'title'           => __( 'Guest Notice' ),
   'description'     => __( 'Content visible only to logged-out users. Use for WooCommerce My Account instructions.' ),
   'render_template' => plugin_dir_path( __FILE__ ) . 'template.php',
   'mode'            => 'preview',
   'category'        => 'acf-core-blocks',
   'multiple'        => true,
   'icon'            => 'visibility',
   'keywords'        => array( 'guest', 'notice', 'logged out', 'woocommerce', 'account' ),
   'supports'        => [
      'mode'            => true,
      'align'           => true,
      'anchor'          => true,
      'customClassName' => true,
      'jsx'             => true,
   ]
) );

// Enqueue styles
wp_enqueue_style( 'guest_notice_style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.0', 'all' );
