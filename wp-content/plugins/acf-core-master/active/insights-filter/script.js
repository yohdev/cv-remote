(function () {
    'use strict';

    function initFilter(block) {
        var form        = block.querySelector('.insights-filter__bar');
        var checkboxes  = block.querySelectorAll('.insights-filter__checkbox');
        var searchInput = block.querySelector('.insights-filter__search');

        if (!form) return;

        // Submit form when a checkbox changes.
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                form.submit();
            });
        });

        // Submit form on Enter in search field.
        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
    }

    function initAll() {
        document.querySelectorAll('.insights-filter').forEach(function (block) {
            if (block.dataset.filterInit) return;
            block.dataset.filterInit = '1';
            initFilter(block);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    if (typeof acf !== 'undefined') {
        acf.addAction('render_block_preview', initAll);
    }
})();
