<!DOCTYPE html>
<html>
<head>
    <title>Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/style.css?v=999">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>

    <nav class="navbar">
    <img id="logo" src="../groovifytextlogo.png" alt="GroovifyText Logo">
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="login.php">Log in</a></li>
    </ul>
</nav>

<div class="hero-section">
    <img src="../homepage.jpg" alt="Homepage Image" class="hero-image">

    <div class="hero-text">
        <div class="tagline">
            Feel the Beat.<br>Live the Groove.
        </div>

        <p class="subheading">
            Stream unlimited music. Discover new artists. Experience sound like never before.
        </p>

        <div class="cta">
            <a href="signup.php" class="cta-button">Sign up now</a>
        </div>
    </div>
</div>

    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 0);

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "midtermProject";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8mb4");

        if (isset($_POST['user_name']) && !empty(trim($_POST['user_name']))) {
            $user = trim($_POST['user_name']);

            $stmt = $conn->prepare("INSERT INTO users (username) VALUES (?)");
            $stmt->bind_param("s", $user);

            if ($stmt->execute()) {
                echo "<p style='color:blue;'>Saved: " . htmlspecialchars($user) . "</p>";
            } else {
                echo "<p style='color:red;'>Error saving user.</p>";
            }

            $stmt->close();
        }

        $conn->close();

    } catch (mysqli_sql_exception $e) {
        exit;
    }
    ?>
</body>

</html>