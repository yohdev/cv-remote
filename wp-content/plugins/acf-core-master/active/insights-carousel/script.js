(function () {
    'use strict';

    var BREAKPOINT = 782;

    function destroyCarousel(carousel) {
        var track  = carousel.querySelector('.insights-carousel__track');
        var slides = carousel.querySelectorAll('.insights-carousel__slide');

        if (track) {
            track.style.display = '';
            track.style.flexDirection = '';
            track.style.flexWrap = '';
            track.style.gap = '';
            track.style.transform = '';
        }

        for (var i = 0; i < slides.length; i++) {
            slides[i].style.minWidth = '';
            slides[i].style.flexShrink = '';
        }

        delete carousel.dataset.carouselInit;
        carousel._carouselActive = false;
    }

    function initCarousel(carousel) {
        var track     = carousel.querySelector('.insights-carousel__track');
        var slides    = carousel.querySelectorAll('.insights-carousel__slide');
        var btnPrev   = carousel.querySelector('.insights-carousel__btn--prev');
        var btnNext   = carousel.querySelector('.insights-carousel__btn--next');
        var counterEl = carousel.querySelector('.insights-carousel__current');

        if (!track || slides.length === 0) return;

        // Force flex layout in case CSS specificity is losing to WP core
        track.style.display = 'flex';
        track.style.flexDirection = 'row';
        track.style.flexWrap = 'nowrap';
        track.style.gap = '0px';
        for (var i = 0; i < slides.length; i++) {
            slides[i].style.minWidth = '100%';
            slides[i].style.flexShrink = '0';
        }

        var total   = slides.length;
        var current = 0;

        function goTo(index) {
            if (index < 0) index = 0;
            if (index >= total) index = total - 1;

            current = index;
            track.style.transform = 'translateX(-' + (current * 100) + '%)';

            if (counterEl) counterEl.textContent = current + 1;
            if (btnPrev) btnPrev.disabled = current === 0;
            if (btnNext) btnNext.disabled = current === total - 1;
        }

        if (!carousel._listenersAttached) {
            if (btnPrev) btnPrev.addEventListener('click', function () { if (carousel._carouselActive) goTo(current - 1); });
            if (btnNext) btnNext.addEventListener('click', function () { if (carousel._carouselActive) goTo(current + 1); });

            carousel.addEventListener('keydown', function (e) {
                if (!carousel._carouselActive) return;
                if (e.key === 'ArrowLeft')  { goTo(current - 1); e.preventDefault(); }
                if (e.key === 'ArrowRight') { goTo(current + 1); e.preventDefault(); }
            });

            var touchStartX = null;
            carousel.addEventListener('touchstart', function (e) {
                if (carousel._carouselActive) touchStartX = e.touches[0].clientX;
            }, { passive: true });
            carousel.addEventListener('touchend', function (e) {
                if (!carousel._carouselActive || touchStartX === null) return;
                var delta = touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(delta) > 40) goTo(delta > 0 ? current + 1 : current - 1);
                touchStartX = null;
            }, { passive: true });

            carousel._listenersAttached = true;
        }

        carousel._carouselActive = true;
        goTo(0);
    }

    function handleResize() {
        document.querySelectorAll('.insights-carousel').forEach(function (carousel) {
            var isMobile = window.innerWidth < BREAKPOINT;
            var isActive = carousel._carouselActive;

            if (isMobile && !isActive) {
                carousel.dataset.carouselInit = '1';
                initCarousel(carousel);
            } else if (!isMobile && isActive) {
                destroyCarousel(carousel);
            }
        });
    }

    function initAll() {
        if (window.innerWidth >= BREAKPOINT) return;

        document.querySelectorAll('.insights-carousel').forEach(function (carousel) {
            if (carousel.dataset.carouselInit) return;
            carousel.dataset.carouselInit = '1';
            initCarousel(carousel);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Debounced resize handler
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 150);
    });

    if (typeof acf !== 'undefined') {
        acf.addAction('render_block_preview', initAll);
    }
})();
