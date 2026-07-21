const API = "/midtermProject/frontEnd/search_songs.php";

console.log("=== DASHBOARD.JS LOADED ===");
console.log("PLAYLISTS:", USER_PLAYLISTS);
console.log("Current page:", window.location.href);

// Close dropdowns when clicking outside
document.addEventListener("click", () => {
  document.querySelectorAll(".menu-dropdown").forEach(dropdown => {
    dropdown.style.display = "none";
  });
});

const qInput = document.getElementById("jamendoQuery");
const btn = document.getElementById("jamendoBtn");
const results = document.getElementById("jamendoResults");
const statusText = document.getElementById("statusText");

const player = document.getElementById("audioPlayer");
const npTitle = document.getElementById("npTitle");
const npArtist = document.getElementById("npArtist");

const btnPlayPause = document.getElementById("btnPlayPause");
const curTime = document.getElementById("curTime");
const durTime = document.getElementById("durTime");
const seekBar = document.getElementById("seekBar");
const seekFill = document.getElementById("seekFill");
const vol = document.getElementById("vol");

let currentRow = null;
let playlist = [];
let currentIndex = -1;

/* FORMAT TIME */
function fmtTime(s) {
  if (!isFinite(s)) return "0:00";
  const m = Math.floor(s / 60);
  const r = Math.floor(s % 60);
  return `${m}:${String(r).padStart(2, "0")}`;
}

/* UPDATE ICONS */
function updateRowIcons() {
  results.querySelectorAll(".playdot").forEach((dot, idx) => {
    if (idx === currentIndex && !player.paused) dot.textContent = "⏸";
    else dot.textContent = "▶";
  });

  btnPlayPause.textContent = player.paused ? "⏯" : "⏸";
}

/* PLAY */
function playAtIndex(i) {
  console.log("=== playAtIndex CALLED ===");
  console.log("Index:", i);
  console.log("Playlist length:", playlist.length);

  if (!playlist.length) {
    console.log("❌ Playlist is empty, cannot play");
    return;
  }

  currentIndex = i;
  const track = playlist[i];

  console.log("Playing track:", track);
  console.log("Song ID:", track.songID);

  player.src = track.file_path;
  player.play().catch(() => {});

  npTitle.textContent = track.title;
  npArtist.textContent = track.artist;

  // Record listening history
  console.log("About to call recordListeningHistory for songID:", track.songID);
  recordListeningHistory(track.songID);

  // Highlight row
  if (currentRow) currentRow.classList.remove("playing");
  const row = results.querySelector(`[data-index="${i}"]`);
  if (row) {
    currentRow = row;
    row.classList.add("playing");
  }

  updateRowIcons();
}

