<?php
session_start();
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        header("Location: login.php?error=missing");
        exit;
    }

    // Updated: use 'userID' and 'fullname' to match your DB
    $stmt = $conn->prepare("SELECT userID, fullname, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["userID"] = $user["userID"];
            $_SESSION["fullname"] = $user["fullname"]; // matches your DB column

            header("Location: dashboard.php?login=success");
            exit;
        }
    }

    header("Location: login.php?error=invalid");
    exit;
}
?>