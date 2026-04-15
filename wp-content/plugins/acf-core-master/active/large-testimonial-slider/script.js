(function () {
    'use strict';

    function initSlider(container) {
        var track = container.querySelector('.large-testimonial-slider__track');
        if (!track) return;

        var realCards = Array.from(track.querySelectorAll('.large-testimonial-slider__card'));
        var btnPrev   = container.querySelector('.large-testimonial-slider__btn--prev');
        var btnNext   = container.querySelector('.large-testimonial-slider__btn--next');
        var counterEl = container.querySelector('.large-testimonial-slider__current');
        var totalEl   = container.querySelector('.large-testimonial-slider__total');

        if (realCards.length === 0) return;

        var total    = realCards.length;
        var isMoving = false;

        // We need enough clones to fill the viewport at the wrap boundary.
        // Desktop shows 2 full + peek = 3 cards visible at once.
        var cloneCount = 3;
        if (cloneCount > total) cloneCount = total;

        // ── Append clones of first N cards to the end ───────────
        for (var i = 0; i < cloneCount; i++) {
            var endClone = realCards[i].cloneNode(true);
            endClone.classList.add('is-clone');
            endClone.setAttribute('aria-hidden', 'true');
            track.appendChild(endClone);
        }

        // ── Prepend clones of last N cards before first real ────
        // Inserted in order so the visual sequence is correct.
        var firstReal = realCards[0];
        for (var j = 0; j < cloneCount; j++) {
            var srcIndex   = total - cloneCount + j;
            var startClone = realCards[srcIndex].cloneNode(true);
            startClone.classList.add('is-clone');
            startClone.setAttribute('aria-hidden', 'true');
            track.insertBefore(startClone, firstReal);
        }

        // Extended layout example (5 real, 3 clones):
        // [c2 c3 c4 | R0 R1 R2 R3 R4 | c0 c1 c2]
        //   0  1  2    3  4  5  6  7     8  9  10
        //
        // current starts at cloneCount (first real card).

        var current = cloneCount;

        // ── Measurement ─────────────────────────────────────────
        function getCardWidth() {
            return realCards[0].getBoundingClientRect().width;
        }

        function getGap() {
            return parseFloat(getComputedStyle(track).gap) || 0;
        }

        function setPosition(index) {
            var cardWidth = getCardWidth();
            var gap       = getGap();
            var offset    = (cardWidth + gap) * index;
            track.style.transform = 'translateX(-' + offset + 'px)';
        }

        // ── Counter (1-indexed, real cards only) ────────────────
        function updateCounter() {
            var realIndex = ((current - cloneCount) % total + total) % total;
            if (counterEl) counterEl.textContent = realIndex + 1;
        }

        // ── Navigate ────────────────────────────────────────────
        function goTo(index) {
            if (isMoving) return;
            isMoving = true;
            current  = index;
            track.style.transition = 'transform 0.4s ease';
            setPosition(current);
            updateCounter();
        }

        function next() { goTo(current + 1); }
        function prev() { goTo(current - 1); }

        // ── After transition: snap if sitting on a clone ────────
        track.addEventListener('transitionend', function (e) {
            if (e.target !== track || e.propertyName !== 'transform') return;

            var needsSnap = false;

            if (current >= total + cloneCount) {
                current = current - total;
                needsSnap = true;
            } else if (current < cloneCount) {
                current = current + total;
                needsSnap = true;
            }

            if (needsSnap) {
                // Kill transition, snap, force paint, then restore.
                track.style.transition = 'none';
                setPosition(current);

                // Double rAF guarantees the browser has painted the
                // snap position before transitions are re-enabled.
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        track.style.transition = 'transform 0.4s ease';
                        isMoving = false;
                    });
                });
            } else {
                isMoving = false;
            }
        });

        // ── Counter total ───────────────────────────────────────
        if (totalEl) totalEl.textContent = total;

        // ── Buttons (never disable — infinite) ──────────────────
        if (btnPrev) btnPrev.addEventListener('click', prev);
        if (btnNext) btnNext.addEventListener('click', next);

        // ── Keyboard ────────────────────────────────────────────
        container.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { prev(); e.preventDefault(); }
            if (e.key === 'ArrowRight') { next(); e.preventDefault(); }
        });

        // ── Touch / swipe ───────────────────────────────────────
        var touchStartX = null;
        container.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });

        container.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var delta = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(delta) > 40) {
                if (delta > 0) { next(); } else { prev(); }
            }
            touchStartX = null;
        }, { passive: true });

        // ── Resize ──────────────────────────────────────────────
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                track.style.transition = 'none';
                setPosition(current);
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        track.style.transition = 'transform 0.4s ease';
                    });
                });
            }, 100);
        });

        // ── Initial position (no animation) ─────────────────────
        track.style.transition = 'none';
        setPosition(current);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                track.style.transition = 'transform 0.4s ease';
            });
        });
        updateCounter();
    }

    // ── Auto-init ───────────────────────────────────────────────
    function initAll() {
        document.querySelectorAll('.large-testimonial-slider').forEach(function (el) {
            if (el.dataset.sliderInit) return;
            el.dataset.sliderInit = '1';
            initSlider(el);
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
