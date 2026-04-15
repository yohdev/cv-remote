//Parallax background
const fadeInOnce = false;

//smooth scroll and other initializations
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll
  document.querySelectorAll('a[href*="#"]').forEach(anchor => {
	anchor.addEventListener('click', function(e) {
	  const url = new URL(this.href, window.location);
	  if (url.pathname === window.location.pathname && url.hash.length > 1) {
		const target = document.querySelector(url.hash);
		if (target) {
		  e.preventDefault();
		  target.scrollIntoView({
			behavior: 'smooth'
		  });
		}
	  }
	});
  });

  // Menu Controls
  // Toggle menu on button click
 const menuBtns = document.querySelectorAll('.menu-btn');
 const menuBlocks = document.querySelectorAll('.mobile-slider');
 
 if (menuBtns.length > 0 && menuBlocks.length > 0) {
   menuBtns.forEach(function (btn) {
	 btn.addEventListener('click', function () {
	   menuBlocks.forEach(function (block) {
		 block.classList.toggle('show');
	   });
	 });
   });
 }


  // Close menu when an in-page anchor link is clicked
  const anchorLinks = document.querySelectorAll('.nav-list a[href*="#"]');

  if (anchorLinks.length > 0 && menuBlocks.length > 0) {
	anchorLinks.forEach(function (link) {
	  link.addEventListener('click', function (e) {
		const linkURL = new URL(link.href);
		const currentURL = window.location;

		// Only close if staying on same page
		if (linkURL.pathname === currentURL.pathname) {
		  menuBlocks.forEach(function (block) {
			block.classList.remove('show');
		  });
		}
	  });
	});
  }
});



document.addEventListener("DOMContentLoaded", function() {
  const wpSiteCover = document.querySelectorAll(".wp-block-cover");

  wpSiteCover.forEach(function(block) {
  setTimeout(function() {
	block.classList.add("fade-in");
  }, 100);
  });
});


// Scroll direction-based header toggle
document.addEventListener('DOMContentLoaded', function() {
  const topHeader = document.querySelector('.top-header');
  if (!topHeader) return;

  let lastScrollY = window.pageYOffset;
  let scrollUpAccumulated = 0;
  let scrollDownAccumulated = 0;
  const threshold = 120;

  window.addEventListener('scroll', function() {
	const currentScrollY = window.pageYOffset;
	const delta = currentScrollY - lastScrollY;

	if (delta > 0) {
	  // Scrolling down - hide header
	  scrollDownAccumulated += delta;
	  scrollUpAccumulated = 0;

	  if (scrollDownAccumulated >= threshold) {
		topHeader.classList.add('scrolled');
	  }
	} else if (delta < 0) {
	  // Scrolling up - show header
	  scrollUpAccumulated += Math.abs(delta);
	  scrollDownAccumulated = 0;

	  if (scrollUpAccumulated >= threshold) {
		topHeader.classList.remove('scrolled');
	  }
	}

	lastScrollY = currentScrollY;
  });
});


// Helper function to determine if an element is in the viewport
function isInViewport(element, offset = 0) {
  const rect = element.getBoundingClientRect();
  const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
  const windowWidth = (window.innerWidth || document.documentElement.clientWidth);
  return (
  rect.top <= (windowHeight * offset) &&
  rect.left <= (windowWidth * offset) &&
  rect.bottom >= 0 &&
  rect.right >= 0
  );
}

// Function to handle the fade-in effect
function fadeInOnScroll(elements, offset) {
  elements.forEach(element => {
	if (isInViewport(element, offset)) {
	  if (!element.classList.contains('has-faded-in') || !fadeInOnce) {
		element.classList.add('visible');
		if (fadeInOnce) {
		  element.classList.add('has-faded-in');
		}
	  }
	} else if (!fadeInOnce) {
	  element.classList.remove('visible');
	}
  });
}

