<?php
include("../config/db.php");

$message = "";
$color = "red";

$token = $_POST['token'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $message = "Invalid code.";
} else {
    $user = $result->fetch_assoc();

    // CHECK EXPIRY
    if (strtotime($user['token_expiry']) < time()) {
        $message = "Code expired.";
    } else {

        // UPDATE PASSWORD
        $newPass = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
        $update->bind_param("si", $newPass, $user['id']);
        $update->execute();

        $message = "Password updated successfully!";
        $color = "green";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Result - Groovify</title>

    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=1000">
    <link rel="stylesheet" href="/midtermProject/assets/login.css?v=1000">
    <link rel="icon" href="../groovifylogo.ico">
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
    <img id="logo" src="../groovifytextlogo.png" alt="Groovify Logo">
</nav>

<!-- Background -->
<img src="../homepage.jpg" class="hero-image">

<!-- Center Card -->
<div class="login-container">
    <div class="login-card">

        <img src="../logotransparent.png" class="logo-image">

        <h1>Reset Status</h1>

        <!-- MESSAGE -->
        <p style="color: <?php echo $color; ?>; text-align:center; font-weight:bold;">
            <?php echo $message; ?>
        </p>

        <!-- BUTTONS -->
        <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">

            <a href="login.php">
                <button class="login-btn">Back to Login</button>
            </a>

            <a href="index.php">
                <button class="login-btn" style="background-color:#444;">
                    Go to Homepage
                </button>
            </a>

        </div>

    </div>
</div>

</body>
</html>