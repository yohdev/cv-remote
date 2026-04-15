<?php
/**
 * Block Name: Insights Carousel
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'insights-carousel';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

$post_count = get_field('post_count') ?: 3;

$query = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => $post_count,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
));

if (!$query->have_posts()) : ?>
    <div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
        <p class="insights-carousel__empty">No posts found.</p>
    </div>
<?php wp_reset_postdata(); return; endif;

$total = $query->post_count;
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" data-total="<?php echo esc_attr($total); ?>">

    <div class="insights-carousel__track-wrapper">
        <div class="insights-carousel__track">

            <?php $index = 0; while ($query->have_posts()) : $query->the_post();
                $categories  = get_the_category();
                $cat_name    = !empty($categories) ? $categories[0]->name : '';
                $author_name = get_the_author();
                $date        = get_the_date('F j, Y');
                $excerpt     = wp_trim_words(get_the_excerpt(), 25, '...');
                $permalink   = get_permalink();
                $thumb_id    = get_post_thumbnail_id();
            ?>
            <div class="insights-carousel__slide" aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>">
                <div class="insights-carousel__card">

                    <a href="<?php echo esc_url($permalink); ?>" class="insights-carousel__image-wrapper">
                        <?php if ($thumb_id) :
                            echo wp_get_attachment_image($thumb_id, 'medium_large', false, array(
                                'class'   => 'insights-carousel__image',
                                'loading' => 'lazy',
                            ));
                        else :
                            $placeholder_url = plugin_dir_url(__FILE__) . 'placeholder.jpg';
                        ?>
                            <img src="<?php echo esc_url($placeholder_url); ?>"
                                 alt=""
                                 class="insights-carousel__image"
                                 loading="lazy" />
                        <?php endif; ?>
                        <?php if ($cat_name) : ?>
                            <span class="insights-carousel__badge"><?php echo esc_html($cat_name); ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="insights-carousel__meta">
                        <?php echo esc_html($author_name); ?> | <?php echo esc_html($date); ?>
                    </div>

                    <h3 class="insights-carousel__title">
                        <?php the_title(); ?>
                    </h3>

                    <?php if ($excerpt) : ?>
                        <p class="insights-carousel__excerpt"><?php echo esc_html($excerpt); ?></p>
                    <?php endif; ?>

                    <a href="<?php echo esc_url($permalink); ?>" class="insights-carousel__link">
                        Read more &rarr;
                    </a>

                </div>
            </div>
            <?php $index++; endwhile; ?>

        </div>
    </div>

    <div class="insights-carousel__controls">
        <div class="insights-carousel__buttons">
            <button class="insights-carousel__btn insights-carousel__btn--prev"
                    aria-label="Previous post">&#8592;</button>
            <button class="insights-carousel__btn insights-carousel__btn--next"
                    aria-label="Next post">&#8594;</button>
        </div>
        <span class="insights-carousel__counter">
            <span class="insights-carousel__current">1</span>/<span class="insights-carousel__total"><?php echo esc_html($total); ?></span>
        </span>
    </div>

</div>

<?php wp_reset_postdata(); ?>
