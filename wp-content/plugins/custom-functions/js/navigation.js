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

	// ── Inject header logo into mobile overlay ──
	function initMobileOverlayLogo() {
		const observer = new MutationObserver(function () {
			const overlay = document.querySelector(
				'.wp-block-navigation__responsive-container.is-menu-open'
			);
			if (!overlay) return;

			// Don't inject twice
			if (overlay.querySelector('.mobile-nav-logo')) return;

			const headerLogo = document.querySelector('.top-logo img');
			if (!headerLogo) return;

			const logoWrapper = document.createElement('div');
			logoWrapper.className = 'mobile-nav-logo';

			const logoLink = document.createElement('a');
			logoLink.href = '/';
			logoLink.setAttribute('aria-label', 'Home');

			const logoImg = headerLogo.cloneNode(true);
			logoLink.appendChild(logoImg);
			logoWrapper.appendChild(logoLink);

			const content = overlay.querySelector(
				'.wp-block-navigation__responsive-container-content'
			);
			if (content) {
				content.insertBefore(logoWrapper, content.firstChild);
			}
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: ['class'],
		});
	}

	initMobileOverlayLogo();

	// ── Close mobile menu whenever a link is clicked ──
	// Anchor links (e.g. #section) on the current page don't trigger a
	// navigation, so the overlay would otherwise stay open. Delegated
	// click simulates pressing the overlay's close button.
	document.addEventListener('click', function (e) {
		const link = e.target.closest('a');
		if (!link) return;

		const overlay = link.closest(
			'.wp-block-navigation__responsive-container.is-menu-open'
		);
		if (!overlay) return;

		const closeBtn = overlay.querySelector(
			'.wp-block-navigation__responsive-container-close'
		);
		if (closeBtn) closeBtn.click();
	});
});
