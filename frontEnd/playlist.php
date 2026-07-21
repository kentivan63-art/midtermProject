<?php
require_once("../config/session.php");
requireLogin();

require_once("../config/db.php");

$userID = getCurrentUserID();
$playlistID = (int)($_GET['id'] ?? 0);

if (!$playlistID) {
    die("No playlist selected");
}

/* GET PLAYLIST NAME */
$stmt = $conn->prepare("SELECT name FROM playlists WHERE playlistID = ? AND userID = ?");
$stmt->bind_param("ii", $playlistID, $userID);
$stmt->execute();
$playlist = $stmt->get_result()->fetch_assoc();

if (!$playlist) {
    die("Playlist not found or access denied");
}

/* GET SONGS */
$stmt = $conn->prepare("
    SELECT songs.songID, songs.title, songs.artist, songs.file_path
    FROM playlist_songs
    JOIN songs ON playlist_songs.songID = songs.songID
    WHERE playlist_songs.playlistID = ?
    ORDER BY playlist_songs.added_at ASC
");
$stmt->bind_param("i", $playlistID);
$stmt->execute();
$songs = $stmt->get_result();

// Convert songs to array for JavaScript
$songsArray = [];
while($song = $songs->fetch_assoc()){
    $songsArray[] = $song;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($playlist['name']); ?> - Groovify</title>
<link rel="stylesheet" href="../assets/dashboard.css?v=3">
<link rel="stylesheet" href="../assets/library.css?v=3">
<link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
<style>
/* Keep small overrides for playlist */
.row.item { cursor:pointer; }
.row.item.playing { background: rgba(29,185,84,0.1); }
.playdot { cursor:pointer; }
</style>
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <a href="dashboard.php"><img src="../logotransparent.png" class="brand-logo" alt="Groovify"></a>
            <div class="brand-name">Groovify</div>
        </div>
        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item active" href="library.php">Library</a>
            <a class="nav-item" href="updates.php">Update Version</a>
            <?php if (isAdmin()): ?>
            <a class="nav-item" href="admin_updates.php">Admin</a>
            <?php endif; ?>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>
        <div class="spacer"></div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size:22px;font-weight:800;"><?php echo htmlspecialchars($playlist['name']); ?></h2>
        </header>

        <!-- SONG LIST -->
        <?php if (count($songsArray) > 0): ?>

        <div class="playlist-container">

            <!-- HEADER -->
            <div class="playlist-header">
                <div>#</div>
                <div>Title</div>
                <div>Artist</div>
            </div>

            <?php $count = 1; ?>
            <?php foreach($songsArray as $song): ?>

                <div class="playlist-row row item" 
                     style="cursor: pointer;" 
                     data-index="<?php echo $count - 1; ?>"
                     data-title="<?php echo htmlspecialchars($song['title'], ENT_QUOTES); ?>"
                     data-artist="<?php echo htmlspecialchars($song['artist'], ENT_QUOTES); ?>"
                     data-file="<?php echo htmlspecialchars($song['file_path'], ENT_QUOTES); ?>"
                     data-songid="<?php echo $song['songID']; ?>">
                    <div class="col-num"><?php echo $count++; ?></div>
                    <div class="col-title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="col-artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                    <div class="col-action"><span class="playdot">▶</span></div>
                </div>

            <?php endforeach; ?>
            
            <?php else: ?>
                <div style="padding:16px; color:rgba(255,255,255,.65);">
                    No songs in this playlist yet.
                </div>
            <?php endif; ?>

    <!-- FOOTER PLAYER -->
    <footer class="player">
        <div class="now">
            <div class="cover"></div>
            <div class="meta">
                <div class="title" id="npTitle">Select a track</div>
                <div class="artist" id="npArtist">—</div>
            </div>
        </div>

        <div class="controls">
            <button class="icon" id="btnPlayPause">▶</button>
            <div class="time">
                <span id="curTime">0:00</span>
                <div class="bar" id="seekBar"><div class="fill" id="seekFill"></div></div>
                <span id="durTime">0:00</span>
            </div>
        </div>

        <div class="right">
            <span>🔊</span>
            <input id="vol" type="range" min="0" max="1" step="0.01" value="0.8">
        </div>

        <audio id="audioPlayer"></audio>
    </footer>

</div>

<script>
console.log("=== PLAYLIST.PHP SCRIPT LOADED ===");

const rows = document.querySelectorAll(".row.item");
const player = document.getElementById("audioPlayer");
const npTitle = document.getElementById("npTitle");
const npArtist = document.getElementById("npArtist");
const btnPlayPause = document.getElementById("btnPlayPause");
const curTime = document.getElementById("curTime");
const durTime = document.getElementById("durTime");
const seekBar = document.getElementById("seekBar");
const seekFill = document.getElementById("seekFill");
const vol = document.getElementById("vol");

console.log("Elements found:", {
    rows: rows.length,
    player: !!player,
    npTitle: !!npTitle,
    btnPlayPause: !!btnPlayPause
});

let playlist = Array.from(rows).map(r=>({
    title: r.dataset.title,
    artist: r.dataset.artist,
    file_path: r.dataset.file,
    songID: r.dataset.songid
}));

console.log("Playlist loaded:", playlist);
let currentIndex = -1;
let currentRow = null;

function fmtTime(s){ if(!isFinite(s)) return "0:00"; const m=Math.floor(s/60); const r=Math.floor(s%60); return `${m}:${String(r).padStart(2,"0")}`; }
function updateRowIcons(){
    rows.forEach((r,i)=>r.querySelector(".playdot").textContent = i===currentIndex&&!player.paused?"⏸":"▶");
    btnPlayPause.textContent = player.paused?"⏯":"⏸";
}
function playAtIndex(i){
    console.log("=== playAtIndex CALLED ===");
    console.log("Index:", i);
    console.log("Playlist length:", playlist.length);
    
    if(!playlist.length) {
        console.log("❌ Playlist is empty, cannot play");
        return;
    }
    if(i < 0 || i >= playlist.length) {
        console.log("❌ Invalid index:", i);
        return;
    }
    
    currentIndex=i;
    const track = playlist[i];
    console.log("Playing track:", track);
    console.log("File path:", track.file_path);
    
    player.src = track.file_path;
    console.log("Player src set to:", player.src);
    
    player.play().then(() => {
        console.log("✅ Audio playing successfully");
    }).catch(error => {
        console.error("❌ Audio play error:", error);
        console.error("Error details:", {
            name: error.name,
            message: error.message
        });
    });
    
    npTitle.textContent=track.title;
    npArtist.textContent=track.artist;
    
    // Record listening history
    recordListeningHistory(track.songID);
    
    if(currentRow) currentRow.classList.remove("playing");
    currentRow=rows[i]; 
    if(currentRow) currentRow.classList.add("playing");
    updateRowIcons();
}

function recordListeningHistory(songID) {
    fetch("track_listen.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `songID=${songID}`,
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("Listening history recorded:", data);
        } else {
            console.error("Failed to record listening history:", data.error);
        }
    })
    .catch(err => console.error("Error recording listening history:", err));
}

