<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$playlist_id = $_GET['id'] ?? null;

if (!$playlist_id) {
    die("No playlist selected");
}

/* GET PLAYLIST NAME */
$stmt = $conn->prepare("SELECT name FROM playlists WHERE id = ?");
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$playlist = $stmt->get_result()->fetch_assoc();

/* GET SONGS */
$sql = "
SELECT songs.title, songs.artist
FROM playlist_songs
JOIN songs ON playlist_songs.song_id = songs.id
WHERE playlist_songs.playlist_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$songs = $stmt->get_result();
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
        <?php if ($songs->num_rows > 0): ?>

        <div class="playlist-container">

            <!-- HEADER -->
            <div class="playlist-header">
                <div>#</div>
                <div>Title</div>
                <div>Artist</div>
            </div>

            <?php $count = 1; ?>
            <?php while($song = $songs->fetch_assoc()): ?>

                <div class="playlist-row">
                    <div class="col-num"><?php echo $count++; ?></div>
                    <div class="col-title"><?php echo htmlspecialchars($song['title']); ?></div>
                    <div class="col-artist"><?php echo htmlspecialchars($song['artist']); ?></div>
                </div>

            <?php endwhile; ?>

        </div>

        <?php else: ?>

        <p class="empty">No songs in this playlist.</p>

        <?php endif; ?>

    </main>

</div>

</body>
</html>