document.addEventListener('DOMContentLoaded', () => {
const fadeElements = document.querySelectorAll('.fade-in-element');
const fadeLeftElements = document.querySelectorAll('.fade-in-left');
const fadeRightElements = document.querySelectorAll('.fade-in-right');
const fadeBottomElements = document.querySelectorAll('.fade-in-bottom');
const offset = 0.80;

  // Initial check to fade-in elements
 fadeInOnScroll(fadeElements, offset);
 fadeInOnScroll(fadeLeftElements, offset);
 fadeInOnScroll(fadeRightElements, offset);
 fadeInOnScroll(fadeBottomElements, offset);

  // Add a scroll event listener to fade-in elements when they enter the viewport
 window.addEventListener('scroll', () => {
   fadeInOnScroll(fadeElements, offset);
   fadeInOnScroll(fadeLeftElements, offset);
   fadeInOnScroll(fadeRightElements, offset);
   fadeInOnScroll(fadeBottomElements, offset);
 });

  window.addEventListener('scroll', function() {
	
	// Top header change state
	const threshold = 200; // number of pixels before applying the scroll effect
	const winPos = window.scrollY;
	const topHeader = document.querySelector('.top-header');
	/*	
	if (topHeader && winPos > threshold) {
		topHeader.classList.add('scrolled');
	} else if (topHeader) {
		topHeader.classList.remove('scrolled');
	}
	*/
	  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
	  const setOpacity = (selector, value) => {
		  const elements = document.querySelectorAll(selector);
		  if (elements.length > 0) {
			  elements.forEach(element => {
				  element.style.opacity = value;
			  });
		  }
	  };
  
	  const setMarginTop = (selector, value) => {
		  const elements = document.querySelectorAll(selector);
		  if (elements.length > 0) {
			  elements.forEach(element => {
				  element.style.marginTop = value;
			  });
		  }
	  };
  
	  const setScale = (selector, value) => {
		  const elements = document.querySelectorAll(selector);
		  if (elements.length > 0) {
			  elements.forEach(element => {
				  element.style.transform = `scale(${value})`;
			  });
		  }
	  };
  
	 const setLetterSpacing = (selector, value) => {
		 const elements = document.querySelectorAll(selector);
		 if (elements.length > 0) {
			 elements.forEach(element => {
				 element.style.letterSpacing = value + 'px'; // set letter-spacing in pixels
			 });
		 }
	 };
	 
	 // Letter spacing settings
	 const baseSpacing = 0.25; // Base letter spacing in pixels
	 const spacingIncrement = 0.02; // Increment letter spacing by 0.01px per pixel scrolled
	 setLetterSpacing('.stretch', baseSpacing + scrollTop * spacingIncrement);
	 
	 // Example scaling settings
	 // Adjust the base scale and scaling factor as per your design needs
	 const baseScale = 1; // Base scale when scroll position is at the top
	 const imgScalingFactor = 0.0001; // Increment scale by 0.001 per pixel scrolled
	 const divScalingFactor = 0.0005; // Increment scale by 0.001 per pixel scrolled
	 setScale('.wp-block-cover.scale img', baseScale + scrollTop * imgScalingFactor);
	 setScale('.scale-image', baseScale + scrollTop * imgScalingFactor);
	 setScale('.scale-up', baseScale + scrollTop * divScalingFactor);
	 setOpacity('.top', 1 - scrollTop / 750);
	 setOpacity('.fadey', 1 - scrollTop / 750);
	 setOpacity('.fadey-fast', 1 - scrollTop / 400);
	 setMarginTop('.wp-block-cover.move-down-img img', 0 + scrollTop * 0.025 + 'vh');
	 setMarginTop('.move-down', 0 + scrollTop * 0.25 + 'vh');
	 setMarginTop('.move-up', 0 - scrollTop * 0.05 + 'vh');
	 setMarginTop('.move-down.med', 0 + scrollTop * 0.1 + 'vh')
	 setMarginTop('.move-down.slow', 0 + scrollTop * 0.065 + 'vh')
	 //setMarginTop('.move-up', 0 + scrollTop * 0.25 + 'vh');
	 setMarginTop('.move-up.slow', 0 - scrollTop * 0.025 + 'vh');
  });
  
  // Grow line height
  
 // Select all elements with the 'grow-line' class
 const growLines = document.querySelectorAll('.grow-line');
 const maxHeightVh = 30; // 30vh
 const minHeightVh = 0; // 0.5vh
 
 function updateLineHeights() {
   if (growLines.length === 0) return;
   
   const viewportHeight = window.innerHeight;
   const scrollPosition = window.scrollY;
 
   growLines.forEach(growLine => {
	 const rect = growLine.getBoundingClientRect();
	 const elementPosition = rect.top + scrollPosition;
	 
	 // Calculate the progress (0 to 1) based on scroll position
	 const progress = Math.min(Math.max((scrollPosition - elementPosition + viewportHeight) / viewportHeight, 0), 1);
	 
	 // Calculate the new height in vh
	 const newHeightVh = minHeightVh + progress * (maxHeightVh - minHeightVh);
	 
	 // Apply the new height
	 growLine.style.height = `${newHeightVh}vh`;
   });
 }
 
 // Initial call to set the initial heights
 updateLineHeights();
 
 // Add scroll event listener
 window.addEventListener('scroll', updateLineHeights);

});


