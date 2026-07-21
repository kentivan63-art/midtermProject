<?php
require_once("../config/session.php");
require_once("../config/db.php");

header("Content-Type: application/json");

// Debug logging
error_log("track_listen.php called");
error_log("REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("SESSION data: " . print_r($_SESSION, true));
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
    exit;
}

if (!getCurrentUserID()) {
    echo json_encode([
        "success" => false,
        "error" => "User not logged in"
    ]);
    exit;
}

$songID = $_POST["songID"] ?? null;
$userID = getCurrentUserID();

error_log("userID from session: " . $userID);
error_log("songID received: " . $songID);

// Input validation - ensure songID is a valid integer
if (!filter_var($songID, FILTER_VALIDATE_INT)) {
    error_log("Invalid input: songID is not a valid integer");
    echo json_encode([
        "success" => false,
        "error" => "Invalid songID parameter"
    ]);
    exit;
}

if (!$songID) {
    echo json_encode([
        "success" => false,
        "error" => "No songID received"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO listeninghistory (userID, songID, timestamp)
     VALUES (?, ?, NOW())"
);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode([
        "success" => false,
        "error" => "Database prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ii", $userID, $songID);

if ($stmt->execute()) {
    error_log("Successfully inserted listening history. ID: " . $stmt->insert_id);
    echo json_encode([
        "success" => true,
        "userID" => $userID,
        "songID" => $songID,
        "insert_id" => $stmt->insert_id
    ]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode([
        "success" => false,
        "error" => "Database execute failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>