(function($){	
	$( document ).ready(function() {
	
	const player = new Plyr('#audio');
	
	// Store preloading state
	var preloadedAudio = {};
	var currentlyPreloading = null;
	
	function showaudio() {     
		$('.audio_footer').addClass('show');
	}
	 
	function hidemini() {     
		$('.mini-audio-btn').removeClass('show');
	}
	
	// Smart preloading based on file size
	function smartPreload(url, callback) {
		// Check if already preloaded
		if (preloadedAudio[url]) {
			if (callback) callback(true);
			return;
		}
		
		// Create a hidden audio element for preloading
		var testAudio = new Audio();
		testAudio.preload = 'metadata'; // Only load metadata first
		
		// Set up event handlers
		testAudio.addEventListener('loadedmetadata', function() {
			var duration = testAudio.duration;
			console.log('Audio duration:', duration, 'seconds');
			
			// For files longer than 5 minutes, use streaming approach
			if (duration > 300) {
				console.log('Large file detected, using streaming approach');
				testAudio.preload = 'none'; // Don't preload large files
				preloadedAudio[url] = 'stream';
			} else {
				console.log('Small file, preloading fully');
				testAudio.preload = 'auto'; // Fully preload small files
				preloadedAudio[url] = 'preload';
			}
			
			if (callback) callback(true);
		});
		
		testAudio.addEventListener('error', function() {
			console.error('Error loading audio metadata:', url);
			if (callback) callback(false);
		});
		
		// Start loading metadata
		testAudio.src = url;
		testAudio.load();
		
		// Store reference
		currentlyPreloading = testAudio;
	}
	
	// Streaming-optimized playback for large files
	function playWithStreaming(disclaimerUrl, mainUrl) {
		var audioElement = document.getElementById('audio');
		
		// Clear any existing handlers
		$(audioElement).off('ended.disclaimer');
		
		// Configure for streaming
		audioElement.preload = 'metadata'; // Start with just metadata
		
		if (disclaimerUrl && disclaimerUrl.trim() !== '') {
			// Play disclaimer first
			audioElement.src = disclaimerUrl;
			
			// Start loading main audio metadata in background
			smartPreload(mainUrl, function(success) {
				console.log('Main audio preload status:', success);
			});
			
			// When disclaimer ends, switch to main audio
			$(audioElement).one('ended.disclaimer', function() {
				console.log('Disclaimer ended, starting main audio stream');
				
				// For streaming, we don't preload the entire file
				// Just set the source and let it buffer progressively
				audioElement.preload = 'auto'; // Allow buffering
				audioElement.src = mainUrl;
				
				// Create a promise for play action
				var playPromise = audioElement.play();
				
				if (playPromise !== undefined) {
					playPromise.then(function() {
						console.log('Main audio streaming started');
						
						// Monitor buffering progress
						$(audioElement).on('progress', function() {
							var buffered = audioElement.buffered;
							if (buffered.length > 0) {
								var bufferedEnd = buffered.end(buffered.length - 1);
								var duration = audioElement.duration;
								if (duration > 0) {
									var bufferedPercent = (bufferedEnd / duration) * 100;
									console.log('Buffered:', bufferedPercent.toFixed(1) + '%');
								}
							}
						});
					}).catch(function(error) {
						console.error('Playback failed:', error);
						// Show play button if autoplay fails
						player.toggleControls(true);
					});
				}
			});
			
			// Start disclaimer playback
			audioElement.play().then(function() {
				console.log('Disclaimer playing');
			}).catch(function(error) {
				console.error('Could not play disclaimer:', error);
			});
			
		} else {
			// No disclaimer, stream main audio directly
			audioElement.preload = 'auto';
			audioElement.src = mainUrl;
			audioElement.play();
		}
	}
	
	// Range request support for better streaming
	function setupRangeRequests(audioElement) {
		// This ensures the server supports partial content requests
		// Most modern servers do, but we can add headers if needed
		
		// Monitor buffering and adjust strategy
		var lastBufferCheck = 0;
		var bufferCheckInterval;
		
		function checkBuffer() {
			if (audioElement.buffered.length > 0) {
				var buffered = audioElement.buffered.end(0);
				var currentTime = audioElement.currentTime;
				var bufferAhead = buffered - currentTime;
				
				// Maintain 30 seconds of buffer ahead
				if (bufferAhead < 30 && audioElement.preload !== 'auto') {
					audioElement.preload = 'auto';
					console.log('Low buffer, increasing preload');
				} else if (bufferAhead > 60 && audioElement.preload !== 'metadata') {
					// Reduce preloading if we have plenty of buffer
					audioElement.preload = 'metadata';
					console.log('Sufficient buffer, reducing preload');
				}
			}
		}
		
		$(audioElement).on('playing', function() {
			// Start monitoring buffer when playing
			bufferCheckInterval = setInterval(checkBuffer, 5000);
		});
		
		$(audioElement).on('pause ended', function() {
			// Stop monitoring when not playing
			clearInterval(bufferCheckInterval);
		});
	}
	
	// Function to handle audio button activation
	function handleAudioButtonClick(element) {
		$('.play_audio.active, .list-group-item.active').removeClass('active');
		$('.image-audio-btn.active').removeClass('active');
		$(element).addClass('active');
		showaudio();
		hidemini();
		
		var disclaimerUrl = $(element).attr("disclaimerUrl");
		var mainAudioUrl = $(element).attr("audioUrl");
		var audioElement = document.getElementById('audio');
		
		// Clear any existing handlers
		$(audioElement).off('ended.disclaimer progress playing pause ended');
		
		// Cancel any preloading in progress
		if (currentlyPreloading) {
			currentlyPreloading.src = '';
			currentlyPreloading = null;
		}
		
		// Use streaming approach for all files
		playWithStreaming(disclaimerUrl, mainAudioUrl);
		
		// Set up range request optimization
		setupRangeRequests(audioElement);
	}
	
	// Detect if it's a touch device
	var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
	
	if (isTouchDevice) {
		// For touch devices (Safari on iOS), use touchend to avoid hover state issues
		$(document).on('touchend', '.play_audio', function(e) {
			e.preventDefault();
			e.stopPropagation();
			handleAudioButtonClick(this);
		});
	} else {
		// For non-touch devices, use click
		$(document).on('click', '.play_audio', function(e) {
			e.preventDefault();
			handleAudioButtonClick(this);
		});
	}
	
	// Preload audio on hover (desktop only) for better responsiveness
	if (!isTouchDevice) {
		$(document).on('mouseenter', '.play_audio', function() {
			var mainAudioUrl = $(this).attr("audioUrl");
			var disclaimerUrl = $(this).attr("disclaimerUrl");
			
			// Preload metadata on hover
			if (disclaimerUrl && disclaimerUrl.trim() !== '') {
				smartPreload(disclaimerUrl);
			}
			if (mainAudioUrl) {
				smartPreload(mainAudioUrl);
			}
		});
	}
	
	// Mini audio button handler
	$( ".mini-audio-btn" ).click(function() {
		$('.audio_footer').addClass('show');
		$(this).removeClass('show');
	});
	
	// Hide player handler
	$( ".hide-player" ).click(function() {
		$('.audio_footer').removeClass('show');
		$('.mini-audio-btn').addClass('show');
	});
	
	// Close player handler
	$( ".close-player" ).click(function() {
		$('.play_audio.active, .list-group-item.active').removeClass('active');
		$('.image-audio-btn.active').removeClass('active');
		
		var audioElement = document.getElementById('audio');
		audioElement.pause();
		
		// Clear source to stop loading
		audioElement.src = '';
		
		$('.audio_footer').removeClass('show');
		
		// Clean up handlers
		$(audioElement).off('ended.disclaimer progress playing pause ended');
		
		// Cancel any preloading
		if (currentlyPreloading) {
			currentlyPreloading.src = '';
			currentlyPreloading = null;
		}
	});
	
	// Optional: Add visual loading indicator
	var audioElement = document.getElementById('audio');
	
	$(audioElement).on('waiting', function() {
		console.log('Buffering...');
		// Could add a loading spinner here
	});
	
	$(audioElement).on('canplay', function() {
		console.log('Ready to play');
		// Remove loading spinner
	});
	
});
	
})(jQuery)