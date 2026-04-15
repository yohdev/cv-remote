(function () {
    'use strict';

    function initCarousel(carousel) {
        var track     = carousel.querySelector('.testimonials-carousel__track');
        var slides    = carousel.querySelectorAll('.testimonials-carousel__slide');
        var btnPrev   = carousel.querySelector('.testimonials-carousel__btn--prev');
        var btnNext   = carousel.querySelector('.testimonials-carousel__btn--next');
        var counterEl = carousel.querySelector('.testimonials-carousel__current');

        if (!track || slides.length === 0) return;

        var total   = slides.length;
        var current = 0;

        function goTo(index) {
            if (index < 0) index = 0;
            if (index >= total) index = total - 1;

            slides[current].classList.remove('is-active');
            slides[current].setAttribute('aria-hidden', 'true');

            current = index;

            slides[current].classList.add('is-active');
            slides[current].setAttribute('aria-hidden', 'false');

            track.style.transform = 'translateX(-' + (current * 100) + '%)';

            if (counterEl) counterEl.textContent = current + 1;
            if (btnPrev) btnPrev.disabled = current === 0;
            if (btnNext) btnNext.disabled = current === total - 1;
        }

        if (btnPrev) btnPrev.addEventListener('click', function () { goTo(current - 1); });
        if (btnNext) btnNext.addEventListener('click', function () { goTo(current + 1); });

        carousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { goTo(current - 1); e.preventDefault(); }
            if (e.key === 'ArrowRight') { goTo(current + 1); e.preventDefault(); }
        });

        var touchStartX = null;
        carousel.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        carousel.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var delta = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(delta) > 40) goTo(delta > 0 ? current + 1 : current - 1);
            touchStartX = null;
        }, { passive: true });

        goTo(0);
    }

    function initAll() {
        document.querySelectorAll('.testimonials-carousel').forEach(function (carousel) {
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

    if (typeof acf !== 'undefined') {
        acf.addAction('render_block_preview', initAll);
    }
})();
