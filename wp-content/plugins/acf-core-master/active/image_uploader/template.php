<?php
/**
 * Block Name: Image Uploader
 *
 * This is the template for an image uploader that is outside of a figure container.
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

<?php 
$image = get_field('image');
$link = get_field('link');

if( !empty( $image ) ): 
	// If there's a link, wrap the image in an anchor tag
	if( !empty( $link ) ): 
		$link_url = $link['url'];
		$link_title = $link['title'];
		$link_target = $link['target'] ? $link['target'] : '_self';
		?>
		<a href="<?php echo esc_url( $link_url ); ?>" 
		   target="<?php echo esc_attr( $link_target ); ?>"
		   <?php if( $link_title ): ?>title="<?php echo esc_attr( $link_title ); ?>"<?php endif; ?>>
			<img class="<?php echo esc_attr($className); ?>" id="<?php echo $id;?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
		</a>
	<?php else: ?>
		<img class="<?php echo esc_attr($className); ?>" id="<?php echo $id;?>" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
	<?php endif; 
endif; ?>
