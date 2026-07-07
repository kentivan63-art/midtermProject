<?php
session_start();
require_once("../config/db.php");

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
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // REDIRECT BACK WITH ERROR
        header("Location: login.php?error=exists");
        exit;
    }
    $stmt->close();

    // The users table requires a NOT NULL username, but the signup form does
    // not collect one. Derive a unique username from the email local part.
    $base_username = preg_replace('/[^a-zA-Z0-9_]/', '', explode("@", $email)[0]);
    if ($base_username === "") {
        $base_username = "user";
    }
    $username = $base_username;
    $suffix = 1;
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    while ($check->num_rows > 0) {
        $username = $base_username . $suffix;
        $suffix++;
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
    }
    $check->close();

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $username, $email, $hashed);
    if ($stmt->execute()) {
        $_SESSION["user_id"] = $stmt->insert_id;
        $_SESSION["email"] = $email;
        $_SESSION["fullname"] = $fullname;
        $_SESSION["full_name"] = $fullname;
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error creating account.";
    }
    $stmt->close();
}
?>