rows.forEach((r,i)=>{
    r.addEventListener("click",()=>playAtIndex(i));
    const playdot = r.querySelector(".playdot");
    if(playdot) {
        playdot.addEventListener("click",e=>{ e.stopPropagation(); playAtIndex(i); });
    }
});

if(btnPlayPause) {
    btnPlayPause.addEventListener("click",()=>{
        if(!player.src && playlist.length) playAtIndex(0);
        else if(player.paused) player.play();
        else player.pause();
    });
}

player.addEventListener("play",updateRowIcons);
player.addEventListener("pause",updateRowIcons);
player.addEventListener("ended",()=>playAtIndex(currentIndex+1));
if(vol) {
    vol.addEventListener("input",()=>player.volume=vol.value);
}
player.addEventListener("timeupdate",()=>{
    if(curTime) curTime.textContent=fmtTime(player.currentTime);
    if(durTime) durTime.textContent=fmtTime(player.duration);
    if(seekFill) seekFill.style.width=((player.currentTime/player.duration)*100||0)+"%";
});
if(seekBar) {
    seekBar.addEventListener("click",(e)=>{
        const rect=seekBar.getBoundingClientRect();
        const pct=(e.clientX-rect.left)/rect.width;
        player.currentTime=pct*player.duration;
    });
}
</script>

</body>
</html>