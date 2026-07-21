<?php
require_once("../config/session.php");
requireLogin();

require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $playlistName = trim($_POST["playlist_name"] ?? "");
    $userID = getCurrentUserID();

    // Input validation
    if (empty($playlistName)) {
        error_log("Empty playlist name provided");
        header("Location: library.php?error=empty_name");
        exit;
    }

    // Sanitize playlist name
    $playlistName = htmlspecialchars($playlistName, ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("INSERT INTO playlists (userID, name) VALUES (?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        header("Location: library.php?error=db_error");
        exit;
    }
    
    $stmt->bind_param("is", $userID, $playlistName);
    if ($stmt->execute()) {
        error_log("Successfully created playlist: " . $playlistName . " for userID: " . $userID);
        header("Location: library.php?success=created");
    } else {
        error_log("Execute failed: " . $stmt->error);
        header("Location: library.php?error=execute_failed");
    }
    $stmt->close();
}
?>