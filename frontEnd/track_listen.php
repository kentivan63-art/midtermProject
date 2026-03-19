<?php
session_start();
require_once("../config/db.php");

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "error" => "Invalid request method"
    ]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "error" => "User not logged in"
    ]);
    exit;
}

$songID = $_POST["songID"] ?? null;
$userID = $_SESSION["user_id"];

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
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("ii", $userID, $songID);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "userID" => $userID,
        "songID" => $songID
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>