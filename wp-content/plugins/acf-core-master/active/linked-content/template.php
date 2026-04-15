<?php
/**
 * Block Name: Linked Content
 * Wraps InnerBlocks content in a clickable link.
 */

// Block ID
$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Block classes
$className = 'linked-content';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// ACF field
$link = get_field('linked_content_link');
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <?php if ($is_preview) : ?>
        <?php echo '<InnerBlocks />'; ?>
    <?php elseif ($link) : ?>
        <a
            class="linked-content__link"
            href="<?php echo esc_url($link['url']); ?>"
            <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '" rel="noopener noreferrer"' : ''; ?>
            <?php echo !empty($link['title']) ? 'title="' . esc_attr($link['title']) . '"' : ''; ?>
        >
            <?php echo $content; ?>
        </a>
    <?php else : ?>
        <?php echo $content; ?>
    <?php endif; ?>
</div>
