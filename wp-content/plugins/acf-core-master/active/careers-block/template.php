<?php
/**
 * Block Name: Careers Block
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'careers-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

$section_heading = get_field('section_heading');
$careers         = get_field('careers');
?>

<section class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">

    <?php if (!empty($section_heading)) : ?>
        <h3 class="careers-block__heading has-x-large-font-size"><?php echo esc_html($section_heading); ?></h3>
    <?php endif; ?>

    <?php if (empty($careers)) : ?>
        <p class="careers-block__empty">No open positions at this time.</p>
    <?php else : ?>
        <div class="careers-block__grid">
            <?php foreach ($careers as $post_obj) :
                $title    = get_the_title($post_obj);
                $subtitle = get_the_excerpt($post_obj);
                $link     = get_permalink($post_obj);
                $terms    = wp_get_post_terms($post_obj->ID, 'career_category');
                $job_type = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
            ?>
                <article class="careers-block__card">
                    <?php if ($title) : ?>
                        <h3 class="careers-block__title"><?php echo esc_html($title); ?></h3>
                    <?php endif; ?>

                    <?php if ($job_type) : ?>
                        <div class="careers-block__tags">
                            <span class="careers-block__tag"><?php echo esc_html($job_type); ?></span>
                        </div>
                    <?php endif; ?>

                    <hr class="careers-block__divider" />

                    <?php if ($subtitle) : ?>
                        <p class="careers-block__subtitle"><?php echo esc_html($subtitle); ?></p>
                    <?php endif; ?>

                    <a class="careers-block__link" href="<?php echo esc_url($link); ?>">
                        View Full Description &amp; Apply <span aria-hidden="true">&rarr;</span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>
