<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $playlistName = $_POST["playlist_name"];
    $userID = $_SESSION["user_id"];

    $stmt = $conn->prepare("INSERT INTO playlists (userID, name) VALUES (?, ?)");
    $stmt->bind_param("is", $userID, $playlistName);
    $stmt->execute();

    header("Location: library.php");
}
?>