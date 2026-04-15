<?php
/**
 * Block Name: Insights Filter
 *
 * Renders role checkboxes and a search input.
 * Submits via URL parameters (?role[]=physician&ins_search=term).
 * The pre_get_posts hook in module.php handles the actual query filtering.
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'insights-filter';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Get role terms from the taxonomy.
$roles = get_terms([
    'taxonomy'   => 'role',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);

// Current filter state from URL.
$active_roles  = isset($_GET['role']) ? array_map('sanitize_text_field', (array) $_GET['role']) : [];
$active_search = isset($_GET['ins_search']) ? sanitize_text_field($_GET['ins_search']) : '';
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <form class="insights-filter__bar" method="get" action="">
        <div class="insights-filter__roles">
            <span class="insights-filter__roles-label">Filter by role:</span>
            <?php if (!empty($roles) && !is_wp_error($roles)) :
                foreach ($roles as $role) :
                    $checked = in_array($role->slug, $active_roles, true) ? ' checked' : '';
                ?>
                    <label class="insights-filter__role">
                        <input type="checkbox"
                               name="role[]"
                               value="<?php echo esc_attr($role->slug); ?>"
                               class="insights-filter__checkbox"<?php echo $checked; ?> />
                        <span class="insights-filter__role-text"><?php echo esc_html($role->name); ?></span>
                    </label>
                <?php endforeach;
            endif; ?>
        </div>
        <div class="insights-filter__search-wrapper">
            <svg class="insights-filter__search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text"
                   name="ins_search"
                   class="insights-filter__search"
                   placeholder="Search"
                   value="<?php echo esc_attr($active_search); ?>" />
        </div>
        <noscript><button type="submit" class="insights-filter__submit">Apply</button></noscript>
    </form>
</div>
