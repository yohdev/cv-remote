(function() {
    'use strict';

    // Initialize all slider navigation blocks on the page
    function initSliderNavBlocks() {
        const sliderNavBlocks = document.querySelectorAll('.slider-nav-block');

        sliderNavBlocks.forEach(block => {
            initSliderNav(block);
        });
    }

    // Initialize a single slider navigation block
    function initSliderNav(block) {
        const hamburger = block.querySelector('.slider-nav-hamburger');
        const panel = block.querySelector('.slider-nav-panel');
        const closeBtn = block.querySelector('.slider-nav-close, .slides-nav-box');
        const overlay = block.querySelector('.slider-nav-overlay');

        if (!hamburger || !panel) return;

        // Set panel width from ACF field
        const panelWidth = block.getAttribute('data-panel-width');
        if (panelWidth) {
            block.setAttribute('data-panel-width', panelWidth);
        }

        // Toggle menu function
        function toggleMenu(open) {
            const isOpen = open !== undefined ? open : !panel.classList.contains('is-open');

            if (isOpen) {
                // Open menu
                panel.classList.add('is-open');
                panel.setAttribute('aria-hidden', 'false');
                hamburger.setAttribute('aria-expanded', 'true');

                if (overlay) {
                    overlay.classList.add('is-visible');
                    overlay.setAttribute('aria-hidden', 'false');
                }

                // Trap focus
                trapFocus(panel);

                // Add ESC key listener
                document.addEventListener('keydown', handleEscKey);

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            } else {
                // Close menu
                panel.classList.remove('is-open');
                panel.setAttribute('aria-hidden', 'true');
                hamburger.setAttribute('aria-expanded', 'false');

                if (overlay) {
                    overlay.classList.remove('is-visible');
                    overlay.setAttribute('aria-hidden', 'true');
                }

                // Remove ESC key listener
                document.removeEventListener('keydown', handleEscKey);

                // Restore body scroll
                document.body.style.overflow = '';

                // Return focus to hamburger
                hamburger.focus();
            }
        }

        // ESC key handler
        function handleEscKey(e) {
            if (e.key === 'Escape') {
                toggleMenu(false);
            }
        }

        // Focus trap for accessibility
        function trapFocus(element) {
            const focusableElements = element.querySelectorAll(
                'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select, [tabindex]:not([tabindex="-1"])'
            );
            const firstFocusableElement = focusableElements[0];
            const lastFocusableElement = focusableElements[focusableElements.length - 1];

            // Focus on close button initially
            if (closeBtn) {
                closeBtn.focus();
            }

            element.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstFocusableElement) {
                        lastFocusableElement.focus();
                        e.preventDefault();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastFocusableElement) {
                        firstFocusableElement.focus();
                        e.preventDefault();
                    }
                }
            });
        }

        // Event listeners
        hamburger.addEventListener('click', () => toggleMenu());

        if (closeBtn) {
            closeBtn.addEventListener('click', () => toggleMenu(false));
        }

        if (overlay) {
            overlay.addEventListener('click', () => toggleMenu(false));
        }

        // Handle clicks on navigation links (close menu after navigation)
        const navLinks = panel.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // If it's an anchor link on the same page
                if (link.getAttribute('href').startsWith('#')) {
                    setTimeout(() => toggleMenu(false), 100);
                }
            });
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                // Close menu on resize to prevent layout issues
                if (panel.classList.contains('is-open')) {
                    toggleMenu(false);
                }
            }, 250);
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSliderNavBlocks);
    } else {
        initSliderNavBlocks();
    }

    // Re-initialize for Gutenberg editor
    if (window.acf) {
        window.acf.addAction('render_block_preview/type=slider-nav-block', function($block) {
            const block = $block[0].querySelector('.slider-nav-block');
            if (block) {
                initSliderNav(block);
            }
        });
    }

    // Export for external use if needed
    window.SliderNav = {
        init: initSliderNavBlocks,
        initBlock: initSliderNav
    };

})();