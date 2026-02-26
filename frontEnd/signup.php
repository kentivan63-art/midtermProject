<!DOCTYPE html>
<html>
<head>
    <title>Sign up - Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/signup.css?v=999">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>

<img src="../homepage.jpg" alt="Homepage Image" class="hero-image">
<div class="login-container" style="position:relative; min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="login-card">

    <img src="../logotransparent.png" alt="Groovify Logo" class="logo-image" style="width: 80px; height: 80px; margin-bottom: 18px;">

    <h1>Create Account</h1>
    <p class="login-subtext">Sign up to explore unlimited music and curated playlists.</p>

    <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <form action="signup_process.php" method="POST">
        <button type="submit" class="login-btn">Submit</button>
    </form>


        <div class="divider">
        <span>OR</span>
    </div>

    <!-- GOOGLE SIGN UP -->
    <a href="#" class="google-btn">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google logo">
        Continue with Google
    </a>

    <p class="signup-link">
        Already have an account?
        <a href="login.php">Log in</a>
    </p>
</div>
</body>
</html>