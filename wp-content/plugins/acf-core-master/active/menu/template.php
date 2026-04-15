<?php
/**
 * Menu Block Template
 *
 * This is the template for a responsive menu block with mobile hamburger navigation.
 */

// Create id attribute allowing for custom "anchor" value.
$id = $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = '';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}

// Get the selected menu
$menu_id = get_field('menu_select');

if( $menu_id ) {
    // Get menu object
    $menu_object = wp_get_nav_menu_object($menu_id);
    $menu_name = $menu_object ? $menu_object->name : '';

    ?>
    <nav class="menu-block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" role="navigation" aria-label="<?php echo esc_attr($menu_name); ?>">

        <!-- Hamburger Button for Mobile -->
        <button
            class="menu-toggle"
            aria-expanded="false"
            aria-controls="menu-<?php echo esc_attr($id); ?>"
            aria-label="Toggle navigation menu"
        >
            <span class="screen-reader-text">Menu</span>
            <span class="hamburger-icon" aria-hidden="true">
                <span class="line"></span>
                <span class="line"></span>
                <span class="line"></span>
            </span>
        </button>

        <!-- Menu Container -->
        <div class="menu-container" id="menu-<?php echo esc_attr($id); ?>">
            <?php
            wp_nav_menu( array(
                'menu'           => $menu_id,
                'container'      => false,
                'menu_class'     => 'primary-menu',
                'fallback_cb'    => false,
                'items_wrap'     => '<ul id="%1$s" class="%2$s" role="menubar">%3$s</ul>',
                'walker'         => new Accessible_Menu_Walker()
            ) );
            ?>
        </div>
    </nav>
    <?php
} else {
    echo '<p>Please select a menu in the block settings.</p>';
}

/**
 * Custom Walker for ADA Compliance
 */
class Accessible_Menu_Walker extends Walker_Nav_Menu {

    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\" role=\"menu\">\n";
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $has_children = in_array('menu-item-has-children', $classes);

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $role = $depth === 0 ? ' role="none"' : ' role="none"';

        $output .= $indent . '<li' . $id . $class_names . $role .'>';

        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        $atts['role']   = 'menuitem';

        if( $has_children ) {
            $atts['aria-haspopup'] = 'true';
            $atts['aria-expanded'] = 'false';
        }

        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

        if( $has_children ) {
            $item_output .= '<span class="submenu-indicator" aria-hidden="true">
            <svg id="Layer_1" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 19.7 14.1">
              <!-- Generator: Adobe Illustrator 30.1.0, SVG Export Plug-In . SVG Version: 2.1.1 Build 136)  -->
              <polygon points="0 14.1 9.9 0 19.7 14.1 0 14.1"/>
            </svg></span>';
        }

        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}
