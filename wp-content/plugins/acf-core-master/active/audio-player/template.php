<?php
/**
 * Unified Audio Player Block Template
 * Handles both Audio Button and Playlist Player modes
 */

// Get the player mode
$player_mode = get_field('player_mode');

// Create id attribute allowing for custom "anchor" value.
$id = $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Get custom attributes
$attributes = get_field('attributes');

// Handle Button Mode
if ($player_mode === 'button') {

	// Create class attribute for button mode
	$className = 'audio-btn';
	if( !empty($block['className']) ) {
	    $className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
	    $className .= ' align' . $block['align'];
	}

	// Get button mode fields
	$title = get_field('title');
	$audio_source = get_field('audio_source');
	$upload = get_field('upload');
	$remote = get_field('file_url');
	$thumb = get_field('thumb');
	$button_type = get_field('button_type');
	$disclaimer_url = get_field('disclaimer_url');

	// Determine audio URL
	if( $audio_source == 'upload' ) :
		$audio = $upload;
	elseif( $audio_source == 'remote' ):
		$audio = $remote;
	endif;
	?>

	<?php if ( $button_type == 'text' ): ?>
	    <span id="<?php echo esc_attr($id); ?>" class="play_audio <?php echo esc_attr($className); ?>" audiourl="<?php echo $audio; ?>" disclaimerurl="<?php echo $disclaimer_url; ?>" <?php echo $attributes; ?>>
	    <div class="audio-btn-title">
		<svg viewBox="0 0 24 24"><path d="M9,11V5c0-1.7,1.3-3,3-3s3,1.3,3,3v6c0,1.7-1.3,3-3,3S9,12.7,9,11z M17,11c0,2.8-2.2,5-5,5s-5-2.2-5-5H5 c0,3.5,2.6,6.4,6,6.9V21h2v-3.1c3.4-0.5,6-3.4,6-6.9H17z"/></svg><div class="btn-title"><?php echo $title; ?></div>
	    </div>
	    </span>

	<?php else: ?>

	<div class="image-audio-btn play_audio" audiourl="<?php echo $audio; ?>" disclaimerurl="<?php echo $disclaimer_url; ?>" <?php echo $attributes; ?>>
		<div class="image-audio-link">
			<img class="audio-btn-img" style="background-image: url('<?php echo esc_url($thumb); ?>')"  src="<?php echo plugin_dir_url( __FILE__ ); ?>images/audio_thumb.gif"/>
			<div class="audio-title">
				<svg class="audio-play-btn" viewBox="0 0 24 24">
					<path d="M12,2C6.5,2,2,6.5,2,12s4.5,10,10,10s10-4.5,10-10S17.5,2,12,2z M9.8,7.7c0-1.2,1-2.2,2.2-2.2 c1.2,0,2.2,1,2.2,2.2V12c0,1.2-1,2.2-2.2,2.2c-1.2,0-2.2-1-2.2-2.2V7.7z M12.7,17v2.2h-1.4V17c-2.5-0.4-4.3-2.5-4.3-5h1.4 c0,2,1.6,3.6,3.6,3.6c2,0,3.6-1.6,3.6-3.6h1.4C17.1,14.6,15.2,16.7,12.7,17z"/>
				</svg>
			</div>
		</div>
		<div class="video-btn-img-title"><?php echo $title; ?></div>
	</div>

	<?php endif; ?>

<?php
// Handle Playlist Mode
} elseif ($player_mode === 'playlist') {

	// Get playlist mode fields
	$playlist_source = get_field('playlist_source') ?: 'paste';
	$playlist_json = get_field('playlist_json');
	$playlist_remote_url = get_field('playlist_remote_url');
	$autoplay = get_field('autoplay');
	$show_playlist = get_field('show_playlist');
	$loop_playlist = get_field('loop_playlist');

	// Create class attribute for playlist mode
	$className = 'audio-playlist-player';
	if( !empty($block['className']) ) {
		$className .= ' ' . $block['className'];
	}
	if( !empty($block['align']) ) {
		$className .= ' align' . $block['align'];
	}

	// Process the JSON data - either from pasted JSON or remote URL
	$playlist = array();
	$playlist_data = null;

	if ($playlist_source === 'remote' && $playlist_remote_url) {
		// Fetch remote JSON with caching
		$cache_key = 'playlist_' . md5($playlist_remote_url);
		$cached_data = get_transient($cache_key);

		if ($cached_data !== false) {
			$playlist_data = $cached_data;
		} else {
			$response = wp_remote_get($playlist_remote_url, array(
				'timeout' => 10,
				'sslverify' => true
			));

			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$body = wp_remote_retrieve_body($response);
				$playlist_data = json_decode($body, true);

				if (json_last_error() === JSON_ERROR_NONE && is_array($playlist_data)) {
					// Cache for 5 minutes
					set_transient($cache_key, $playlist_data, 5 * MINUTE_IN_SECONDS);
				}
			}
		}
	} elseif ($playlist_json) {
		$playlist_data = json_decode($playlist_json, true);
	}

	if ($playlist_data && json_last_error() === JSON_ERROR_NONE && is_array($playlist_data)) {
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

	// Generate unique player ID
	$player_id = 'playlist-player-' . uniqid();
	?>

	<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" <?php echo $attributes; ?>>
		<?php if (!empty($playlist)): ?>
			<div class="playlist-player-wrapper">
				<div class="playlist-controls">
					<button class="playlist-play-btn"
							data-playlist='<?php echo esc_attr(json_encode($playlist)); ?>'
							data-player-id="<?php echo esc_attr($player_id); ?>"
							data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
							data-loop="<?php echo $loop_playlist ? 'true' : 'false'; ?>">
						<svg viewBox="0 0 24 24" width="40" height="40">
							<path d="M8 5v14l11-7z"/>
						</svg>
					</button>

					<div class="playlist-autoplay-toggle">
						<label for="<?php echo esc_attr($player_id); ?>-autoplay">
							<span>Autoplay</span>
							<input type="checkbox"
								   id="<?php echo esc_attr($player_id); ?>-autoplay"
								   <?php echo $autoplay ? 'checked' : ''; ?>>
							<span class="toggle-slider"></span>
						</label>
					</div>
				</div>
				<div class="current-track-title">
					<span class="playlist-current-track">
						<?php echo isset($playlist[0]) ? esc_html($playlist[0]['title']) : 'No track loaded'; ?>
					</span>
				</div>
			</div>

			<?php if ($show_playlist && count($playlist) > 1): ?>
				<div class="playlist-list" id="<?php echo esc_attr($player_id); ?>-playlist">
					<?php foreach ($playlist as $index => $track): ?>
						<div class="playlist-item <?php echo $index === 0 ? 'active' : ''; ?>"
							 data-url="<?php echo esc_attr($track['url']); ?>"
							 data-index="<?php echo esc_attr($index); ?>">
							<span class="playlist-title"><?php echo esc_html($track['title']); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// Enqueue the external JavaScript file to avoid WordPress text processing
			wp_enqueue_script('playlist-handler', plugin_dir_url(__FILE__) . 'playlist-handler.js', array('jquery'), '1.0', true);
			?>
			<style>
				@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap');
			</style>

		<?php else: ?>
			<div class="playlist-empty-state">
				<p>Please add playlist JSON data to display the player.</p>
			</div>
		<?php endif; ?>
	</div>

<?php } ?>