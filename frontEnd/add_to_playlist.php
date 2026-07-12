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

    $playlistID = $_POST["playlist_id"] ?? null;
    $songID     = $_POST["song_id"] ?? null;
    $title      = $_POST["title"] ?? '';
    $artist     = $_POST["artist"] ?? '';
    $file_path  = $_POST["file_path"] ?? '';

    if ($playlistID && $songID && $file_path) {

        $check = $conn->prepare("SELECT playlistID FROM playlists WHERE playlistID = ? AND userID = ?");
        $check->bind_param("ii", $playlistID, $userID);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            // ✅ OPTIONAL: prevent duplicate songs
            $dup = $conn->prepare("SELECT id FROM playlist_songs WHERE playlistID=? AND songID=?");
            $dup->bind_param("ii", $playlistID, $songID);
            $dup->execute();

            if ($dup->get_result()->num_rows === 0) {

                $stmt = $conn->prepare("
                    INSERT INTO playlist_songs 
                    (playlistID, songID, title, artist, file_path) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iisss", $playlistID, $songID, $title, $artist, $file_path);
                $stmt->execute();
            }
        }
    }

    echo "success";
}
?>