<?php
require_once __DIR__ . "/../config/db.php";

$playlistID = $_GET["playlist_id"] ?? 0;

$sql = "
SELECT songs.*
FROM playlist_songs
JOIN songs ON songs.songID = playlist_songs.songID
WHERE playlist_songs.playlistID = $playlistID
";

$result = $conn->query($sql);

$songs = [];

while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

echo json_encode($songs);