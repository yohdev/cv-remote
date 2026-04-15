<?php
/**
 * Block Name: Starter Content Block
 *
 * This is the template for a Starter Content Block
 */


// create id attribute for specific styling
	$id = $block['id'];
	if( !empty($block['anchor']) ) {
		$id = $block['anchor'];
	}
	
	// Create class attribute allowing for custom "className" and "align" values.
	$className = 'timeline-container';
	if( !empty($block['className']) ) {
		$className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
		$className .= ' align' . $block['align'];
		if($block['align'] === 'wide' || $block['align'] === 'full') {
			$className .= ' is-' . $block['align'];
		}
	}
?>

<div id="<?php echo $id; ?>" class="timeline-container <?php echo esc_attr($className); ?>">
	<div class="tl-circle"></div>
	<div class="center-line"></div>
	<div class="time-indicator"></div>
	<?php echo '<InnerBlocks />';?>
</div>
