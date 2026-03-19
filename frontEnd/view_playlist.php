<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION["user_id"];
$playlistID = $_GET["id"] ?? 0;

// GET PLAYLIST INFO
$stmt = $conn->prepare("SELECT id, name FROM playlists WHERE id = ? AND userID = ?");
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
    SELECT songs.id, songs.title, songs.artist, songs.file_path
    FROM playlist_songs
    JOIN songs ON playlist_songs.songID = songs.id
    WHERE playlist_songs.playlistID = ?
    ORDER BY playlist_songs.added_at DESC
");
$stmtSongs->bind_param("i", $playlistID);
$stmtSongs->execute();
$songs = $stmtSongs->get_result();
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

            <?php if ($songs->num_rows > 0): ?>
                <div class="table">
                    <div class="row head">
                        <div class="col-num">#</div>
                        <div class="col-track">Track</div>
                        <div class="col-artist">Artist</div>
                        <div class="col-action">Play</div>
                    </div>

                    <?php $count = 1; ?>
                    <?php while ($song = $songs->fetch_assoc()): ?>
                        <div class="row item">
                            <div class="col-num"><?php echo $count++; ?></div>
                            <div class="col-track"><?php echo htmlspecialchars($song["title"]); ?></div>
                            <div class="col-artist"><?php echo htmlspecialchars($song["artist"]); ?></div>
                            <div class="col-action">▶</div>
                        </div>
                    <?php endwhile; ?>
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