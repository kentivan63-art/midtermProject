<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Groovify</title>

    <!-- Your existing styles -->
    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=1000">
    <link rel="stylesheet" href="/midtermProject/assets/login.css?v=1000">

    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
    <img id="logo" src="../groovifytextlogo.png" alt="GroovifyText Logo">
</nav>

<!-- Background -->
<img src="../homepage.jpg" alt="Homepage Image" class="hero-image">

<!-- Center Container -->
<div class="login-container">
    <div class="login-card">

        <!-- Logo -->
        <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image">

        <h1>Reset Password</h1>
        <p class="login-subtext">Enter your reset code and new password.</p>

        <!-- FORM -->
        <form action="process_reset.php" method="POST">

            <div class="input-group">
                <label>Reset Code</label>
                <input type="text" name="token" placeholder="Enter code" required>
            </div>

            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="password" placeholder="New password" required>
            </div>

            <button type="submit" class="login-btn">Reset Password</button>

        </form>

        <p class="signup-link">
            Back to <a href="login.php">Login</a>
        </p>

    </div>
</div>

</body>
</html>