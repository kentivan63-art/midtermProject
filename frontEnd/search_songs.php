<?php
header('Content-Type: application/json; charset=utf-8');

// ✅ Database connection (since you don't have db.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "midtermProject"; // make sure this matches your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// create the songs table if it doesn't exist yet
$conn->query("CREATE TABLE IF NOT EXISTS songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL
)");

// if the table is empty, try populating from the provided SQL file
$res = $conn->query("SELECT COUNT(*) FROM songs");
$count = $res->fetch_row()[0] ?? 0;
if ($count == 0) {
    $sqlPath = __DIR__ . '/../assets/audio/songs_insert.sql';
    if (file_exists($sqlPath)) {
        $sql = file_get_contents($sqlPath);
        if ($sql) {
            $conn->multi_query($sql);
            // consume any additional results to clear the connection
            while ($conn->more_results() && $conn->next_result()) {
                // do nothing
            }
        }
    }
}

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