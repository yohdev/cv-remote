/**
 * Accessible Responsive Menu
 * ADA Compliant with keyboard navigation and ARIA support
 */

document.addEventListener('DOMContentLoaded', function () {
	const menuBlocks = document.querySelectorAll('.menu-block');

	menuBlocks.forEach(function (menuBlock) {
		const menuToggle = menuBlock.querySelector('.menu-toggle');
		const menuContainer = menuBlock.querySelector('.menu-container');
		const menuItems = menuBlock.querySelectorAll('.menu-item-has-children');
		const allLinks = menuBlock.querySelectorAll('a');
		const topLevelLinks = menuBlock.querySelectorAll('.primary-menu > li > a');

		// Hamburger Menu Toggle
		if (menuToggle && menuContainer) {
			menuToggle.addEventListener('click', function () {
				toggleMenu();
			});

			// Close menu with Escape key
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && menuContainer.classList.contains('active')) {
					toggleMenu();
					menuToggle.focus();
				}
			});

			// Close menu when clicking outside
			document.addEventListener('click', function (e) {
				if (!menuBlock.contains(e.target) && menuContainer.classList.contains('active')) {
					toggleMenu();
				}
			});

			function toggleMenu() {
				const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
				menuToggle.setAttribute('aria-expanded', !isExpanded);
				if (isExpanded) {
					menuToggle.classList.remove('active');
				} else {
					menuToggle.classList.add('active');
				}
				menuContainer.classList.toggle('active');

				// Manage focus
				if (!isExpanded) {
					// Menu is opening - focus first link
					const firstLink = menuContainer.querySelector('a');
					if (firstLink) {
						setTimeout(() => firstLink.focus(), 100);
					}
				}
			}
		}

		// Submenu Handling
		menuItems.forEach(function (menuItem) {
			const link = menuItem.querySelector('a');
			const submenu = menuItem.querySelector('.sub-menu');

			if (!link || !submenu) return;

			// Click/Touch handling for submenus
			link.addEventListener('click', function (e) {
				// On mobile or when menu item doesn't have href
				if (window.innerWidth <= 768 || link.getAttribute('href') === '#') {
					e.preventDefault();
					toggleSubmenu(link, submenu);
				}
			});

			// Keyboard navigation
			link.addEventListener('keydown', function (e) {
				const isExpanded = link.getAttribute('aria-expanded') === 'true';

				// Enter or Space to toggle submenu
				if (e.key === 'Enter' || e.key === ' ') {
					if (window.innerWidth <= 768 || link.getAttribute('href') === '#') {
						e.preventDefault();
						toggleSubmenu(link, submenu);

						// Focus first submenu item when opening
						if (!isExpanded) {
							const firstSubmenuLink = submenu.querySelector('a');
							if (firstSubmenuLink) {
								setTimeout(() => firstSubmenuLink.focus(), 100);
							}
						}
					}
				}

				// Arrow Down - open submenu and focus first item
				if (e.key === 'ArrowDown') {
					e.preventDefault();
					if (!isExpanded) {
						openSubmenu(link, submenu);
					}
					const firstSubmenuLink = submenu.querySelector('a');
					if (firstSubmenuLink) {
						firstSubmenuLink.focus();
					}
				}

				// Arrow Up - close submenu if it's open
				if (e.key === 'ArrowUp' && isExpanded) {
					e.preventDefault();
					closeSubmenu(link, submenu);
					link.focus();
				}

				// Escape - close submenu
				if (e.key === 'Escape' && isExpanded) {
					e.preventDefault();
					closeSubmenu(link, submenu);
					link.focus();
				}

				// Arrow Right - open submenu (on top level)
				if (e.key === 'ArrowRight' && menuItem.parentElement.classList.contains('primary-menu')) {
					const nextItem = menuItem.nextElementSibling;
					if (nextItem) {
						const nextLink = nextItem.querySelector('a');
						if (nextLink) nextLink.focus();
					}
				}

				// Arrow Left - previous menu item (on top level)
				if (e.key === 'ArrowLeft' && menuItem.parentElement.classList.contains('primary-menu')) {
					const prevItem = menuItem.previousElementSibling;
					if (prevItem) {
						const prevLink = prevItem.querySelector('a');
						if (prevLink) prevLink.focus();
					}
				}
			});

			// Handle submenu items keyboard navigation
			const submenuLinks = submenu.querySelectorAll('a');
			submenuLinks.forEach(function (submenuLink, index) {
				submenuLink.addEventListener('keydown', function (e) {
					// Arrow Down - next submenu item
					if (e.key === 'ArrowDown') {
						e.preventDefault();
						const nextLink = submenuLinks[index + 1];
						if (nextLink) {
							nextLink.focus();
						}
					}

					// Arrow Up - previous submenu item or parent
					if (e.key === 'ArrowUp') {
						e.preventDefault();
						if (index === 0) {
							link.focus();
						} else {
							submenuLinks[index - 1].focus();
						}
					}

					// Escape - close submenu and focus parent
					if (e.key === 'Escape') {
						e.preventDefault();
						closeSubmenu(link, submenu);
						link.focus();
					}
				});
			});

			// Desktop: Open submenu on hover
			if (window.innerWidth > 768) {
				menuItem.addEventListener('mouseenter', function () {
					openSubmenu(link, submenu);
				});

				menuItem.addEventListener('mouseleave', function () {
					closeSubmenu(link, submenu);
				});
			}

			// Focus within submenu keeps it open
			menuItem.addEventListener('focusin', function () {
				if (window.innerWidth > 768) {
					openSubmenu(link, submenu);
				}
			});

			// Close when focus leaves (desktop only)
			menuItem.addEventListener('focusout', function (e) {
				if (window.innerWidth > 768) {
					// Check if focus is moving outside the menu item
					setTimeout(function () {
						if (!menuItem.contains(document.activeElement)) {
							closeSubmenu(link, submenu);
						}
					}, 100);
				}
			});
		});

		// Helper functions
		function toggleSubmenu(link, submenu) {
			const isExpanded = link.getAttribute('aria-expanded') === 'true';
			if (isExpanded) {
				closeSubmenu(link, submenu);
			} else {
				openSubmenu(link, submenu);
			}
		}

		function openSubmenu(link, submenu) {
			link.setAttribute('aria-expanded', 'true');
			submenu.setAttribute('aria-hidden', 'false');
		}

		function closeSubmenu(link, submenu) {
			link.setAttribute('aria-expanded', 'false');
			submenu.setAttribute('aria-hidden', 'true');
		}

		// Handle window resize - close mobile menu when switching to desktop
		let resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				if (window.innerWidth > 768 && menuContainer.classList.contains('active')) {
					menuToggle.setAttribute('aria-expanded', 'false');
					menuToggle.classList.remove('active');
					menuContainer.classList.remove('active');
				}
			}, 250);
		});
	});
});
