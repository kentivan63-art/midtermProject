<?php
header('Content-Type: application/json; charset=utf-8');

// ✅ Database connection (since you don't have db.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "midtermProject"; // make sure this matches your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "results" => [],
        "error" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$q = trim($_GET["q"] ?? "");
$limit = (int)($_GET["limit"] ?? 200);
if ($limit < 1 || $limit > 500) $limit = 200;

if ($q === "") {
    $stmt = $conn->prepare("
        SELECT id, title, artist, file_path
        FROM songs
        ORDER BY id DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
} else {
    $like = "%" . $q . "%";
    $stmt = $conn->prepare("
        SELECT id, title, artist, file_path
        FROM songs
        WHERE title LIKE ? OR artist LIKE ?
        ORDER BY title ASC
        LIMIT ?
    ");
    $stmt->bind_param("ssi", $like, $like, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = $row;
}

echo json_encode(["results" => $songs]);