/* RENDER SONGS */
function renderSongs(list) {
  results.innerHTML = "";

  if (!list.length) {
    results.innerHTML = `<div style="padding:14px; color:rgba(255,255,255,0.6)">No tracks found.</div>`;
    return;
  }

  list.forEach((track, i) => {
    const row = document.createElement("div");
    row.className = "row item";
    row.dataset.index = i;

    row.innerHTML = `
      <div class="col-num">${i + 1}</div>
      <div class="col-track">${track.title}</div>
      <div class="col-artist">${track.artist}</div>
      <div class="col-action"><span class="playdot">▶</span></div>

      <div class="col-menu">
        <div class="menu-wrapper">
          <button class="menu-btn" title="Add to playlist">➕</button>
          <div class="menu-dropdown">
            <div class="menu-header">Add to Playlist:</div>
            <div class="playlist-list">
              ${USER_PLAYLISTS.map(pl => `
                <div class="playlist-item"
                     data-song="${track.songID}"
                     data-playlist="${pl.playlistID}"
                     title="Add to ${pl.name}">
                  ${pl.name}
                </div>
              `).join('')}
              ${USER_PLAYLISTS.length === 0 ? '<div class="no-playlists">No playlists available</div>' : ''}
            </div>
          </div>
        </div>
      </div>
    `;

    // Row click: toggle if same, play new if different
    row.addEventListener("click", (e) => {
      const i = parseInt(row.dataset.index);
      if (i === currentIndex) {
        if (player.paused) {
          player.play();
          // Record listening history when resuming the same song
          recordListeningHistory(playlist[i].songID);
        } else {
          player.pause();
        }
      } else {
        playAtIndex(i);
      }
    });

    row.querySelector(".playdot").addEventListener("click", (e) => {
      e.stopPropagation();
      const i = parseInt(row.dataset.index);
      if (i === currentIndex) {
        if (player.paused) {
          player.play();
          // Record listening history when resuming the same song
          recordListeningHistory(playlist[i].songID);
        } else {
          player.pause();
        }
      } else {
        playAtIndex(i);
      }
    });

    // Menu toggle
    const menuBtn = row.querySelector(".menu-btn");
    const dropdown = row.querySelector(".menu-dropdown");

    menuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      document.querySelectorAll(".menu-dropdown").forEach(m => {
        if (m !== dropdown) m.style.display = "none";
      });
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    // Add to playlist action
    row.querySelectorAll(".playlist-item").forEach(item => {
      item.addEventListener("mouseenter", () => {
        // Optional: Add visual feedback on hover
        item.style.background = "rgba(29,185,84,.1)";
        item.style.color = "var(--green)";
      });

      item.addEventListener("mouseleave", () => {
        // Remove visual feedback when not hovering
        item.style.background = "";
        item.style.color = "";
      });

      item.addEventListener("click", async (e) => {
        e.stopPropagation();

        const songId = item.dataset.song;
        const playlistId = item.dataset.playlist;
        const trackData = playlist.find(t => t.songID == songId);

        await fetch("add_to_playlist.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `songID=${songId}
          &playlistID=${playlistId}
          &title=${encodeURIComponent(trackData.title)}
          &artist=${encodeURIComponent(trackData.artist)}
          &file_path=${encodeURIComponent(trackData.file_path)}`
        });

        statusText.textContent = "✅ Added to playlist! Redirecting...";
        setTimeout(() => {
          window.location.href = "library.php";
        }, 500);
      });
    });

    results.appendChild(row);
  });

  updateRowIcons();
}

/* ADD TO PLAYLIST */
function addToPlaylist(songId, playlistId) {
  console.log("Adding to playlist - songID:", songId, "playlistID:", playlistId);
  
  fetch("add_to_playlist.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `songID=${songId}&playlistID=${playlistId}`,
    credentials: 'same-origin'
  })
  .then(res => res.json())
  .then(data => {
    console.log("Server response:", data);
    if (data.success) {
      console.log("✅ Successfully added to playlist");
      // Optional: Show user feedback
      // alert("Song added to playlist!");
    } else {
      console.error("❌ Failed to add to playlist:", data.error);
      // Optional: Show error to user
      // alert("Error: " + data.error);
    }
  })
  .catch(err => {
    console.error("Network error:", err);
  });
}

/* RECORD LISTENING HISTORY */
function recordListeningHistory(songId) {
  console.log("=== LISTENING HISTORY TRACKING START ===");
  console.log("Attempting to record listening history for songID:", songId);
  console.log("Current URL:", window.location.href);
  console.log("Fetch URL:", "track_listen.php");

  fetch("track_listen.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `songID=${songId}`,
    credentials: 'same-origin'
  })
  .then(res => {
    console.log("Response received. Status:", res.status);
    console.log("Response OK:", res.ok);
    console.log("Response type:", res.type);
    return res.json();
  })
  .then(data => {
    console.log("=== SERVER RESPONSE ===");
    console.log("Full response data:", data);
    if (data.success) {
      console.log("✅ SUCCESS: Listening history recorded");
      console.log("Insert ID:", data.insert_id);
      console.log("User ID:", data.userID);
      console.log("Song ID:", data.songID);
    } else {
      console.error("❌ FAILED: Server returned error");
      console.error("Error message:", data.error);
      console.error("Full error data:", data);
    }
    console.log("=== LISTENING HISTORY TRACKING END ===");
  })
  .catch(err => {
    console.error("=== FETCH ERROR ===");
    console.error("Error type:", err.name);
    console.error("Error message:", err.message);
    console.error("Full error:", err);
    console.error("=== LISTENING HISTORY TRACKING END ===");
  });
}

/* LOAD SONGS */
async function loadSongs(query = "") {
  statusText.textContent = "Loading tracks...";
  const url = query ? `${API}?q=${encodeURIComponent(query)}` : API;

  const res = await fetch(url);
  const data = await res.json();

  playlist = data.results || [];
  renderSongs(playlist);

  statusText.textContent = `Showing ${playlist.length} track(s)`;
}

/* SEARCH */
btn.addEventListener("click", () => loadSongs(qInput.value));
qInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") loadSongs(qInput.value);
});

/* PLAYER BUTTON */
btnPlayPause.addEventListener("click", () => {
  console.log("=== PLAY BUTTON CLICKED ===");
  console.log("Player src:", player.src);
  console.log("Current index:", currentIndex);
  console.log("Playlist length:", playlist.length);

  if (!player.src) {
    console.log("No player src, playing first track");
    if (playlist.length) playAtIndex(0);
    return;
  }

  if (player.paused) {
    console.log("Player was paused, resuming");
    player.play();
    // Record listening history when resuming via play button
    if (currentIndex >= 0 && playlist[currentIndex]) {
      console.log("Recording history for current track");
      recordListeningHistory(playlist[currentIndex].songID);
    }
  } else {
    console.log("Player was playing, pausing");
    player.pause();
  }
});

/* EVENTS */
player.addEventListener("play", updateRowIcons);
player.addEventListener("pause", updateRowIcons);
player.addEventListener("ended", () => playAtIndex(currentIndex + 1));

/* VOLUME */
vol.addEventListener("input", () => { player.volume = vol.value; });

/* TIME UPDATE */
player.addEventListener("timeupdate", () => {
  curTime.textContent = fmtTime(player.currentTime);
  durTime.textContent = fmtTime(player.duration);
  seekFill.style.width = ((player.currentTime / player.duration) * 100 || 0) + "%";
});

/* SEEK */
seekBar.addEventListener("click", (e) => {
  const rect = seekBar.getBoundingClientRect();
  const pct = (e.clientX - rect.left) / rect.width;
  player.currentTime = pct * player.duration;
});

/* CLOSE MENU ON CLICK OUTSIDE */
document.addEventListener("click", () => {
  document.querySelectorAll(".menu-dropdown").forEach(m => { m.style.display = "none"; });
});

/* INIT */
loadSongs();