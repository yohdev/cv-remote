(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initTeamMembers();
    });

    // Re-init when ACF re-renders the block in the editor
    if (typeof acf !== 'undefined') {
        acf.addAction('render_block_preview', initTeamMembers);
    }

    function initTeamMembers() {
        var blocks = document.querySelectorAll('.team-members');

        blocks.forEach(function (block) {
            // Avoid double-binding
            if (block.dataset.tmInit) return;
            block.dataset.tmInit = '1';

            var modal    = block.querySelector('.team-members__modal');
            var backdrop = block.querySelector('.team-members__modal-backdrop');
            var closeBtn = block.querySelector('.team-members__modal-close');
            var bioBtns  = block.querySelectorAll('.team-members__bio-btn');

            if (!modal) return;

            // Open modal when any "View bio" button is clicked
            bioBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var card = btn.closest('.team-members__card');
                    populateModal(modal, card);
                    openModal(modal);
                });
            });

            // Close via backdrop click
            if (backdrop) {
                backdrop.addEventListener('click', function () {
                    closeModal(modal);
                });
            }

            // Close via × button
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    closeModal(modal);
                });
            }

            // Close via Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hidden) {
                    closeModal(modal);
                }
            });
        });
    }

    function populateModal(modal, card) {
        var name        = card.dataset.name        || '';
        var credentials = card.dataset.credentials || '';
        var role        = card.dataset.role        || '';
        var bio         = card.dataset.bio         || '';
        var photoSrc    = card.dataset.photo       || '';
        var photoAlt    = card.dataset.photoAlt    || name;

        setText(modal, '.team-members__modal-name',        name);
        setText(modal, '.team-members__modal-credentials', credentials);
        setText(modal, '.team-members__modal-role',        role);

        // Bio: convert newlines to <p> tags for basic formatting
        var bioEl = modal.querySelector('.team-members__modal-bio');
        if (bioEl) {
            var paragraphs = bio.split(/\n+/).filter(Boolean);
            bioEl.innerHTML = paragraphs.map(function (p) {
                return '<p>' + escapeHtml(p) + '</p>';
            }).join('');
        }

        var photoEl = modal.querySelector('.team-members__modal-photo');
        if (photoEl) {
            photoEl.src = photoSrc;
            photoEl.alt = photoAlt;
        }

        // Social links
        var socialEl = modal.querySelector('.team-members__modal-social');
        if (socialEl) {
            var socialRaw = card.dataset.social || '';
            if (socialRaw) {
                try {
                    var links = JSON.parse(socialRaw);
                    socialEl.innerHTML = links
                        .filter(function (l) { return l.url; })
                        .map(function (l) {
                            var label = l.label || l.url.replace(/^https?:\/\/(www\.)?/, '').split('/')[0];
                            return '<a href="' + escapeHtml(l.url) + '" class="team-members__modal-social-link" target="_blank" rel="noopener noreferrer">' +
                                '<svg class="team-members__modal-social-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>' +
                                '<span>' + escapeHtml(label) + '</span></a>';
                        })
                        .join('');
                } catch (e) {
                    socialEl.innerHTML = '';
                }
            } else {
                socialEl.innerHTML = '';
            }
        }
    }

    function openModal(modal) {
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        // Move focus to close button for accessibility
        var closeBtn = modal.querySelector('.team-members__modal-close');
        if (closeBtn) closeBtn.focus();
    }

    function closeModal(modal) {
        modal.hidden = true;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function setText(modal, selector, value) {
        var el = modal.querySelector(selector);
        if (el) el.textContent = value;
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g,  '&quot;')
            .replace(/'/g,  '&#039;');
    }

}());
