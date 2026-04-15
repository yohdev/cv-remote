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
	$className = '';
	if( !empty($block['className']) ) {
		$className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
		$className .= ' align' . $block['align'];
	}

?>

<div class="timeline-block <?php echo esc_attr($className); ?>" id="<?php echo $id;?> ">
	<div class="timeline-dot fade-in-element"></div>
	<div class="timeline-line fade-in-element"></div>
	
		<?php echo '<InnerBlocks />';?>
</div>