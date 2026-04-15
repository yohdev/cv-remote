<?php
/**
 * Gallery Block
 *
 * This is the template for a Gallery Block.
 */

	
	
// Create id attribute allowing for custom "anchor" value.
$id = $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'gallery';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

$gallery_name = get_field('gallery_name');

if ($gallery_name) {
    $gallery_name = preg_replace('/\s*/', '', $gallery_name);
    // convert the string to all lowercase
    $gallery_name = strtolower($gallery_name);
} else {
    $gallery_name = 'gallery';
}

	?>
	
	
<div class="js-gallery-block" name="<?php echo $id; ?>">
	<?php 
$images = get_field('gallery');
if( $images ): ?>
   <div data-aos="fade-in" data-aos-delay="50" data-aos-duration="1000" data-aos-easing="ease-in-out" id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($className); ?>">
        <?php foreach( $images as $image ): ?>
			<a class="gl-image" data-fancybox="<?php echo $gallery_name; ?>" href="<?php echo esc_url($image['url']); ?>">
				<img data-aos="fade-in" data-aos-delay="50" data-aos-duration="1000" data-aos-easing="ease-in-out" src="<?php echo esc_url($image['sizes']['medium_large']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
           <?php echo esc_html($image['caption']); ?>
			</a>
        <?php endforeach; ?>
   </div>
<?php endif; ?>
</div>