<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=1000">
    <link rel="stylesheet" href="/midtermProject/assets/forgot_password.css?v=6">
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
            <li><a href="signup.php">Sign up</a></li>
        <?php endif; ?>
    </ul>
</nav>

<img src="../homepage.jpg" alt="Homepage Image" class="hero-image">

<div class="forgot-page">
    <div class="forgot-brand">
    </div>

    <div class="forgot-container">
        <div class="forgot-card">
            <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image">

            <h2>Forgot Password</h2>
            <p class="forgot-subtext">Enter your email and we’ll send you a reset code.</p>

            <form action="process_forgot.php" method="POST">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="forgot-btn">Send Code</button>
            </form>

            <p class="back-link">
                Remember your password?
                <a href="login.php">Log in</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>