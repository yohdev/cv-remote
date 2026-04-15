<?php
/**
 * Block Name: Slider Navigation Block
 *
 * This is the template for a Slider Navigation Block with hamburger menu
 */


// create id attribute for specific styling
	$id = $block['id'];
	if( !empty($block['anchor']) ) {
		$id = $block['anchor'];
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$className = 'slider-nav-block';
	if( !empty($block['className']) ) {
		$className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
		$className .= ' align' . $block['align'];
	}

?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo $id;?>">
	<!-- Hamburger Menu Button -->
	<button class="slider-nav-hamburger" aria-label="Open Menu" aria-expanded="false">
		<span class="hamburger-line"></span>
		<span class="hamburger-line"></span>
		<span class="hamburger-line"></span>
	</button>

	<!-- Slider Navigation Panel -->
	<div class="slider-nav-panel" aria-hidden="true">
		<div class="slider-nav-content">
			<?php echo '<InnerBlocks />';?>
		</div>
	</div>

	<!-- Overlay for closing menu when clicking outside -->
	<div class="slider-nav-overlay" aria-hidden="true"></div>
</div>