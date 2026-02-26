<!DOCTYPE html>
<html>
<head>
    <title>Log In - Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/login.css?v=999">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>

    <!-- Background -->
    <img src="../homepage.jpg" alt="Homepage Image" class="hero-image">

    <!-- Center Container -->
    <div class="login-container">
        <div class="login-card">

            <!-- Logo Above Title -->
            <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image">

            <h1>Welcome Back</h1>
            <p class="login-subtext">Access your playlists and discover new sounds.</p>

            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

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