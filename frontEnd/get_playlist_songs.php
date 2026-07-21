<?php
require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json");

// Debug logging
error_log("get_playlist_songs.php called");
error_log("REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("GET data: " . print_r($_GET, true));

if (!getCurrentUserID()) {
    echo json_encode([
        "success" => false,
        "error" => "User not logged in"
    ]);
    exit;
}

$playlistID = $_GET["playlist_id"] ?? 0;
$userID = getCurrentUserID();

// Input validation - ensure playlistID is a valid integer
if (!filter_var($playlistID, FILTER_VALIDATE_INT)) {
    error_log("Invalid input: playlistID is not a valid integer");
    echo json_encode([
        "success" => false,
        "error" => "Invalid playlistID parameter"
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
    $sql = "
    SELECT songs.*
    FROM playlist_songs
    JOIN songs ON songs.songID = playlist_songs.songID
    WHERE playlist_songs.playlistID = ?
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode([
            "success" => false,
            "error" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $playlistID);
    $stmt->execute();
    $result = $stmt->get_result();

    $songs = [];

    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    
    error_log("Successfully retrieved " . count($songs) . " songs for playlistID: " . $playlistID);
    echo json_encode([
        "success" => true,
        "songs" => $songs
    ]);
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