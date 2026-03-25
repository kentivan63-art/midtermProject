<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION["user_id"];

// Create playlist
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["playlist_name"])) {
    $playlistName = trim($_POST["playlist_name"]);

    if (!empty($playlistName)) {
        $stmt = $conn->prepare("INSERT INTO playlists (userID, name) VALUES (?, ?)");
        $stmt->bind_param("is", $userID, $playlistName);
        $stmt->execute();
        $stmt->close();

        header("Location: library.php");
        exit;
    }
}

// Get user's playlists
$stmt = $conn->prepare("SELECT id, name, created_at FROM playlists WHERE userID = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Groovify</title>
    <link rel="stylesheet" href="../assets/dashboard.css?v=3">
    <link rel="stylesheet" href="../assets/library.css?v=3">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <img src="../logotransparent.png" class="brand-logo" alt="Groovify">
            <div class="brand-name">Groovify</div>
        </div>

        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item active" href="library.php">Library</a>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>

        <div class="spacer"></div>
        <a class="logout" href="logout.php">Log out</a>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size: 22px; font-weight: 800;">Your Library</h2>
        </header>

        <!-- CREATE PLAYLIST -->
        <section class="panel" style="padding: 18px; margin-bottom: 20px;">
            <div class="panel-title">Create Playlist</div>

            <form method="POST" action="library.php" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
                <input 
                    type="text" 
                    name="playlist_name" 
                    placeholder="Enter playlist name"
                    required
                    style="
                        flex:1;
                        min-width:220px;
                        padding:12px 14px;
                        border-radius:10px;
                        border:1px solid rgba(255,255,255,.1);
                        background:rgba(255,255,255,.05);
                        color:white;
                        outline:none;
                    "
                >
                <button 
                    type="submit"
                    style="
                        padding:12px 18px;
                        border:none;
                        border-radius:10px;
                        background:#1db954;
                        color:white;
                        font-weight:700;
                        cursor:pointer;
                    "
                >
                    Create
                </button>
            </form>
        </section>

        <!-- PLAYLISTS -->
        <section class="panel">
            <div class="panel-title" style="padding: 14px 14px 0;">My Playlists</div>

            <?php if ($result->num_rows > 0): ?>
                <div class="table">
                    <div class="row head">
                        <div class="col-num">#</div>
                        <div class="col-track">Playlist Name</div>
                        <div class="col-artist">Created</div>
                        <div class="col-action"></div>
                    </div>

                    <?php $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="row item">
                            <div class="col-num"><?php echo $count++; ?></div>
                            <div class="col-track">
                            <a href="playlist.php?id=<?php echo $row['id']; ?>" class="playlist-link">
                            <?php echo htmlspecialchars($row["name"]); ?>
                            </a>
                            </div>
                            <div class="col-artist"><?php echo htmlspecialchars($row["created_at"]); ?></div>
                            <div class="col-action"></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="padding: 16px; color: rgba(255,255,255,.65);">
                    No playlists yet.
                </div>
            <?php endif; ?>
        </section>
    </main>

</div>

</body>
</html><?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION["user_id"];

// Create playlist
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["playlist_name"])) {
    $playlistName = trim($_POST["playlist_name"]);

    if (!empty($playlistName)) {
        $stmt = $conn->prepare("INSERT INTO playlists (userID, name) VALUES (?, ?)");
        $stmt->bind_param("is", $userID, $playlistName);
        $stmt->execute();
        $stmt->close();

        header("Location: library.php");
        exit;
    }
}

// Get user's playlists
$stmt = $conn->prepare("SELECT id, name, created_at FROM playlists WHERE userID = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Groovify</title>
    <link rel="stylesheet" href="../assets/dashboard.css?v=3">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <img src="../logotransparent.png" class="brand-logo" alt="Groovify">
            <div class="brand-name">Groovify</div>
        </div>

        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item active" href="library.php">Library</a>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>

        <div class="spacer"></div>
        <a class="logout" href="logout.php">Log out</a>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size: 22px; font-weight: 800;">Your Library</h2>
        </header>

        <!-- CREATE PLAYLIST -->
        <section class="panel" style="padding: 18px; margin-bottom: 20px;">
            <div class="panel-title">Create Playlist</div>

            <form method="POST" action="library.php" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
                <input 
                    type="text" 
                    name="playlist_name" 
                    placeholder="Enter playlist name"
                    required
                    style="
                        flex:1;
                        min-width:220px;
                        padding:12px 14px;
                        border-radius:10px;
                        border:1px solid rgba(255,255,255,.1);
                        background:rgba(255,255,255,.05);
                        color:white;
                        outline:none;
                    "
                >
                <button 
                    type="submit"
                    style="
                        padding:12px 18px;
                        border:none;
                        border-radius:10px;
                        background:#1db954;
                        color:white;
                        font-weight:700;
                        cursor:pointer;
                    "
                >
                    Create
                </button>
            </form>
        </section>

        <!-- PLAYLISTS -->
        <section class="panel">
            <div class="panel-title" style="padding: 14px 14px 0;">My Playlists</div>

            <?php if ($result->num_rows > 0): ?>
                <div class="table">
                    <div class="row head">
                        <div class="col-num">#</div>
                        <div class="col-track">Playlist Name</div>
                        <div class="col-artist">Created</div>
                        <div class="col-action"></div>
                    </div>

                    <?php $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="row item">
                            <div class="col-num"><?php echo $count++; ?></div>
                            <div class="col-track"><?php echo htmlspecialchars($row["name"]); ?></div>
                            <div class="col-artist"><?php echo htmlspecialchars($row["created_at"]); ?></div>
                            <div class="col-action"></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="padding: 16px; color: rgba(255,255,255,.65);">
                    No playlists yet.
                </div>
            <?php endif; ?>
        </section>
    </main>

</div>

</body>
</html>