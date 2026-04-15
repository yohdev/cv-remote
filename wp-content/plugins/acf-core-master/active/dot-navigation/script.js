/**
 * Navigation Dots Block Script
 * Handles smooth scrolling and active state tracking
 */

(function() {
	'use strict';

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		const navDots = document.querySelector('.navigation-dots');
		if (!navDots) return;

		const links = navDots.querySelectorAll('.navigation-dots__link');
		if (!links.length) return;

		// Smooth scroll to anchor on click
		links.forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();

				const anchorId = this.getAttribute('data-anchor');
				const targetElement = document.getElementById(anchorId);

				if (targetElement) {
					targetElement.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});

					// Update active state
					updateActiveState(this);
				}
			});
		});

		// Track scroll position and update active state
		let ticking = false;
		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					updateActiveOnScroll(links);
					ticking = false;
				});
				ticking = true;
			}
		});

		// Set initial active state
		updateActiveOnScroll(links);
	}

	function updateActiveState(activeLink) {
		const allLinks = document.querySelectorAll('.navigation-dots__link');
		allLinks.forEach(link => link.classList.remove('active'));
		activeLink.classList.add('active');
	}

	function updateActiveOnScroll(links) {
		const scrollPosition = window.scrollY + window.innerHeight / 2;

		let currentActive = null;
		let closestDistance = Infinity;

		links.forEach(link => {
			const anchorId = link.getAttribute('data-anchor');
			const targetElement = document.getElementById(anchorId);

			if (targetElement) {
				const elementTop = targetElement.offsetTop;
				const distance = Math.abs(scrollPosition - elementTop);

				if (distance < closestDistance) {
					closestDistance = distance;
					currentActive = link;
				}
			}
		});

		if (currentActive) {
			updateActiveState(currentActive);
		}
	}
})();
