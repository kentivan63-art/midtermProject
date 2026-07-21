<?php
session_start();
require_once("../config/db.php");
require_once("../config/session.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$fullname || !$email || !$password) {
        die("Please fill all required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    // CHECK IF EMAIL ALREADY EXISTS
    $stmt = $conn->prepare("SELECT userID FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // REDIRECT BACK WITH ERROR
        header("Location: login.php?error=exists");
        exit;
    }
    $stmt->close();

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hashed);
    if ($stmt->execute()) {
        // Use centralized session management
        setLoginSession($stmt->insert_id, $fullname, $email);
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error creating account.";
    }
    $stmt->close();
}
?>