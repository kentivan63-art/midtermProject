<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
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
      <a class="nav-item" href="dashboard.php"><span class="ico"></span> Home</a>
      <a class="nav-item active" href="library.php"><span class="ico"></span> Library</a>
      <a class="nav-item" href="logout_process.php"><span class="ico"></span> Log out</a>
    </nav>

    <div class="spacer"></div>
    <a class="logout" href="logout_process.php">Log out</a>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <header class="topbar">
      <h2>My Library</h2>
    </header>

    <section class="panel">
      <div class="panel-title">Your saved songs will appear here.</div>
      <p style="padding:20px; color:#ccc;">(Library functionality not yet implemented.)</p>
    </section>
  </main>

</div>

</body>
</html>