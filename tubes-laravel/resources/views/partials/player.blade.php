<div id="music-player" class="fixed-bottom bg-dark-950 border-top border-dark-700 p-3 fade-in" style="z-index: 1050; transition: transform 0.3s ease;">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-xxl d-flex align-items-center justify-content-between">
        <!-- Song Info -->
        <div class="d-flex align-items-center gap-3" style="width: 30%;">
            <img id="player-cover" src="{{ asset('assets/img/logo.png') }}" class="rounded bg-dark-800" width="56" height="56" style="object-fit: cover;">
            <div class="overflow-hidden">
                <h6 id="player-title" class="mb-0 text-truncate fw-bold text-white">Select a song</h6>
                <small id="player-artist" class="text-dark-300 text-truncate">UKM Band</small>
            </div>
        </div>

        <!-- Controls -->
        <div class="d-flex flex-column align-items-center justify-content-center flex-grow-1 mx-3" style="max-width: 500px;">
            <div class="d-flex align-items-center gap-3 mb-2">
                <button class="btn btn-sm btn-link text-dark-300 hover-text-white" id="shuffle-btn" onclick="toggleShuffle()" title="Shuffle">
                    <i class="bi bi-shuffle"></i>
                </button>
                <button class="btn btn-sm btn-link text-dark-300 hover-text-white" onclick="prevSong()"><i class="bi bi-skip-start-fill fs-4"></i></button>
                <button id="play-pause-btn" class="btn btn-accent rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" onclick="togglePlay()">
                    <i class="bi bi-play-fill fs-4 ms-1"></i>
                </button>
                <button class="btn btn-sm btn-link text-dark-300 hover-text-white" onclick="nextSong()"><i class="bi bi-skip-end-fill fs-4"></i></button>
                <button class="btn btn-sm btn-link text-dark-300 hover-text-white" id="repeat-btn" onclick="toggleRepeat()" title="Repeat">
                    <i class="bi bi-repeat"></i>
                </button>
            </div>
            <div class="w-100 d-flex align-items-center gap-2">
                <span id="current-time" class="small text-dark-300" style="font-size: 10px; min-width: 35px;">0:00</span>
                <input type="range" id="seek-bar" class="form-range flex-grow-1" min="0" value="0" step="1" onchange="seekAudio()" oninput="updateSeekUI()">
                <span id="duration" class="small text-dark-300" style="font-size: 10px; min-width: 35px;">0:00</span>
            </div>
        </div>

        <!-- Volume & Extras -->
        <div class="d-flex align-items-center justify-content-end gap-3" style="width: 30%;">
           <div class="d-flex align-items-center gap-2" style="width: 120px;">
                <button class="btn btn-sm btn-link text-dark-300 hover-text-white p-0" onclick="toggleMute()" id="mute-btn">
                    <i class="bi bi-volume-up"></i>
                </button>
                <input type="range" id="volume-bar" class="form-range" min="0" max="1" step="0.1" value="1" onchange="setVolume()">
            </div>
        </div>

        <audio id="global-audio" preload="none"></audio>
    </div>
</div>

