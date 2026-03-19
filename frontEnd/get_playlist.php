<?php
require_once __DIR__ . "/../config/db.php";

$playlist_id = $_POST["playlist_id"] ?? 0;
$song_id = $_POST["song_id"] ?? 0;

$stmt = $conn->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
$stmt->bind_param("ii", $playlist_id, $song_id);
$stmt->execute();

echo "Added!";