<?php
/**
 * Block Name: Navigation Dots
 *
 * Fixed vertical dot navigation for page anchors
 */

// create id attribute for specific styling
$id = $block['id'];
if( !empty($block['anchor']) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'navigation-dots';
if( !empty($block['className']) ) {
	$className .= ' ' . $block['className'];
}

// Get the position field
$position = get_field('position');
if( $position ) {
	$className .= ' navigation-dots--' . $position;
}

// Get the navigation dots repeater field
$navigation_dots = get_field('navigation_dots');

?>

<?php if( $navigation_dots ): ?>
<nav class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
	<ul class="navigation-dots__list">
		<?php foreach( $navigation_dots as $dot ): ?>
			<li class="navigation-dots__item">
				<a href="#<?php echo esc_attr($dot['anchor_id']); ?>"
				   class="navigation-dots__link"
				   data-anchor="<?php echo esc_attr($dot['anchor_id']); ?>"
				   aria-label="<?php echo esc_attr($dot['dot_name']); ?>">
					<span class="navigation-dots__dot"></span>
					<span class="navigation-dots__label"><?php echo esc_html($dot['dot_name']); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php endif; ?>