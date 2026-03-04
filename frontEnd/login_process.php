<?php
session_start();
require_once("../config/db.php"); // make sure path is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        // redirect back with missing-field indicator
        header("Location: login.php?error=missing");
        exit;
    }

    // Find user
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // If you're using password_hash()
        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"] = $email;

            header("Location: dashboard.php");
            exit;
        }
    }

    // credentials didn't match
    header("Location: login.php?error=invalid");
    exit;
}
?>