<?php
/**
 * Block Name: Breadcrumb
 * Renders: All > Category (optional) > Post Title
 * "All" links to /insights/ (blog home).
 */

// Block ID (supports custom anchor)
$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Block classes
$className = 'breadcrumb';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Editor preview fallback — use a representative post if not in post context
$post_id = get_the_ID();
if (is_admin() && (!$post_id || get_post_type($post_id) !== 'post')) {
    $recent = get_posts(array('numberposts' => 1, 'post_type' => 'post'));
    if (!empty($recent)) {
        $post_id = $recent[0]->ID;
    }
}

$post_title = $post_id ? get_the_title($post_id) : __('Post Title');

// Pick a primary category (first non-"Uncategorized" if possible)
$primary_category = null;
if ($post_id) {
    $categories = get_the_category($post_id);
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            if ($cat->slug !== 'uncategorized') {
                $primary_category = $cat;
                break;
            }
        }
        if (!$primary_category) {
            $primary_category = $categories[0];
        }
    }
}
?>

<nav class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" aria-label="Breadcrumb">
    <ol class="breadcrumb__list">
        <li class="breadcrumb__item">
            <a class="breadcrumb__link" href="<?php echo esc_url(home_url('/insights/')); ?>">All</a>
        </li>

        <?php if ($primary_category) : ?>
            <li class="breadcrumb__separator" aria-hidden="true">&rsaquo;</li>
            <li class="breadcrumb__item">
                <a class="breadcrumb__link" href="<?php echo esc_url(get_category_link($primary_category->term_id)); ?>"><?php echo esc_html($primary_category->name); ?></a>
            </li>
        <?php endif; ?>

        <li class="breadcrumb__separator" aria-hidden="true">&rsaquo;</li>
        <li class="breadcrumb__item breadcrumb__item--current" aria-current="page"><?php echo esc_html($post_title); ?></li>
    </ol>
</nav>
