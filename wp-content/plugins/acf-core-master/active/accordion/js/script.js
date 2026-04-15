document.addEventListener('DOMContentLoaded', function () {
	const accHeads = document.querySelectorAll('.acc-head');

	if (accHeads.length > 0) {
		// Add 'last' class to last header
		accHeads[accHeads.length - 1].classList.add('last');

		accHeads.forEach(function (head) {
			head.setAttribute('tabindex', '0'); // make focusable
			head.setAttribute('role', 'button');
			head.setAttribute('aria-expanded', 'false');

			const panel = head.nextElementSibling;
			panel.setAttribute('aria-hidden', 'true');
			panel.style.display = 'none';

			function toggleAccordion() {
				const isOpen = head.getAttribute('aria-expanded') === 'true';

				// Close all accordions
				accHeads.forEach(function (otherHead) {
					otherHead.classList.remove('active');
					otherHead.setAttribute('aria-expanded', 'false');
					const otherPanel = otherHead.nextElementSibling;
					otherPanel.style.display = 'none';
					otherPanel.setAttribute('aria-hidden', 'true');
				});

				// Toggle current
				if (!isOpen) {
					head.classList.add('active');
					head.setAttribute('aria-expanded', 'true');
					panel.style.display = 'block';
					panel.setAttribute('aria-hidden', 'false');
				}
			}

			head.addEventListener('click', function (e) {
				toggleAccordion();
				e.preventDefault();
			});

			head.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
					toggleAccordion();
					e.preventDefault();
				}
			});
		});
	}
});
