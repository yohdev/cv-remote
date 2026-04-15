<?php
/**
 * Guest Notice Block
 *
 * Shows content only to logged-out users on the frontend.
 * Always visible in the block editor for editing.
 */

// Create id attribute allowing for custom "anchor" value.
$id = $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'guest-notice';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}

// In the editor: always show the block for editing.
if( is_admin() || wp_is_json_request() ) : ?>
    <div class="<?php echo esc_attr( $className ); ?> guest-notice--editor" id="<?php echo esc_attr( $id ); ?>">
        <div class="guest-notice__label">Guest Only — Hidden when logged in</div>
        <div class="guest-notice__content"><?php echo '<InnerBlocks />';?></div>
    </div>
<?php
// On the frontend: only show if the user is NOT logged in.
elseif( ! is_user_logged_in() ) : ?>
    <div class="<?php echo esc_attr( $className ); ?>" id="<?php echo esc_attr( $id ); ?>">
        <div class="guest-notice__content"><?php echo '<InnerBlocks />';?></div>
    </div>
<?php endif; ?>
