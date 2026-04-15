<?php
/**
 * Block Name: Copy Permalink
 * Renders a button that copies the current post's permalink to the clipboard.
 */

// Block ID (supports custom anchor)
$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Block classes
$className = 'copy-permalink';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

// Get the current post permalink
$permalink = get_permalink();
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <button
        class="copy-permalink__btn"
        data-permalink="<?php echo esc_url($permalink); ?>"
        aria-label="Copy link to this article"
        title="Copy link"
        type="button"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none" aria-hidden="true" focusable="false">
            <path d="M0 14C0 6.26801 6.26801 0 14 0C21.732 0 28 6.26801 28 14C28 21.732 21.732 28 14 28C6.26801 28 0 21.732 0 14Z" fill="#00508D"/>
            <path d="M14.4415 17.9703L13.5592 18.8525C12.3411 20.0707 10.3661 20.0707 9.14798 18.8525C7.92984 17.6344 7.92984 15.6594 9.14798 14.4413L10.0302 13.559M17.9705 14.4413L18.8528 13.559C20.0709 12.3409 20.0709 10.3659 18.8528 9.14773C17.6346 7.9296 15.6596 7.9296 14.4415 9.14773L13.5592 10.03M11.8169 16.1836L16.1838 11.8166" stroke="white" stroke-width="1.2477" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="copy-permalink__tooltip" aria-live="polite"></span>
    </button>
</div>
