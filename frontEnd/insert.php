<?php
require_once("../config/db.php");

// CHECK IF CONNECTION WAS SUCCESSFUL
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CHECK IF THE FORM WAS SUBMITTED
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['user_name'] ?? ''); // MATCHES THE NAME ATTRIBUTE IN YOUR FORM INPUT

    if (empty($user)) {
        die("Username is required.");
    }

    // WE WILL USE A FIXED EMAIL FOR DEMONSTRATION PURPOSES, AS THE FORM ONLY HAS A USERNAME FIELD
    $email = "user@example.com"; 

    // USE PREPARED STATEMENT TO PREVENT SQL INJECTION
    $stmt = $conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $email);

    if ($stmt->execute()) {
        echo "Successfully saved to database! <br>";
        echo "<a href='index.php'>Return to Home</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>