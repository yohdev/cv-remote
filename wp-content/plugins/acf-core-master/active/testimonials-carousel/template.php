<?php
/**
 * Block Name: Testimonials Carousel
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'testimonials-carousel';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

$testimonials = get_field('testimonials');

if (empty($testimonials)) : ?>
    <div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
        <p class="testimonials-carousel__empty">No testimonials added yet.</p>
    </div>
<?php return; endif;

$total = count($testimonials);
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" data-total="<?php echo esc_attr($total); ?>">

    <div class="testimonials-carousel__controls">
        <div class="testimonials-carousel__buttons">
            <button class="testimonials-carousel__btn testimonials-carousel__btn--prev"
                    aria-label="Previous testimonial">&#8592;</button>
            <button class="testimonials-carousel__btn testimonials-carousel__btn--next"
                    aria-label="Next testimonial">&#8594;</button>
        </div>
        <span class="testimonials-carousel__counter">
            <span class="testimonials-carousel__current">1</span>/<span class="testimonials-carousel__total"><?php echo esc_html($total); ?></span>
        </span>
    </div>

    <div class="testimonials-carousel__track-wrapper">
        <div class="testimonials-carousel__track">

            <?php foreach ($testimonials as $index => $post_obj) :
                $author_name = get_the_title($post_obj);
                $content     = apply_filters('the_content', $post_obj->post_content);
                $company     = get_post_meta($post_obj->ID, 'testimonial_company', true);
                $position    = get_post_meta($post_obj->ID, 'testimonial_position', true);
                $byline_parts = array_filter([$company, $position]);
                $byline       = implode(' / ', $byline_parts);
                $thumb_url   = get_the_post_thumbnail_url($post_obj->ID, 'thumbnail');
                $thumb_alt   = $author_name;
            ?>
            <div class="testimonials-carousel__slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                 aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>">
                <div class="testimonials-carousel__card">

                    <span class="testimonials-carousel__quote-icon" aria-hidden="true">&#8220;</span>

                    <?php if ($content) : ?>
                        <div class="testimonials-carousel__quote"><?php echo wp_kses_post($content); ?></div>
                    <?php endif; ?>

                    <div class="testimonials-carousel__author">
                        <?php if ($thumb_url) : ?>
                            <div class="testimonials-carousel__avatar">
                                <img src="<?php echo esc_url($thumb_url); ?>"
                                     alt="<?php echo esc_attr($thumb_alt); ?>"
                                     loading="lazy" />
                            </div>
                        <?php endif; ?>
                        <div class="testimonials-carousel__author-info">
                            <?php if ($author_name) : ?>
                                <strong class="testimonials-carousel__name"><?php echo esc_html($author_name); ?></strong>
                            <?php endif; ?>
                            <?php if ($byline) : ?>
                                <span class="testimonials-carousel__company"><?php echo esc_html($byline); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>
