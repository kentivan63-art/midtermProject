<?php
session_start();
require_once("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_SESSION["user_id"];
    $playlistID = $_POST["playlistID"] ?? null;
    $songID = $_POST["songID"] ?? null;

    if ($playlistID && $songID) {
        // Make sure playlist belongs to logged in user
        $check = $conn->prepare("SELECT id FROM playlists WHERE id = ? AND userID = ?");
        $check->bind_param("ii", $playlistID, $userID);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO playlist_songs (playlistID, songID) VALUES (?, ?)");
            $stmt->bind_param("ii", $playlistID, $songID);
            $stmt->execute();
            $stmt->close();
        }

        $check->close();
    }
}

header("Location: dashboard.php");
exit;
?>