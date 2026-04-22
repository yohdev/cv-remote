(function () {
    'use strict';

    function initCarousel(carousel) {
        var track        = carousel.querySelector('.testimonials-carousel__track');
        var trackWrapper = carousel.querySelector('.testimonials-carousel__track-wrapper');
        if (!track) return;

        var realSlides = Array.from(track.querySelectorAll('.testimonials-carousel__slide'));
        var btnPrev    = carousel.querySelector('.testimonials-carousel__btn--prev');
        var btnNext    = carousel.querySelector('.testimonials-carousel__btn--next');
        var counterEl  = carousel.querySelector('.testimonials-carousel__current');
        var totalEl    = carousel.querySelector('.testimonials-carousel__total');

        if (realSlides.length === 0) return;

        var total      = realSlides.length;
        var isMoving   = false;
        var moveTimer  = null;
        var cloneCount = 1;

        function clearMoveTimer() {
            if (moveTimer) {
                clearTimeout(moveTimer);
                moveTimer = null;
            }
        }
        if (cloneCount > total) cloneCount = total;

        // ── Append clone of first slide to the end ──────────────
        for (var i = 0; i < cloneCount; i++) {
            var endClone = realSlides[i].cloneNode(true);
            endClone.classList.add('is-clone');
            endClone.classList.remove('is-active');
            endClone.setAttribute('aria-hidden', 'true');
            track.appendChild(endClone);
        }

        // ── Prepend clone of last slide before first real ───────
        var firstReal = realSlides[0];
        for (var j = 0; j < cloneCount; j++) {
            var srcIndex   = total - cloneCount + j;
            var startClone = realSlides[srcIndex].cloneNode(true);
            startClone.classList.add('is-clone');
            startClone.classList.remove('is-active');
            startClone.setAttribute('aria-hidden', 'true');
            track.insertBefore(startClone, firstReal);
        }

        // current starts at cloneCount (first real slide)
        var current = cloneCount;

        function setPosition(index) {
            track.style.transform = 'translateX(-' + (index * 100) + '%)';
        }

        function updateCounter() {
            var realIndex = ((current - cloneCount) % total + total) % total;
            if (counterEl) counterEl.textContent = realIndex + 1;
        }

        function syncHeight() {
            if (!trackWrapper) return;
            var allSlides = track.querySelectorAll('.testimonials-carousel__slide');
            var active = allSlides[current];
            if (active) {
                trackWrapper.style.height = active.offsetHeight + 'px';
            }
        }

        function goTo(index) {
            if (isMoving) return;
            isMoving = true;
            current  = index;
            track.style.transition = 'transform 0.4s ease';
            setPosition(current);
            updateCounter();
            syncHeight();

            // Safety net: if transitionend never fires (e.g. iOS Safari
            // cancels the transition when the address bar toggles),
            // release the lock so the carousel can still advance.
            clearMoveTimer();
            moveTimer = setTimeout(function () {
                isMoving = false;
                moveTimer = null;
            }, 500);
        }

        function next() { goTo(current + 1); }
        function prev() { goTo(current - 1); }

        // ── After transition: snap if sitting on a clone ────────
        track.addEventListener('transitionend', function (e) {
            if (e.target !== track || e.propertyName !== 'transform') return;

            clearMoveTimer();

            var needsSnap = false;

            if (current >= total + cloneCount) {
                current = current - total;
                needsSnap = true;
            } else if (current < cloneCount) {
                current = current + total;
                needsSnap = true;
            }

            if (needsSnap) {
                track.style.transition = 'none';
                trackWrapper.style.transition = 'none';
                setPosition(current);
                syncHeight();
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        track.style.transition = 'transform 0.4s ease';
                        trackWrapper.style.transition = 'height 0.4s ease';
                        isMoving = false;
                    });
                });
            } else {
                isMoving = false;
            }
        });

        track.addEventListener('transitioncancel', function (e) {
            if (e.target !== track || e.propertyName !== 'transform') return;
            clearMoveTimer();
            isMoving = false;
        });

        if (totalEl) totalEl.textContent = total;

        if (btnPrev) btnPrev.addEventListener('click', prev);
        if (btnNext) btnNext.addEventListener('click', next);

        carousel.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { prev(); e.preventDefault(); }
            if (e.key === 'ArrowRight') { next(); e.preventDefault(); }
        });

        var touchStartX = null;
        carousel.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        carousel.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var delta = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(delta) > 40) {
                if (delta > 0) { next(); } else { prev(); }
            }
            touchStartX = null;
        }, { passive: true });

        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                track.style.transition = 'none';
                trackWrapper.style.transition = 'none';
                setPosition(current);
                syncHeight();
                // Resize forcibly kills any in-flight transition; make sure
                // isMoving doesn't stay stuck at true.
                clearMoveTimer();
                isMoving = false;
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        track.style.transition = 'transform 0.4s ease';
                        trackWrapper.style.transition = 'height 0.4s ease';
                    });
                });
            }, 100);
        });

        // ── Initial position (no animation) ─────────────────────
        track.style.transition = 'none';
        trackWrapper.style.transition = 'none';
        setPosition(current);
        syncHeight();
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                track.style.transition = 'transform 0.4s ease';
                trackWrapper.style.transition = 'height 0.4s ease';
            });
        });
        updateCounter();
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
