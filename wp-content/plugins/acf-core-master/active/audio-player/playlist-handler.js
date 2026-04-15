jQuery(document).ready(function($) {
    // Find all playlist players on the page
    $('.audio-playlist-player').each(function() {
        var $container = $(this);
        var playerId = $container.attr('id');

        // Get elements
        var $playBtn = $container.find('.playlist-play-btn');
        var $playlist = $container.find('.playlist-list');
        var $autoplayToggle = $container.find('.playlist-autoplay-toggle input');

        if (!$playBtn.length) return;

        // Get playlist data
        var playlist = JSON.parse($playBtn.attr('data-playlist'));
        var autoplayEnabled = $playBtn.attr('data-autoplay') === 'true';
        var loop = $playBtn.attr('data-loop') === 'true';

        var currentIndex = 0;
        var isPlaylistActive = false;

        // Handle autoplay toggle
        $autoplayToggle.on('change', function() {
            autoplayEnabled = $(this).is(':checked');
            localStorage.setItem('playlist-autoplay-' + playerId, autoplayEnabled);
        });

        // Check saved preference
        var saved = localStorage.getItem('playlist-autoplay-' + playerId);
        if (saved !== null) {
            autoplayEnabled = saved === 'true';
            $autoplayToggle.prop('checked', autoplayEnabled);
        }

        // Update display
        function updateCurrentTrack(index) {
            $container.find('.playlist-current-track').text(playlist[index].title);
            $playlist.find('.playlist-item').removeClass('active');
            $playlist.find('.playlist-item').eq(index).addClass('active');
        }

        // Play track
        function playTrack(index) {
            if (!playlist[index]) return;

            currentIndex = index;
            isPlaylistActive = true;
            updateCurrentTrack(index);

            var audioElement = document.querySelector('.audio_footer audio');
            if (audioElement) {
                audioElement.src = playlist[index].url;
                audioElement.play();
                $('.audio_footer').addClass('show');
                $('.mini-audio-btn').removeClass('show');
                $playBtn.html('<svg viewBox="0 0 24 24" width="40" height="40"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>');
            }
        }

        // Play button click
        $playBtn.on('click', function(e) {
            e.preventDefault();
            var audioElement = document.querySelector('.audio_footer audio');

            if (isPlaylistActive && audioElement && !audioElement.paused) {
                // Just pause, don't reset
                audioElement.pause();
                $playBtn.html('<svg viewBox="0 0 24 24" width="40" height="40"><path d="M8 5v14l11-7z"/></svg>');
            } else if (isPlaylistActive && audioElement && audioElement.paused && audioElement.src) {
                // Resume from where we paused
                audioElement.play();
                $playBtn.html('<svg viewBox="0 0 24 24" width="40" height="40"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>');
            } else {
                // Start playing from the beginning or current track
                playTrack(currentIndex);
            }
        });

        // Playlist item click
        $playlist.on('click', '.playlist-item', function() {
            var index = $(this).data('index');
            playTrack(index);
        });

        // Track ended - use Plyr if available
        function setupEndedListener() {
            if (window.audioFooterPlayer) {
                window.audioFooterPlayer.on('ended', function() {
                    if (!isPlaylistActive || !autoplayEnabled) return;

                    var nextIndex = currentIndex + 1;
                    if (nextIndex < playlist.length) {
                        setTimeout(function() { playTrack(nextIndex); }, 200);
                    } else if (loop) {
                        setTimeout(function() { playTrack(0); }, 200);
                    } else {
                        isPlaylistActive = false;
                        $playBtn.html('<svg viewBox="0 0 24 24" width="40" height="40"><path d="M8 5v14l11-7z"/></svg>');
                    }
                });
            } else {
                setTimeout(setupEndedListener, 500);
            }
        }

        setupEndedListener();
    });
});