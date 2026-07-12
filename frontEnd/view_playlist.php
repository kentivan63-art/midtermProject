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
$playlistID = $_GET["id"] ?? 0;

// GET PLAYLIST INFO
$stmt = $conn->prepare("SELECT playlistID, name FROM playlists WHERE playlistID = ? AND userID = ?");
$stmt->bind_param("ii", $playlistID, $userID);
$stmt->execute();
$playlistResult = $stmt->get_result();

if ($playlistResult->num_rows === 0) {
    header("Location: library.php");
    exit;
}

$playlist = $playlistResult->fetch_assoc();

// GET SONGS IN PLAYLIST
$stmtSongs = $conn->prepare("
    SELECT songs.songID, songs.title, songs.artist, songs.file_path
    FROM playlist_songs
    JOIN songs ON playlist_songs.songID = songs.songID
    WHERE playlist_songs.playlistID = ?
    ORDER BY playlist_songs.added_at DESC
");
$stmtSongs->bind_param("i", $playlistID);
$stmtSongs->execute();
$songs = $stmtSongs->get_result();

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
    <title><?php echo htmlspecialchars($playlist["name"]); ?> - Groovify</title>
    <link rel="stylesheet" href="../assets/dashboard.css?v=3">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
</head>
<body>

<script>
const playlistSongs = <?= json_encode($songsArray); ?>;

function playSong(songID, title, artist, filePath) {
    // Record listening history
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
            // Play the song (you can add audio player logic here)
            alert(`Now playing: ${title} by ${artist}`);
        } else {
            console.error("Failed to record listening history:", data.error);
        }
    })
    .catch(err => console.error("Error recording listening history:", err));
}
</script>
<body>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../logotransparent.png" class="brand-logo" alt="Groovify">
            <div class="brand-name">Groovify</div>
        </div>

        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item active" href="library.php">Library</a>
        </nav>

        <div class="spacer"></div>
        <a class="logout" href="logout.php">Log out</a>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="panel-title"><?php echo htmlspecialchars($playlist["name"]); ?></div>
        </header>

        <section class="panel">
            <div class="panel-title" style="padding:14px 14px 0;">Songs</div>

            <?php if (count($songsArray) > 0): ?>
                <div class="table">
                    <div class="row head">
                        <div class="col-num">#</div>
                        <div class="col-track">Track</div>
                        <div class="col-artist">Artist</div>
                        <div class="col-action">Play</div>
                    </div>

                    <?php $count = 1; ?>
                    <?php foreach($songsArray as $song): ?>
                        <div class="row item" style="cursor: pointer;" onclick="playSong(<?php echo $song['songID']; ?>, '<?php echo htmlspecialchars($song['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($song['artist'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($song['file_path'], ENT_QUOTES); ?>')">
                            <div class="col-num"><?php echo $count++; ?></div>
                            <div class="col-track"><?php echo htmlspecialchars($song["title"]); ?></div>
                            <div class="col-artist"><?php echo htmlspecialchars($song["artist"]); ?></div>
                            <div class="col-action">▶</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="padding:16px; color:rgba(255,255,255,.65);">
                    No songs in this playlist yet.
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

</body>
</html>