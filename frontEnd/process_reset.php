<?php
include("../config/db.php");

$token = $_POST['token'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, token_expiry FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invalid code.");
}

$user = $result->fetch_assoc();

// check expiry
if (strtotime($user['token_expiry']) < time()) {
    die("Code expired.");
}

// update password
$newPass = password_hash($password, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE id=?");
$update->bind_param("si", $newPass, $user['id']);
$update->execute();

echo "Password updated successfully!";
?>