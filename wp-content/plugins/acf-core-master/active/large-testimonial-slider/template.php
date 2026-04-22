<?php
/**
 * Block Name: Large Testimonial Slider
 */

// Block ID (supports custom anchor)
$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Block classes
$className = 'large-testimonial-slider';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Get testimonials from the Relationship field
$testimonials = get_field('testimonials');

if (empty($testimonials)) : ?>
    <div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
        <p class="large-testimonial-slider__empty">Select testimonials to display.</p>
    </div>
<?php return; endif;

$total = count($testimonials);
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" data-total="<?php echo esc_attr($total); ?>">

    <div class="large-testimonial-slider__controls">
        <div class="large-testimonial-slider__buttons">
            <button class="large-testimonial-slider__btn large-testimonial-slider__btn--prev"
                    aria-label="Previous testimonial">&#8592;</button>
            <button class="large-testimonial-slider__btn large-testimonial-slider__btn--next"
                    aria-label="Next testimonial">&#8594;</button>
        </div>
        <span class="large-testimonial-slider__counter">
            <span class="large-testimonial-slider__current">1</span>/<span class="large-testimonial-slider__total"><?php echo esc_html($total); ?></span>
        </span>
    </div>

    <div class="large-testimonial-slider__track">

        <?php foreach ($testimonials as $index => $post_obj) :
            if ( ! is_object( $post_obj ) ) { continue; }
            $author_name  = get_the_title( $post_obj );
            $content      = apply_filters( 'the_content', $post_obj->post_content );
            $company      = get_post_meta( $post_obj->ID, 'testimonial_company', true );
            $job_position = get_post_meta( $post_obj->ID, 'testimonial_position', true );
            $byline_parts = array_filter( [ $company, $job_position ] );
            $byline       = implode( ', ', $byline_parts );
            $thumb        = get_the_post_thumbnail( $post_obj->ID, 'thumbnail', [
                'class'   => 'large-testimonial-slider__avatar-img',
                'loading' => 'lazy',
            ] );
        ?>
        <div class="large-testimonial-slider__card">

            <span class="large-testimonial-slider__quote-icon" aria-hidden="true">&#8220;</span>

            <?php if ($content) : ?>
                <div class="large-testimonial-slider__quote"><?php echo wp_kses_post($content); ?></div>
            <?php endif; ?>

            <div class="large-testimonial-slider__author">
                <?php if ($thumb) : ?>
                    <div class="large-testimonial-slider__avatar">
                        <?php echo $thumb; ?>
                    </div>
                <?php else : ?>
                    <div class="large-testimonial-slider__avatar large-testimonial-slider__avatar--placeholder">
                        <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="56" height="56" rx="28" fill="#F1F6FB"/>
                            <path d="M28 28C30.76 28 33 25.76 33 23C33 20.24 30.76 18 28 18C25.24 18 23 20.24 23 23C23 25.76 25.24 28 28 28ZM28 30.5C24.66 30.5 18 32.18 18 35.5V38H38V35.5C38 32.18 31.34 30.5 28 30.5Z" fill="#A0AEC0"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <div class="large-testimonial-slider__author-info">
                    <?php if ($author_name) : ?>
                        <strong class="large-testimonial-slider__name"><?php echo esc_html($author_name); ?></strong>
                    <?php endif; ?>
                    <?php if ($byline) : ?>
                        <span class="large-testimonial-slider__byline"><?php echo esc_html($byline); ?></span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

</div>
