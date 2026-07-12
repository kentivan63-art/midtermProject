<?php
require_once __DIR__ . "/../config/db.php";

$playlistID = $_POST["playlist_id"] ?? 0;
$songID = $_POST["song_id"] ?? 0;

$stmt = $conn->prepare("INSERT INTO playlist_songs (playlistID, songID) VALUES (?, ?)");
$stmt->bind_param("ii", $playlistID, $songID);
$stmt->execute();

echo "Added!";