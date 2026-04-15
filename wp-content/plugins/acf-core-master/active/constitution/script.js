/**
 * Constitution Block JavaScript
 * Handles smooth scrolling, active section detection, and progress tracking
 */

(function() {
	'use strict';

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initConstitutionBlocks);
	} else {
		initConstitutionBlocks();
	}

	function initConstitutionBlocks() {
		const constitutionBlocks = document.querySelectorAll('.constitution-block');
		
		constitutionBlocks.forEach(block => {
			const blockId = block.getAttribute('data-block-id');
			
			// Initialize smooth scrolling
			initSmoothScroll(block, blockId);

			// Initialize active section detection
			initActiveDetection(block, blockId);

			// Initialize progress bar
			initProgressBar(block, blockId);
		});
	}

	/**
	 * Smooth Scrolling
	 */
	function initSmoothScroll(block, blockId) {
		const navLinks = block.querySelectorAll('.constitution-nav__link, .constitution-toc a');
		
		navLinks.forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				
				const targetId = this.getAttribute('href');
				const targetElement = document.querySelector(targetId);
				
				if (targetElement) {
					const offset = 80; // Offset for fixed headers
					const elementPosition = targetElement.getBoundingClientRect().top;
					const offsetPosition = elementPosition + window.pageYOffset - offset;
					
					window.scrollTo({
						top: offsetPosition,
						behavior: 'smooth'
					});
				}
			});
		});
	}

	/**
	 * Active Section Detection
	 */
	function initActiveDetection(block, blockId) {
		const navLinks = block.querySelectorAll('.constitution-nav__link');
		if (navLinks.length === 0) return;

		// Track scroll position and update active state
		let ticking = false;
		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					updateActiveOnScroll(navLinks);
					ticking = false;
				});
				ticking = true;
			}
		});

		// Set initial active state
		updateActiveOnScroll(navLinks);
	}

	function updateActiveOnScroll(links) {
		const scrollPosition = window.scrollY + window.innerHeight / 2;

		let currentActive = null;
		let closestDistance = Infinity;

		links.forEach(link => {
			const targetId = link.getAttribute('href');
			if (!targetId) return;

			const targetElement = document.querySelector(targetId);
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
			links.forEach(link => link.classList.remove('active'));
			currentActive.classList.add('active');
		}
	}

	/**
	 * Progress Bar
	 */
	function initProgressBar(block, blockId) {
		const progressBar = document.getElementById(`reading-progress-${blockId}`);
		if (!progressBar) return;
		
		const content = block.querySelector('.constitution-content');
		if (!content) return;
		
		function updateProgress() {
			const contentRect = content.getBoundingClientRect();
			const contentHeight = content.offsetHeight;
			const windowHeight = window.innerHeight;
			const contentTop = contentRect.top;
			
			// Calculate how much of the content has been scrolled
			const scrolled = Math.max(0, -contentTop);
			const scrollableHeight = contentHeight - windowHeight;
			const progress = Math.min(100, (scrolled / scrollableHeight) * 100);
			
			progressBar.style.width = progress + '%';
		}
		
		// Update on scroll with throttling
		let ticking = false;
		window.addEventListener('scroll', function() {
			if (!ticking) {
				window.requestAnimationFrame(function() {
					updateProgress();
					ticking = false;
				});
				ticking = true;
			}
		});
		
		// Initial update
		updateProgress();
	}

})();