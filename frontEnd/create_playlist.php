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

    $playlistName = trim($_POST["playlist_name"] ?? "");
    $userID = $_SESSION["userID"];

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