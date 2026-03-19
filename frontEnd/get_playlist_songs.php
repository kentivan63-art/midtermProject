<?php
require_once __DIR__ . "/../config/db.php";

$playlist_id = $_GET["playlist_id"] ?? 0;

$sql = "
SELECT songs.* 
FROM playlist_songs 
JOIN songs ON songs.id = playlist_songs.song_id
WHERE playlist_songs.playlist_id = $playlist_id
";

$result = $conn->query($sql);

$songs = [];

while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

echo json_encode($songs);