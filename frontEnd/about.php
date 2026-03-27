<!DOCTYPE html>
<html>
<head>
    <title>Groovify</title>
    <link rel="stylesheet" href="/midtermProject/assets/about.css?v=999">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico?v=2">
</head>
<body>

    <nav class="navbar">
    <a href="index.php">
    <img id="logo" src="../groovifytextlogo.png" alt="GroovifyText Logo">
</a>
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
            What is Groovify?
        </div>

        <p class="subheading">
            Groovify is a modern, user-friendly music streaming web application that allows users to explore, play, and manage their favorite songs seamlessly through a clean and responsive interface.
        </p>

        <div class="credits-box">
    <h3>Credits</h3>
    <ul>
        <li><span>Xyril Xian Ogario</span> – Project Manager / Front-End Developer</li>
        <li><span>Kent Ivan Lubas</span> – Database / Back-End Developer</li>
        <li><span>Jahred Inguito</span> – UI/UX Designer / Front-End Developer</li>
        <li><span>Edgil Manjac</span> – Backend Developer / Tester</li>
        <li><span>Alix Supangan</span> – Backend Developer / Front-End Developer / Tester</li>
        <li><span>Mary Antoinette Quiros</span> – Documentation</li>
    </ul>
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