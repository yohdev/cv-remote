/**
 * CVRS Navigation Enhancements
 * - Sticky header shadow on scroll
 * - Mobile accordion: only one section open at a time
 */
document.addEventListener('DOMContentLoaded', function () {

	// ── Sticky header shadow on scroll ──
	const header = document.querySelector('.site-header');
	if (header) {
		let ticking = false;
		window.addEventListener('scroll', function () {
			if (!ticking) {
				window.requestAnimationFrame(function () {
					if (window.scrollY > 10) {
						header.classList.add('scrolled');
					} else {
						header.classList.remove('scrolled');
					}
					ticking = false;
				});
				ticking = true;
			}
		});
	}

	// ── Mobile accordion: collapse siblings when opening a submenu ──
	function initMobileAccordion() {
		const observer = new MutationObserver(function () {
			const toggles = document.querySelectorAll(
				'.wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-submenu__toggle'
			);

			toggles.forEach(function (toggle) {
				if (toggle.dataset.cvrsAccordion) return; // already bound
				toggle.dataset.cvrsAccordion = 'true';

				toggle.addEventListener('click', function () {
					const parent = this.closest('.wp-block-navigation-item');
					if (!parent) return;

					// Delay closing siblings so the current toggle's click
					// fully processes through WP's Interactivity API first.
					// Without this delay, the two state changes collide and
					// the clicked submenu fails to open.
					setTimeout(function () {
						const siblings = parent.parentElement.querySelectorAll(
							':scope > .wp-block-navigation-item'
						);
						siblings.forEach(function (sibling) {
							if (sibling === parent) return;
							const siblingToggle = sibling.querySelector(
								':scope > .wp-block-navigation-submenu__toggle'
							);
							if (siblingToggle && siblingToggle.getAttribute('aria-expanded') === 'true') {
								siblingToggle.click();
							}
						});
					}, 100);
				});
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['class'],
		});
	}

	initMobileAccordion();

	// ── Mobile overlay: inject logo from header ──
	function initMobileOverlayLogo() {
		const observer = new MutationObserver(function () {
			const overlay = document.querySelector(
				'.wp-block-navigation__responsive-container.is-menu-open'
			);
			if (!overlay) return;

			// Don't add if already injected
			if (overlay.querySelector('.mobile-nav-logo')) return;

			const headerLogo = document.querySelector('.top-logo img');
			if (!headerLogo) return;

			const content = overlay.querySelector(
				'.wp-block-navigation__responsive-container-content'
			);
			if (!content) return;

			// Create logo wrapper
			var logoWrapper = document.createElement('div');
			logoWrapper.className = 'mobile-nav-logo';

			var logoLink = document.createElement('a');
			logoLink.href = '/';
			logoLink.setAttribute('aria-label', 'Home');

			var logoImg = document.createElement('img');
			logoImg.src = headerLogo.src;
			logoImg.alt = headerLogo.alt || 'CV Remote Solutions';
			logoImg.style.maxWidth = '180px';
			logoImg.style.height = 'auto';

			logoLink.appendChild(logoImg);
			logoWrapper.appendChild(logoLink);

			// Insert at the top of content
			content.insertBefore(logoWrapper, content.firstChild);
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['class'],
		});
	}

	initMobileOverlayLogo();
});
