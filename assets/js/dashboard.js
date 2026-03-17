// assets/js/dashboard.js
const API = "/midtermProject/frontEnd/search_songs.php";

const qInput = document.getElementById("jamendoQuery");
const btn = document.getElementById("jamendoBtn");
const results = document.getElementById("jamendoResults");
const statusText = document.getElementById("statusText");

const player = document.getElementById("audioPlayer");
const npTitle = document.getElementById("npTitle");
const npArtist = document.getElementById("npArtist");

const btnPlayPause = document.getElementById("btnPlayPause");
const btnPrev = document.getElementById("btnPrev");
const btnNext = document.getElementById("btnNext");

const curTime = document.getElementById("curTime");
const durTime = document.getElementById("durTime");
const seekBar = document.getElementById("seekBar");
const seekFill = document.getElementById("seekFill");
const vol = document.getElementById("vol");

let currentRow = null;

// ✅ playlist state
let playlist = [];
let currentIndex = -1;

function fmtTime(s) {
  if (!isFinite(s)) return "0:00";
  const m = Math.floor(s / 60);
  const r = Math.floor(s % 60);
  return `${m}:${String(r).padStart(2, "0")}`;
}

function setPlayingRow(row) {
  if (currentRow) currentRow.classList.remove("playing");
  currentRow = row;
  if (currentRow) currentRow.classList.add("playing");
}

function playAtIndex(i) {
  if (!playlist.length) return;
  if (i < 0) i = playlist.length - 1;
  if (i >= playlist.length) i = 0;

  currentIndex = i;
  const track = playlist[currentIndex];

  player.src = track.file_path;
  player.play().catch(() => {});

  fetch("/midtermProject/frontEnd/track_listen.php", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: "songID=" + encodeURIComponent(track.id)
})
.then(res => res.json())
.then(data => console.log("track_listen:", data))
.catch(err => console.error("track_listen error:", err));

  if (npTitle) npTitle.textContent = track.title;
  if (npArtist) npArtist.textContent = track.artist;

  // highlight row
  const row = results?.querySelector(`[data-index="${currentIndex}"]`);
  if (row) setPlayingRow(row);
}

function renderSongs(list) {
  results.innerHTML = "";

  if (!list.length) {
    results.innerHTML = `<div style="padding:14px; color:rgba(255,255,255,0.65)">No tracks found.</div>`;
    return;
  }

  list.forEach((track, i) => {
    const row = document.createElement("button");
    row.type = "button";
    row.className = "row item";
    row.dataset.index = String(i);

    row.innerHTML = `
      <div class="col-num">${i + 1}</div>
      <div class="col-track">${track.title}</div>
      <div class="col-artist">${track.artist}</div>
      <div class="col-action"><span class="playdot">▶</span></div>
    `;

    row.addEventListener("click", () => {
      playAtIndex(i);
    });

    results.appendChild(row);
  });
}

async function loadSongs(query = "") {
  if (statusText) statusText.textContent = query ? "Searching…" : "Loading tracks…";

  const url = query
    ? `${API}?q=${encodeURIComponent(query)}&limit=300`
    : `${API}?limit=300`;

  try {
    const res = await fetch(url);
    if (!res.ok) {
      if (statusText) statusText.textContent = `Error (${res.status})`;
      return;
    }

    const data = await res.json();
    playlist = data.results || [];
    renderSongs(playlist);

    if (statusText) statusText.textContent = `Showing ${playlist.length} track(s)`;

    // keep playing track highlighted if it's still in list
    if (currentIndex >= 0 && currentIndex < playlist.length) {
      const row = results?.querySelector(`[data-index="${currentIndex}"]`);
      if (row) setPlayingRow(row);
    } else {
      currentIndex = -1;
      currentRow = null;
    }
  } catch (e) {
    console.error(e);
    if (statusText) statusText.textContent = "Error loading tracks";
  }
}

function doSearch() {
  const q = (qInput?.value || "").trim();
  loadSongs(q);
}

// Search
btn?.addEventListener("click", doSearch);
qInput?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") doSearch();
});

document.addEventListener("DOMContentLoaded", () => {
  if (vol) player.volume = parseFloat(vol.value || "0.8");
  loadSongs("");
});

// ✅ Prev / Next / PlayPause
btnPlayPause?.addEventListener("click", () => {
  if (!player.src) {
    // if nothing selected, start first track
    if (playlist.length) playAtIndex(0);
    return;
  }
  if (player.paused) player.play().catch(() => {});
  else player.pause();
});

btnPrev?.addEventListener("click", () => {
  if (!playlist.length) return;
  if (currentIndex === -1) return playAtIndex(0);
  playAtIndex(currentIndex - 1);
});

btnNext?.addEventListener("click", () => {
  if (!playlist.length) return;
  if (currentIndex === -1) return playAtIndex(0);
  playAtIndex(currentIndex + 1);
});

// volume
vol?.addEventListener("input", () => {
  player.volume = parseFloat(vol.value);
});

// progress
player.addEventListener("timeupdate", () => {
  if (curTime) curTime.textContent = fmtTime(player.currentTime);
  if (durTime) durTime.textContent = fmtTime(player.duration);

  if (seekFill) {
    const pct = (player.duration ? (player.currentTime / player.duration) : 0) * 100;
    seekFill.style.width = pct + "%";
  }
});

// seek click
seekBar?.addEventListener("click", (e) => {
  if (!player.duration) return;
  const rect = seekBar.getBoundingClientRect();
  const pct = (e.clientX - rect.left) / rect.width;
  player.currentTime = Math.max(0, Math.min(1, pct)) * player.duration;
});

// ✅ auto-next when song ends
player.addEventListener("ended", () => {
  if (!playlist.length) return;
  if (currentIndex === -1) return;
  playAtIndex(currentIndex + 1);
});