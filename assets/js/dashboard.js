const API = "/midtermProject/frontEnd/search_songs.php";

console.log("PLAYLISTS:", USER_PLAYLISTS);

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

/* UPDATE PLAY BUTTON ICONS */
function updateRowIcons() {
  results.querySelectorAll(".playdot").forEach((dot, idx) => {
    if (idx === currentIndex && !player.paused) dot.textContent = "⏸";
    else dot.textContent = "▶";
  });

  btnPlayPause.textContent = player.paused ? "⏯" : "⏸";
}

/* PLAY TRACK */
function playAtIndex(i) {
  if (!playlist.length) return;

  if (i < 0) i = playlist.length - 1;
  if (i >= playlist.length) i = 0;

  currentIndex = i;
  const track = playlist[i];

  player.src = track.file_path;
  player.play().catch(() => {});

  npTitle.textContent = track.title;
  npArtist.textContent = track.artist;

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
      <div class="col-action">
        <span class="playdot">▶</span>
      </div>
      <div class="col-menu">
        <div class="menu-wrapper">
          <button class="menu-btn">⋮</button>
          <div class="menu-dropdown">
            <div class="menu-item add-playlist-btn">➕ Add to Playlist</div>
            <div class="submenu">
              ${USER_PLAYLISTS.map(pl => `
                <div class="submenu-item"
                     data-song="${track.id}"
                     data-playlist="${pl.id}">
                  ${pl.name}
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      </div>
    `;

    // Row click: toggle if same, play new if different
    row.addEventListener("click", (e) => {
      const i = parseInt(row.dataset.index);
      if (i === currentIndex) {
        if (player.paused) player.play();
        else player.pause();
      } else {
        playAtIndex(i);
      }
    });

    // Row playdot click also toggles same behavior
    row.querySelector(".playdot").addEventListener("click", (e) => {
      e.stopPropagation();
      const i = parseInt(row.dataset.index);
      if (i === currentIndex) {
        if (player.paused) player.play();
        else player.pause();
      } else {
        playAtIndex(i);
      }
    });

    // Menu toggle
    const menuBtn = row.querySelector(".menu-btn");
    const dropdown = row.querySelector(".menu-dropdown");
    const addBtn = row.querySelector(".add-playlist-btn");
    const submenu = row.querySelector(".submenu");

    menuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      document.querySelectorAll(".menu-dropdown").forEach(m => { if (m !== dropdown) m.style.display = "none"; });
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    });

    addBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      submenu.style.display = submenu.style.display === "block" ? "none" : "block";
    });

    row.querySelectorAll(".submenu-item").forEach(item => {
      item.addEventListener("click", (e) => {
        e.stopPropagation();
        addToPlaylist(item.dataset.song, item.dataset.playlist);
      });
    });

    results.appendChild(row);
  });

  updateRowIcons();
}

/* ADD TO PLAYLIST */
function addToPlaylist(songId, playlistId) {
  fetch("add_to_playlist.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `song_id=${songId}&playlist_id=${playlistId}`
  })
  .then(res => res.text())
  .then(data => console.log("Server:", data))
  .catch(err => console.error(err));
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
qInput.addEventListener("keydown", (e) => { if (e.key === "Enter") loadSongs(qInput.value); });

/* PLAYER BUTTON */
btnPlayPause.addEventListener("click", () => {
  if (!player.src) {
    if (playlist.length) playAtIndex(0);
    return;
  }

  if (player.paused) player.play();
  else player.pause();
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

/* CLOSE MENU */
document.addEventListener("click", () => {
  document.querySelectorAll(".menu-dropdown").forEach(m => { m.style.display = "none"; });
});

/* INIT */
loadSongs();