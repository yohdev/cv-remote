<?php
/**
 * Modal Video Block
 *
 * This is the template for a modal video button.
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
	
	$active_state = get_field('active');
	?>
<div class="acc-head <?php echo $className; ?> <?php if( $active_state ): ?>active <?php endif; ?>" id="<?php echo $id; ?>">
	<span class="acc-title"><?php the_field( 'accordion_header' );?></span>
	
	<svg class="toggle-off">
	<svg viewBox="0 -960 960 960"><path d="M450-200v-250H200v-60h250v-250h60v250h250v60H510v250h-60Z"/></svg>	
	</svg>
	
	<svg class="toggle-on">
	<svg viewBox="0 -960 960 960""><path d="M200-450v-60h560v60H200Z"/></svg>
	</svg>
</div>
<div class="acc-body <?php if( $active_state ): ?>active <?php endif; ?>"><?php echo '<InnerBlocks />';?></div>