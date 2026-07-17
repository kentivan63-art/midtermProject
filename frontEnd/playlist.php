<?php
session_start();
// Session timeout handling
$timeout = 300; // 5 minutes
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=timeout");
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once("../config/db.php");

if (!isset($_SESSION["userID"])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION["userID"];
$playlistID = (int)($_GET['id'] ?? 0);

if (!$playlistID) {
    die("No playlist selected");
}

/* GET PLAYLIST NAME */
$stmt = $conn->prepare("SELECT name FROM playlists WHERE playlistID = ?");
$stmt->bind_param("i", $playlistID);
$stmt->execute();
$playlist = $stmt->get_result()->fetch_assoc();

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
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>
        <div class="spacer"></div>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size:22px;font-weight:800;"><?php echo htmlspecialchars($playlist['name']); ?></h2>
        </header>

        <div class="hint" id="statusText"><?php echo count($songsArray); ?> track(s)</div>

        <!-- SONG LIST -->
        <section class="panel">
            <div class="table">
                <div class="row head">
                    <div class="col-num">#</div>
                    <div class="col-track">Track</div>
                    <div class="col-artist">Artist</div>
                    <div class="col-action"></div>
                </div>

                <?php if (count($songsArray) > 0): ?>
                    <?php $count = 1; ?>
                    <?php foreach($songsArray as $song): ?>
                        <div class="row item" data-index="<?php echo $count - 1; ?>" data-title="<?php echo htmlspecialchars($song['title'], ENT_QUOTES); ?>" data-artist="<?php echo htmlspecialchars($song['artist'], ENT_QUOTES); ?>" data-file="<?php echo htmlspecialchars($song['file_path'], ENT_QUOTES); ?>" data-songid="<?php echo $song['songID']; ?>">
                            <div class="col-num"><?php echo $count++; ?></div>
                            <div class="col-track"><?php echo htmlspecialchars($song['title']); ?></div>
                            <div class="col-artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                            <div class="col-action"><span class="playdot">▶</span></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding:14px; color:rgba(255,255,255,0.6)">No tracks in this playlist.</div>
                <?php endif; ?>
            </div>
        </section>

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
const statusText = document.getElementById("statusText");

let playlist = Array.from(rows).map(r=>({
    title: r.dataset.title,
    artist: r.dataset.artist,
    file_path: r.dataset.file,
    songID: r.dataset.songid
}));
let currentIndex = -1;
let currentRow = null;

function fmtTime(s){ if(!isFinite(s)) return "0:00"; const m=Math.floor(s/60); const r=Math.floor(s%60); return `${m}:${String(r).padStart(2,"0")}`; }

function updateRowIcons(){
    rows.forEach((r,i)=>r.querySelector(".playdot").textContent = i===currentIndex&&!player.paused?"⏸":"▶");
    btnPlayPause.textContent = player.paused?"⏯":"⏸";
}

function recordListeningHistory(songId) {
    fetch("track_listen.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `songID=${songId}`,
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

function playAtIndex(i){
    if(!playlist.length) return;
    currentIndex=i;
    const track = playlist[i];
    player.src = track.file_path;
    player.play().catch(()=>{});
    npTitle.textContent=track.title;
    npArtist.textContent=track.artist;
    
    // Record listening history
    recordListeningHistory(track.songID);
    
    if(currentRow) currentRow.classList.remove("playing");
    currentRow=rows[i]; currentRow.classList.add("playing");
    updateRowIcons();
    statusText.textContent = "Now playing: " + track.title;
}

rows.forEach((r,i)=>{
    r.addEventListener("click",()=>playAtIndex(i));
    r.querySelector(".playdot").addEventListener("click",e=>{ e.stopPropagation(); playAtIndex(i); });
});

btnPlayPause.addEventListener("click",()=>{
    if(!player.src && playlist.length) playAtIndex(0);
    else if(player.paused) player.play();
    else player.pause();
});

player.addEventListener("play",updateRowIcons);
player.addEventListener("pause",updateRowIcons);
player.addEventListener("ended",()=>playAtIndex(currentIndex+1));
vol.addEventListener("input",()=>player.volume=vol.value);
player.addEventListener("timeupdate",()=>{
    curTime.textContent=fmtTime(player.currentTime);
    durTime.textContent=fmtTime(player.duration);
    seekFill.style.width=((player.currentTime/player.duration)*100||0)+"%";
});
seekBar.addEventListener("click",(e)=>{
    const rect=seekBar.getBoundingClientRect();
    const pct=(e.clientX-rect.left)/rect.width;
    player.currentTime=pct*player.duration;
});
</script>

</body>
</html>