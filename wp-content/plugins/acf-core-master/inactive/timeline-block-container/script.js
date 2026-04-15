document.addEventListener('scroll', () => {
  // Timeline indicator logic
  const containers = document.querySelectorAll('.timeline-container');
  containers.forEach(container => {
	const indicator = container.querySelector('.time-indicator');
	const rect = container.getBoundingClientRect();
	const viewportHeight = window.innerHeight;
	const triggerPoint = viewportHeight * 0.8;
	
	const isInTriggerZone = rect.top < triggerPoint && rect.bottom > 0;
	
	if (isInTriggerZone) {
	  const progress = Math.min(100, Math.max(0, 
		((triggerPoint - rect.top) / rect.height) * 100
	  ));
	  indicator.style.height = `${progress}%`;
	} else {
	  indicator.style.height = '0%';
	}
  });

  // Show/hide elements logic
  const elements = document.querySelectorAll('.tl-block, .tl-circle, .tl-element');
  elements.forEach(element => {
	const rect = element.getBoundingClientRect();
	const viewportHeight = window.innerHeight;
	const bottomTrigger = viewportHeight * 0.7;
	const topTrigger = viewportHeight * 0.0;
	
	if (rect.top < bottomTrigger && rect.bottom > topTrigger) {
	  element.classList.add('show');
	} else {
	  element.classList.remove('show');
	}
  });
});