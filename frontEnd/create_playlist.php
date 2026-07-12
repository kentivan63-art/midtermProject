<?php
session_start();
// Session timeout handling
$timeout = 300; // 5 minutes
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=timeout");
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once("../config/db.php");

if (!isset($_SESSION["userID"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $playlistName = $_POST["playlist_name"];
    $userID = $_SESSION["userID"];

    $stmt = $conn->prepare("INSERT INTO playlists (userID, name) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $playlistName);
    $stmt->execute();

    header("Location: library.php");
}
?>