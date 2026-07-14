<?php
session_start();

// Session timeout handling
$timeout = 300; // 5 minutes
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "error" => "Session timeout"
        ]);
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

// Debug logging
error_log("get_playlist.php called");
error_log("REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION["userID"])) {
    echo json_encode([
        "success" => false,
        "error" => "User not logged in"
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
    exit;
}

$playlistID = $_POST["playlist_id"] ?? 0;
$songID = $_POST["song_id"] ?? 0;
$userID = $_SESSION["userID"];

// Input validation - ensure both are integers
if (!filter_var($playlistID, FILTER_VALIDATE_INT) || !filter_var($songID, FILTER_VALIDATE_INT)) {
    error_log("Invalid input: playlistID or songID is not a valid integer");
    echo json_encode([
        "success" => false,
        "error" => "Invalid input parameters"
    ]);
    exit;
}

// Verify user owns the playlist
$check = $conn->prepare("SELECT playlistID FROM playlists WHERE playlistID = ? AND userID = ?");
if (!$check) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode([
        "success" => false,
        "error" => "Database prepare failed: " . $conn->error
    ]);
    exit;
}

$check->bind_param("ii", $playlistID, $userID);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("INSERT INTO playlist_songs (playlistID, songID) VALUES (?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode([
            "success" => false,
            "error" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }
    
    $stmt->bind_param("ii", $playlistID, $songID);
    if ($stmt->execute()) {
        error_log("Successfully added song to playlist. playlistID: " . $playlistID . ", songID: " . $songID);
        echo json_encode([
            "success" => true,
            "playlistID" => $playlistID,
            "songID" => $songID
        ]);
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo json_encode([
            "success" => false,
            "error" => "Database execute failed: " . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    error_log("Playlist not found or user does not have permission");
    echo json_encode([
        "success" => false,
        "error" => "Playlist not found or access denied"
    ]);
}
$check->close();
$conn->close();