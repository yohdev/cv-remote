<?php
/**
 * Block Name: Testimonial Card
 */

// Block ID
$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Block classes
$className = 'testimonial-card';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Get the selected testimonial
$testimonials = get_field('testimonial_post');
$post_obj = is_array($testimonials) && !empty($testimonials) ? $testimonials[0] : null;
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <?php if ($post_obj) :
        $name     = get_the_title($post_obj);
        $content  = apply_filters('the_content', $post_obj->post_content);
        $company  = get_post_meta($post_obj->ID, 'testimonial_company', true);
        $position = get_post_meta($post_obj->ID, 'testimonial_position', true);
        $thumb    = get_the_post_thumbnail($post_obj->ID, 'thumbnail', ['class' => 'testimonial-card__avatar']);

        // Build the byline: "Company Name/Position"
        $byline_parts = array_filter([$company, $position]);
        $byline = implode('/', $byline_parts);
    ?>
        <div class="testimonial-card__quote-icon" aria-hidden="true">&ldquo;</div>

        <div class="testimonial-card__content">
            <?php echo wp_kses_post($content); ?>
        </div>

        <div class="testimonial-card__author">
           <?php if ($thumb) : ?>
               <?php echo $thumb; ?>
           <?php else : ?>
               <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
               <rect width="55.7227" height="55.7227" rx="3.71484" fill="#F1F6FB"/>
               <path d="M34.3493 32.0927L32.4047 31.7166C32.3106 33.695 30.6932 35.2673 28.7072 35.2673C27.6729 35.2673 26.7438 34.8385 26.0743 34.1539C25.0061 33.7891 24.1033 32.939 23.573 31.9159C22.9298 32.0062 22.3129 32.0927 22.3129 32.0927C17.7993 33.2211 14.4141 36.249 14.4141 40.9657V49.0188H43.0004V40.9657C43.0004 36.2528 40.7436 33.2211 34.3493 32.0927Z" fill="#DB775F"/>
               <path d="M35.8544 18.5518L29.4601 17.4234C26.451 17.0473 24.5703 16.4267 24.5703 13.805V10.6379C24.5703 8.98294 25.9131 7.64014 27.5681 7.64014H27.7976C32.2473 7.64014 35.8582 11.2473 35.8582 15.7007V18.5481L35.8544 18.5518Z" fill="#DB775F"/>
               <path d="M24.5703 14.0381L20.8089 18.5518H20.0566V15.5427C20.0566 13.0489 22.0765 9.90063 24.5703 9.90063V14.0381Z" fill="#DB775F"/>
               <path d="M34.1609 41.4964C35.7189 41.4964 36.9819 40.2334 36.9819 38.6754C36.9819 37.1174 35.7189 35.8544 34.1609 35.8544C32.6029 35.8544 31.3398 37.1174 31.3398 38.6754C31.3398 40.2334 32.6029 41.4964 34.1609 41.4964Z" fill="#EDF2F8"/>
               <path d="M34.161 39.6155C34.6804 39.6155 35.1014 39.1945 35.1014 38.6752C35.1014 38.1559 34.6804 37.7349 34.161 37.7349C33.6417 37.7349 33.2207 38.1559 33.2207 38.6752C33.2207 39.1945 33.6417 39.6155 34.161 39.6155Z" fill="#DB775F"/>
               <path d="M34.1621 30.024V35.6661" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M19.4941 30.024V34.1615" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M22.6912 41.4963V37.5469C22.6912 35.7828 21.2581 34.3497 19.494 34.3497C17.73 34.3497 16.2969 35.7828 16.2969 37.5469V41.4963" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M17.1357 39.9918H16.9626C16.595 39.9918 16.2969 40.2899 16.2969 40.6576V41.5829C16.2969 41.9506 16.595 42.2486 16.9626 42.2486H17.1357C17.5033 42.2486 17.8014 41.9506 17.8014 41.5829V40.6576C17.8014 40.2899 17.5033 39.9918 17.1357 39.9918Z" fill="black"/>
               <path d="M22.0243 39.9918H21.8513C21.4836 39.9918 21.1855 40.2899 21.1855 40.6576V41.5829C21.1855 41.9506 21.4836 42.2486 21.8513 42.2486H22.0243C22.392 42.2486 22.6901 41.9506 22.6901 41.5829V40.6576C22.6901 40.2899 22.392 39.9918 22.0243 39.9918Z" fill="black"/>
               <path d="M34.3496 21.7154C35.2448 21.7154 35.967 20.8541 35.967 19.7934C35.967 18.7327 35.2411 17.8713 34.3496 17.8713" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M19.6818 21.7154C18.7866 21.7154 18.0645 20.8541 18.0645 19.7934C18.0645 18.7327 18.7904 17.8713 19.6818 17.8713" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M25.2637 23.7957C25.4705 24.4877 26.1739 25.003 27.0165 25.003C27.859 25.003 28.5661 24.4877 28.7692 23.7957" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M33.787 17.6115V21.3992C33.787 24.4346 31.376 27.5302 28.4007 28.1207C24.0563 28.9783 20.2461 25.6796 20.2461 21.4857V17.2354" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M24.3826 20.0568C24.6942 20.0568 24.9468 19.8042 24.9468 19.4925C24.9468 19.1809 24.6942 18.9283 24.3826 18.9283C24.071 18.9283 23.8184 19.1809 23.8184 19.4925C23.8184 19.8042 24.071 20.0568 24.3826 20.0568Z" fill="black"/>
               <path d="M29.6501 20.0568C29.9617 20.0568 30.2143 19.8042 30.2143 19.4925C30.2143 19.1809 29.9617 18.9283 29.6501 18.9283C29.3385 18.9283 29.0859 19.1809 29.0859 19.4925C29.0859 19.8042 29.3385 20.0568 29.6501 20.0568Z" fill="black"/>
               <path d="M30.7766 27.3914V30.4004C30.7766 32.4767 29.0915 34.1618 27.0153 34.1618C24.939 34.1618 23.2539 32.4767 23.2539 30.4004V27.3914" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M22.8783 30.4004L19.8053 30.9796C15.3556 31.9049 12.7227 35.0908 12.7227 39.2471V48.0642H41.309V40.3527V39.2471C41.309 35.0908 38.676 31.9049 34.2263 30.9796L31.1533 30.4004" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M34.9149 17.6118V14.6027C34.9149 10.2395 31.3792 6.70386 27.016 6.70386C26.5421 6.70386 26.0795 6.74524 25.6281 6.82422C24.5937 7.00853 23.7474 7.74952 23.3525 8.71995L23.2547 8.96067H23.187C22.0322 8.88169 20.9264 9.44213 20.3133 10.4238C19.5573 11.635 19.1172 13.0681 19.1172 14.6027V17.6118" stroke="black" stroke-width="1.12841" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M19.6816 17.7998L23.443 12.91" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M34.3494 17.4239L26.4693 16.0661C24.7203 15.7652 23.4414 14.2494 23.4414 12.474V9.15271" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               <path d="M34.1628 41.4965C35.7208 41.4965 36.9838 40.2335 36.9838 38.6755C36.9838 37.1175 35.7208 35.8545 34.1628 35.8545C32.6048 35.8545 31.3418 37.1175 31.3418 38.6755C31.3418 40.2335 32.6048 41.4965 34.1628 41.4965Z" stroke="black" stroke-width="0.752272" stroke-linecap="round" stroke-linejoin="round"/>
               </svg>
           <?php endif; ?>
           <div class="testimonial-card__author-info">
               <span class="testimonial-card__name"><?php echo esc_html($name); ?></span>
               <?php if ($byline) : ?>
                   <span class="testimonial-card__byline"><?php echo esc_html($byline); ?></span>
               <?php endif; ?>
           </div>
        </div>
    <?php else : ?>
        <p class="testimonial-card__placeholder">Select a testimonial to display.</p>
    <?php endif; ?>
</div>
