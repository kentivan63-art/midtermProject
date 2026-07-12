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

$playlist_id = $_GET['id'] ?? null;

if (!$playlist_id) {
    die("No playlist selected");
}

/* GET PLAYLIST NAME */
$stmt = $conn->prepare("SELECT name FROM playlists WHERE playlistID = ?");
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$playlist = $stmt->get_result()->fetch_assoc();

/* GET SONGS */
$sql = "
SELECT songs.songID, songs.title, songs.artist, songs.file_path
FROM playlist_songs
JOIN songs ON playlist_songs.songID = songs.songID
WHERE playlist_songs.playlistID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $playlist_id);
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
<link rel="stylesheet" href="../assets/playlist.css">
<link rel="icon" href="../groovifylogo.ico">
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

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <img src="../logotransparent.png" class="brand-logo">
            <div class="brand-name">Groovify</div>
        </div>

        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item active" href="library.php">Library</a>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <!-- TITLE -->
        <header class="topbar">
            <h2 style="font-size:22px; font-weight:800;">
                <?php echo htmlspecialchars($playlist['name']); ?>
            </h2>
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

                <div class="playlist-row" style="cursor: pointer;" onclick="playSong(<?php echo $song['songID']; ?>, '<?php echo htmlspecialchars($song['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($song['artist'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($song['file_path'], ENT_QUOTES); ?>')">
                    <div class="col-num"><?php echo $count++; ?></div>
                    <div class="col-title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="col-artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                </div>

            <?php endforeach; ?>

        </div>

        <?php else: ?>

        <p class="empty">No songs in this playlist.</p>

        <?php endif; ?>

    </main>

</div>

</body>
</html>