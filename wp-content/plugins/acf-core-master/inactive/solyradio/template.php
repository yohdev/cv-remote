<?php
/**
 * Block Name: SolyRadio Player
 *
 * This is the template for the SolyRadio Player Block
 */

// Get ACF field values
$playlist_json = get_field('playlist_json');
$autoplay = get_field('autoplay');
$show_playlist = get_field('show_playlist');
$loop_playlist = get_field('loop_playlist');

// Create id attribute for specific styling
$id = $block['id'];
if( !empty($block['anchor']) ) {
	$id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'solyradio-player';
if( !empty($block['className']) ) {
	$className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
	$className .= ' align' . $block['align'];
}

// Process the JSON data
$playlist = array();
if ($playlist_json) {
	$playlist_data = json_decode($playlist_json, true);
	
	if (json_last_error() === JSON_ERROR_NONE && is_array($playlist_data)) {
		// Process URLs - add https://solyradio.com to relative URLs
		foreach ($playlist_data as $key => $track) {
			if (isset($track['url']) && strpos($track['url'], '/') === 0) {
				// URL starts with / but no domain, add solyradio.com
				$playlist_data[$key]['url'] = 'https://solyradio.com' . $track['url'];
			}
		}
		unset($track); // Break the reference
		$playlist = $playlist_data;
	}
}

// Generate unique player ID
$player_id = 'solyradio-player-' . uniqid();

?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
	<?php if (!empty($playlist)): ?>
		<div class="solyradio-player-wrapper">
			<audio id="<?php echo esc_attr($player_id); ?>" controls <?php echo $autoplay ? 'autoplay' : ''; ?>>
				<?php if (isset($playlist[0])): ?>
					<source src="<?php echo esc_url($playlist[0]['url']); ?>" type="audio/mpeg">
				<?php endif; ?>
			</audio>
			<div class="solyradio-autoplay-toggle">
				<label for="<?php echo esc_attr($player_id); ?>-autoplay">
					<span>Autoplay</span>
					<input type="checkbox" id="<?php echo esc_attr($player_id); ?>-autoplay" <?php echo $autoplay ? 'checked' : ''; ?>>
				</label>
			</div>
		</div>
		
		<?php if ($show_playlist && count($playlist) > 1): ?>
			<div class="solyradio-playlist" id="<?php echo esc_attr($player_id); ?>-playlist">
				<?php foreach ($playlist as $index => $track): ?>
					<div class="solyradio-playlist-item <?php echo $index === 0 ? 'active' : ''; ?>" 
						 data-url="<?php echo esc_attr($track['url']); ?>"
						 data-index="<?php echo esc_attr($index); ?>">
						<span style="opacity: 0.5; margin-right: 8px;"></span>
						<?php echo esc_html($track['title']); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				try {
					const playerId = '<?php echo esc_js($player_id); ?>';
					const playerElement = document.getElementById(playerId);
					
					if (!playerElement) {
						return;
					}
					
					// Parse playlist from base64 to avoid quote issues
					const playlistData = '<?php echo base64_encode(json_encode($playlist)); ?>';
					const playlist = JSON.parse(atob(playlistData));
					let currentTrackIndex = 0;
					let autoplayEnabled = <?php echo ($autoplay !== false) ? 'true' : 'false'; ?>;
					
					// Check if Plyr is loaded
					if (typeof Plyr === 'undefined') {
						return;
					}
					
					// Initialize Plyr
					const player = new Plyr(playerElement, {
						controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'fullscreen'],
					});
				
				// Handle autoplay toggle
				const autoplayToggle = document.getElementById(playerId + '-autoplay');
				if (autoplayToggle) {
					autoplayToggle.addEventListener('change', function() {
						autoplayEnabled = this.checked;
						// Store preference in localStorage
						localStorage.setItem('solyradio-autoplay', autoplayEnabled ? 'true' : 'false');
					});
					
					// Check for saved autoplay preference
					const savedAutoplay = localStorage.getItem('solyradio-autoplay');
					if (savedAutoplay !== null) {
						autoplayEnabled = savedAutoplay === 'true';
						autoplayToggle.checked = autoplayEnabled;
					}
				}
				
				// Update active playlist item
				function updateActiveTrack(index) {
					const playlistElement = document.getElementById(playerId + '-playlist');
					if (playlistElement) {
						const items = playlistElement.querySelectorAll('.solyradio-playlist-item');
						items.forEach((item, i) => {
							if (i === index) {
								item.classList.add('active');
							} else {
								item.classList.remove('active');
							}
						});
					}
				}
				
				// Play track by index
				function playTrack(index) {
					// Ensure index is valid
					if (playlist[index]) {
						currentTrackIndex = index;
						player.source = {
							type: 'audio',
							sources: [{
								src: playlist[index].url,
								type: 'audio/mpeg'
							}]
						};
						updateActiveTrack(index);
						player.play();
					}
				}
				
				// Get loop setting from PHP
				const loopEnabled = <?php echo ($loop_playlist !== false) ? 'true' : 'false'; ?>;

				// Auto-advance to next track
				player.on('ended', function() {
					if (autoplayEnabled) {
						const nextIndex = currentTrackIndex + 1;
						if (nextIndex < playlist.length) {
							// Play next track
							playTrack(nextIndex);
						} else if (loopEnabled) {
							// Loop back to first track when playlist ends (if loop is enabled)
							playTrack(0);
						}
					}
				});
				
				// Handle playlist item clicks
				const playlistElement = document.getElementById(playerId + '-playlist');
				if (playlistElement) {
					playlistElement.addEventListener('click', function(e) {
						const item = e.target.closest('.solyradio-playlist-item');
						if (item) {
							const index = parseInt(item.dataset.index);
							playTrack(index);
						}
					});
				}
				} catch (error) {
					console.error('SolyRadio Player Error:', error);
				}
			});
		</script>
	<?php else: ?>
		<p>Please add playlist JSON data to display the player.</p>
	<?php endif; ?>
</div>