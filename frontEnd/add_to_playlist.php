<?php
session_start();
// Session timeout handling
$timeout = 300; // 5 minutes
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        exit;
    }
}
$_SESSION['last_activity'] = time();

require_once("../config/db.php");

if (!isset($_SESSION["userID"])) {
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID = $_SESSION["userID"];
    $playlistID = $_POST["playlistID"] ?? null;
    $songID = $_POST["songID"] ?? null;

    if ($playlistID && $songID) {

        $check = $conn->prepare("SELECT playlistID FROM playlists WHERE playlistID = ? AND userID = ?");
        $check->bind_param("ii", $playlistID, $userID);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO playlist_songs (playlistID, songID) VALUES (?, ?)");
            $stmt->bind_param("ii", $playlistID, $songID);
            $stmt->execute();
        }
    }

    echo "success";
}
?>