// Modal JS Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Get all elements with the 'toggle-info-window' class
  const toggleButtonOne = document.querySelectorAll('.toggle-button-one');
  const toggleButtonTwo = document.querySelectorAll('.toggle-button-two');
  const toggleButtonVideo = document.querySelectorAll('.toggle-button-video');
  
  if (toggleButtonOne.length > 0) {
	toggleButtonOne.forEach(button => {
	  button.addEventListener('click', function() {
		const infoOverlayOne = document.querySelector('.info-overlay-one');
		if (infoOverlayOne) {
		  infoOverlayOne.classList.toggle('show');
		}
	  });
	});
  }
  
  if (toggleButtonTwo.length > 0) {
	toggleButtonTwo.forEach(button => {
	  button.addEventListener('click', function() {
		const infoOverlayTwo = document.querySelector('.info-overlay-two');
		if (infoOverlayTwo) {
		  infoOverlayTwo.classList.toggle('show');
		}
	  });
	});
  }

  if (toggleButtonVideo.length > 0) {
	toggleButtonVideo.forEach(button => {
	  button.addEventListener('click', function() {
		const infoOverlayVideo = document.querySelector('.video-overlay-one');
		if (infoOverlayVideo) {
		  infoOverlayVideo.classList.toggle('show');
		}
	  });
	});
  }	

});


//Menu JS

document.addEventListener('DOMContentLoaded', function () {
  // 1. Submenu SVG toggle behavior
  const submenuToggles = document.querySelectorAll('.wp-block-navigation-submenu__toggle');

  if (submenuToggles.length > 0) {
	submenuToggles.forEach(toggle => {
	  toggle.addEventListener('click', function (e) {
		e.preventDefault(); // Prevent jump
		const parentLi = toggle.closest('.wp-block-navigation-item');
		if (parentLi) {
		  parentLi.classList.toggle('submenu-open');

		  // Toggle aria-expanded
		  const expanded = toggle.getAttribute('aria-expanded') === 'true';
		  toggle.setAttribute('aria-expanded', !expanded);
		}
	  });
	});
  }

  // 2. Parent link behavior (e.g., clicking "Solutions" goes to page)
  const navLinks = document.querySelectorAll('.wp-block-navigation-item > a');

  if (navLinks.length > 0) {
	navLinks.forEach(link => {
	  link.addEventListener('click', function (e) {
		if (window.innerWidth < 1024) {
		  const hasChild = link.closest('.wp-block-navigation-item.has-child');
		  const toggle = hasChild?.querySelector('.wp-block-navigation-submenu__toggle');

		  if (e.target !== toggle && hasChild?.classList.contains('submenu-open')) {
			hasChild.classList.remove('submenu-open');
		  }

		  // Allows navigation to parent page
		}
	  });
	});
  }
});

/* Top logo change fill */
document.addEventListener('DOMContentLoaded', function() {
  const topLogo = document.querySelector('.top-logo');
  const topNav = document.querySelector('.top-nav');
  const lightSections = document.querySelectorAll('.light');
  const resetSections = document.querySelectorAll('.reset');

  if (topLogo && (lightSections.length > 0 || resetSections.length > 0)) {
	
	// Observer for .light sections (adds on-light)
	const lightObserver = new IntersectionObserver((entries) => {
	  entries.forEach(entry => {
		if (entry.isIntersecting) {
		  topLogo.classList.add('on-light');
		  topNav.classList.add('on-light');
		} else {
		  const anyLightVisible = [...lightSections].some(section => {
			const rect = section.getBoundingClientRect();
			return rect.top < 100 && rect.bottom > 0;
		  });
		  if (!anyLightVisible) {
			topLogo.classList.remove('on-light');
			topNav.classList.remove('on-light');
		  }
		}
	  });
	}, {
	  rootMargin: '-0px 0px -90% 0px'
	});

	// Observer for .reset sections (forces removal of on-light)
	const resetObserver = new IntersectionObserver((entries) => {
	  entries.forEach(entry => {
		if (entry.isIntersecting) {
		  topLogo.classList.remove('on-light');
		  topNav.classList.remove('on-light');
		}
	  });
	}, {
	  rootMargin: '-0px 0px -90% 0px'
	});

	lightSections.forEach(section => lightObserver.observe(section));
	resetSections.forEach(section => resetObserver.observe(section));
  }
});

document.querySelectorAll('.career-block .category-item a').forEach(link => {
  link.addEventListener('click', e => e.preventDefault());
});