<?php
session_start();
require_once("../config/db.php"); // make sure path is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        die("Missing fields.");
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

    echo "Invalid email or password.";
}
?>