<?php
session_start();
require_once("../config/db.php");
require_once("../config/session.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        header("Location: login.php?error=missing");
        exit;
    }

    // Updated: use 'userID' and 'fullname' to match your DB
    $stmt = $conn->prepare("SELECT userID, fullname, password, isAdmin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            // Check if user is admin (handle missing column gracefully)
            $isAdmin = false;
            
            // First, try to use database isAdmin column if it exists
            if (isset($user["isAdmin"]) && $user["isAdmin"] == 1) {
                $isAdmin = true;
            }
            // Fallback: make first user admin or specific email admin for demo
            elseif ($email === "admin@groovify.com" || $user["userID"] === 1) {
                $isAdmin = true;
            }
            
            // Use centralized session management
            setLoginSession($user["userID"], $user["fullname"], $email, $isAdmin);

            header("Location: dashboard.php?login=success");
            exit;
        }
    }

    header("Location: login.php?error=invalid");
    exit;
}
?>