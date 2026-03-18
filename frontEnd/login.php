<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log In - Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=1000">
    <link rel="stylesheet" href="/midtermProject/assets/login.css?v=1000">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>
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

    <!-- Background -->
    <img src="../homepage.jpg" alt="Homepage Image" class="hero-image">

    <!-- Center Container -->
    <div class="login-container">
        <div class="login-card">

            <!-- Logo Above Title -->
            <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image">

            <h1>Welcome Back</h1>
            <p class="login-subtext">Access your playlists and discover new sounds.</p>

            <?php
        if (isset($_GET['error'])) {
            $msg = '';
            switch ($_GET['error']) {
                case 'exists':
                    $msg = 'Account already exists. Please log in.';
                    break;
                case 'invalid':
                    $msg = 'Invalid email or password.';
                    break;
                case 'missing':
                    $msg = 'Please enter both email and password.';
                    break;
            }
            if ($msg) {
                echo "<p style=\"color:red; text-align:center;\">$msg</p>";
            }
        }
    ?>
    <form action="login_process.php" method="POST">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <p class="forgot-link">
            <a href="forgot_password.php">Forgot Password?</a>
            </p>

                <button type="submit" class="login-btn">Log In</button>
            </form>

            <p class="signup-link">
                Don’t have an account?
                <a href="signup.php">Sign up here</a>
            </p>

        </div>
    </div>

</body>
</html>