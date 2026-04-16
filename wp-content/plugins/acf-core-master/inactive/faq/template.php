<?php
/**
 * Block Name: FAQ
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'faq';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

$items = get_field('faq_items');

if (empty($items)) : ?>
    <div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
        <p class="faq__empty">No FAQ items added yet.</p>
    </div>
<?php return; endif; ?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">

    <dl class="faq__list">

        <?php foreach ($items as $index => $item) :
            $question = $item['question'] ?? '';
            $answer   = $item['answer']   ?? '';
            $item_id  = $block['id'] . '-item-' . $index;
        ?>
        <div class="faq__item" id="<?php echo esc_attr($item_id); ?>">

            <dt class="faq__question">
                <button
                    class="faq__trigger"
                    type="button"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr($item_id . '-answer'); ?>"
                >
                    <span class="faq__question-text"><?php echo esc_html($question); ?></span>
                    <span class="faq__icon" aria-hidden="true">
                        <svg class="faq__icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <line class="faq__icon-h" x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line class="faq__icon-v" x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                </button>
            </dt>

            <dd class="faq__answer"
                id="<?php echo esc_attr($item_id . '-answer'); ?>"
                hidden>
                <div class="faq__answer-inner">
                    <?php echo wp_kses_post(wpautop($answer)); ?>
                </div>
            </dd>

        </div>
        <?php endforeach; ?>

    </dl>

</div>
