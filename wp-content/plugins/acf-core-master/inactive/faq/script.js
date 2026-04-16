(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initFaq();
    });

    // Re-init when ACF re-renders the block preview in the editor
    if (typeof acf !== 'undefined') {
        acf.addAction('render_block_preview', initFaq);
    }

    function initFaq() {
        var blocks = document.querySelectorAll('.faq');

        blocks.forEach(function (block) {
            if (block.dataset.faqInit) return;
            block.dataset.faqInit = '1';

            var triggers = block.querySelectorAll('.faq__trigger');

            triggers.forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var item     = trigger.closest('.faq__item');
                    var answer   = block.querySelector('#' + trigger.getAttribute('aria-controls'));
                    var isOpen   = trigger.getAttribute('aria-expanded') === 'true';

                    // Close all items in this block
                    block.querySelectorAll('.faq__item').forEach(function (el) {
                        el.classList.remove('is-open');
                    });
                    block.querySelectorAll('.faq__trigger').forEach(function (t) {
                        t.setAttribute('aria-expanded', 'false');
                    });
                    block.querySelectorAll('.faq__answer').forEach(function (a) {
                        a.hidden = true;
                    });

                    // Toggle current item open if it was closed
                    if (!isOpen) {
                        item.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');
                        answer.hidden = false;
                    }
                });
            });
        });
    }

}());
