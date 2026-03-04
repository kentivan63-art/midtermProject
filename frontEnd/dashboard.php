<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Groovify</title>

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
  <a class="nav-item active" href="dashboard.php"><span class="ico"></span> Home</a>
  <a class="nav-item" href="library.php"><span class="ico"></span> Library</a>
  <a class="nav-item" href="logout_process.php"><span class="ico"></span> Log out</a>
</nav>

    <div class="spacer"></div>
    <a class="logout" href="logout.php">Log out</a>
  </aside>

  <!-- MAIN -->
  <main class="main" id="search">

    <header class="topbar">
      <div class="search">
        <span class="search-icon">⌕</span>
        <input
          id="jamendoQuery"
          type="text"
          placeholder="Search songs, artists, albums…"
          autocomplete="off"
        >
      </div>

      <!-- ✅ IMPORTANT: add id so JS can click it -->
      <button class="btn" id="jamendoBtn" type="button">Search</button>
    </header>

    <div class="hint" id="statusText">Loading tracks…</div>

    <!-- RESULTS PANEL -->
    <section class="panel">
      <div class="panel-title">Results</div>

      <div class="table">
        <div class="row head">
          <div class="col-num">#</div>
          <div class="col-track">Track</div>
          <div class="col-artist">Artist</div>
          <div class="col-action">Play</div>
        </div>

        <!-- JS injects rows here -->
        <div id="jamendoResults"></div>
      </div>
    </section>

  </main>

  <!-- PLAYER -->
  <footer class="player">

    <div class="now">
      <div class="cover"></div>
      <div class="meta">
        <div class="title" id="npTitle">Select a track</div>
        <div class="artist" id="npArtist">—</div>
      </div>
    </div>

    <div class="controls">
      <button class="icon" id="btnPlayPause" type="button">⏯</button>

      <div class="time">
        <span id="curTime">0:00</span>
        <div class="bar" id="seekBar">
          <div class="fill" id="seekFill"></div>
        </div>
        <span id="durTime">0:00</span>
      </div>
    </div>

    <div class="right">
      <span>🔊</span>
      <input id="vol" type="range" min="0" max="1" step="0.01" value="0.8">
    </div>

    <!-- Audio -->
    <audio id="audioPlayer"></audio>

  </footer>

</div>

<script src="../assets/js/dashboard.js?v=999"></script>
</body>
</html>