<script>
    // Global Playlist Data
    let playlist = @json($globalSongs ?? []);
    let currentIndex = -1;
    let isShuffle = false;
    let isRepeat = false;
    let previousVolume = 1;

    const audio = document.getElementById('global-audio');
    const playBtn = document.getElementById('play-pause-btn');
    const cover = document.getElementById('player-cover');
    const title = document.getElementById('player-title');
    const artist = document.getElementById('player-artist');
    const seekBar = document.getElementById('seek-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');
    const shuffleBtn = document.getElementById('shuffle-btn');
    const repeatBtn = document.getElementById('repeat-btn');
    const muteBtn = document.getElementById('mute-btn');
    const volumeBar = document.getElementById('volume-bar');

    // Function to set a custom playlist (scoping the queue)
    function setQueue(newSongs) {
        playlist = newSongs;
        currentIndex = -1;
    }

    // Play a full playlist
    function playPlaylist(songs) {
        if (!songs || songs.length === 0) return;
        setQueue(songs);
        playSong(songs[0].id);
    }

    // Function to load and play a song by ID or Index
    function playSong(id) {
        const index = playlist.findIndex(s => s.id == id);
        if (index !== -1) {
            currentIndex = index;
            loadSong(playlist[currentIndex]);
            audio.play();
            updatePlayBtn(true);
            recordPlay(playlist[currentIndex].id);
        } else {
            // If song is not in current playlist, likely from "Select a song" or search
            // We might want to reset to global or add to queue? 
            // For now, if passed ID is not in current scoped playlist, we fallback to global.
            // User requested that clicking "Putar Playlist" scopes it.
            // If user clicks "Play Now" on a song detail, it typically plays THAT song.
            // If that song is not in the "playlist", what happens?
            // To be safe: if song not found, we temporarily play it as single or switch to global.
            // Let's reload global if not found, assuming "Play Now" means global context or single context.
            // But for efficiency, let's just assume playSong is called with a visible song.
            // If we are on Song Detail, we might want to ensure it plays.
            // Let's fallback to global if not found.
            const global = @json($globalSongs ?? []);
            const globalIndex = global.findIndex(s => s.id == id);
            if (globalIndex !== -1) {
                playlist = global; // Reset to global
                currentIndex = globalIndex;
                loadSong(playlist[currentIndex]);
                audio.play();
                updatePlayBtn(true);
                recordPlay(playlist[currentIndex].id);
            }
        }
    }
    
    // Record Play History
    function recordPlay(songId) {
        // Prevent spamming history if same song logs multiple times? Backend handles logic?
        // Backend creates if new, updates if exists.
        // We only call this on explicit play start.
        if (!songId) return;

        fetch(`/songs/${songId}/record-play`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        }).catch(err => console.error('Error recording play:', err));
    }

    function loadSong(song) {
        title.innerText = song.title;
        artist.innerText = song.artist;
        cover.src = "{{ asset('') }}" + song.cover_path; // Handle asset path
        audio.src = "{{ route('songs.stream', ':id') }}".replace(':id', song.id);
        
        // Update browser media session
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: song.title,
                artist: song.artist,
                artwork: [{ src: "{{ asset('') }}" + song.cover_path }]
            });
            
            navigator.mediaSession.setActionHandler('play', togglePlay);
            navigator.mediaSession.setActionHandler('pause', togglePlay);
            navigator.mediaSession.setActionHandler('previoustrack', prevSong);
            navigator.mediaSession.setActionHandler('nexttrack', nextSong);
        }
    }

    function togglePlay() {
        if (currentIndex === -1 && playlist.length > 0) {
            playSong(playlist[0].id);
            return;
        }

        if (audio.paused) {
            audio.play();
            updatePlayBtn(true);
        } else {
            audio.pause();
            updatePlayBtn(false);
        }
    }

    function updatePlayBtn(isPlaying) {
        playBtn.innerHTML = isPlaying ? '<i class="bi bi-pause-fill fs-4"></i>' : '<i class="bi bi-play-fill fs-4 ms-1"></i>';
    }

    function prevSong() {
        if (currentIndex > 0) {
            currentIndex--;
        } else if (isRepeat) {
             currentIndex = playlist.length - 1; // Wrap around if repeat is on
        } else {
            return; // Start of playlist
        }
        
        loadSong(playlist[currentIndex]);
        audio.play();
        updatePlayBtn(true);
        recordPlay(playlist[currentIndex].id);
    }

    function nextSong() {
        if (isShuffle) {
            let nextIndex = Math.floor(Math.random() * playlist.length);
            // Avoid repeating same song if possible, unless only 1 song
            if (playlist.length > 1 && nextIndex === currentIndex) {
                nextIndex = (nextIndex + 1) % playlist.length;
            }
            currentIndex = nextIndex;
        } else {
            if (currentIndex < playlist.length - 1) {
                currentIndex++;
            } else if (isRepeat) {
                currentIndex = 0; // Wrap around
            } else {
                return; // End of playlist
            }
        }

        loadSong(playlist[currentIndex]);
        audio.play();
        updatePlayBtn(true);
        recordPlay(playlist[currentIndex].id);
    }

    function toggleShuffle() {
        isShuffle = !isShuffle;
        shuffleBtn.classList.toggle('text-accent', isShuffle);
        shuffleBtn.classList.toggle('text-dark-300', !isShuffle);
    }

    function toggleRepeat() {
        isRepeat = !isRepeat;
        repeatBtn.classList.toggle('text-accent', isRepeat);
        repeatBtn.classList.toggle('text-dark-300', !isRepeat);
    }

    function toggleMute() {
        if (audio.muted) {
            audio.muted = false;
            volumeBar.value = previousVolume;
            muteBtn.innerHTML = '<i class="bi bi-volume-up"></i>';
            audio.volume = previousVolume; // Ensure volume is restored
        } else {
            previousVolume = volumeBar.value;
            audio.muted = true;
            volumeBar.value = 0;
            muteBtn.innerHTML = '<i class="bi bi-volume-mute"></i>';
            audio.volume = 0; // Ensure volume is set to 0
        }
    }

    function seekAudio() {
        audio.currentTime = seekBar.value;
    }
    
    function updateSeekUI() {
        // Optional: Update displayed current time while dragging
    }

    function setVolume() {
        audio.volume = volumeBar.value;
        if (audio.volume > 0 && audio.muted) {
            audio.muted = false;
             muteBtn.innerHTML = '<i class="bi bi-volume-up"></i>';
        } else if (audio.volume == 0) {
            audio.muted = true;
            muteBtn.innerHTML = '<i class="bi bi-volume-mute"></i>';
        } else if (audio.volume > 0 && !audio.muted) {
            muteBtn.innerHTML = '<i class="bi bi-volume-up"></i>';
        }
    }

    // Audio Event Listeners
    audio.addEventListener('timeupdate', () => {
        seekBar.value = audio.currentTime;
        currentTimeEl.innerText = formatTime(audio.currentTime);
    });

    audio.addEventListener('loadedmetadata', () => {
        seekBar.max = audio.duration;
        durationEl.innerText = formatTime(audio.duration);
    });

    audio.addEventListener('ended', () => {
        nextSong();
    });

    function formatTime(seconds) {
        const min = Math.floor(seconds / 60);
        const sec = Math.floor(seconds % 60);
        return `${min}:${sec < 10 ? '0' : ''}${sec}`;
    }

    // Expose playSong to global scope so card clicks can trigger it
    // CAUTION: This overrides the default navigation link behavior if we want seamless play.
    // However, user asked for clickable cards to go to show page in step 1146.
    // "Lagu di playlist seharusnya ke beranda lagunya".
    // "Down add currently playing song so we can do previous or next".
    // So the persistent player is FOR control, but cards might still navigate.
    // If we want the player to persist, we CANNOT navigate.
    // But this is MPA.
    // I will stick to: Player is present. If you play from player, it plays.
</script>
