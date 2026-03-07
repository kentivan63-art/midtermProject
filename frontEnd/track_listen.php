<?php
session_start();
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {
    $songID = $_POST["songID"] ?? null;
    $userID = $_SESSION["user_id"];
    
    if ($songID) {
        // Insert into listening history
        $stmt = $conn->prepare(
            "INSERT INTO listeninghistory (userID, songID, timestamp) 
             VALUES (?, ?, NOW())"
        );
        $stmt->bind_param("ii", $userID, $songID);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
    }
}
?>