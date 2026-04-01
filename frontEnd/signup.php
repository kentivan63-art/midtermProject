<!DOCTYPE html>
<html>
<head>
    <title>Sign up - Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=1000">
    <link rel="stylesheet" href="/midtermProject/assets/signup.css?v=999">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>
<?php session_start(); ?>

<nav class="navbar">
    <img id="logo" src="../groovifytextlogo.png" alt="GroovifyText Logo">
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout_process.php">Log out</a></li>
        <?php else: ?>
            <li><a href="login.php">Log in</a></li>
        <?php endif; ?>
    </ul>
</nav>

<img src="../homepage.jpg" alt="Homepage Image" class="hero-image">
<div class="login-container" style="position:relative; min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="login-card">

    <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image" style="width: 80px; height: 80px; margin-bottom: 18px;">

    <h1>Create Account</h1>
    <p class="login-subtext">Sign up to explore unlimited music and curated playlists.</p>
    <form action="signup_process.php" method="POST">
        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="login-btn">Submit</button>
    </form>


        <div class="divider">
        <span>OR</span>
    </div>

 
    <p class="signup-link">
        Already have an account?
        <a href="login.php">Log in</a>
    </p>
</div>
</body>
</html>