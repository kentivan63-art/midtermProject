<?php
session_start();
require_once("../config/db.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['forgot_error'] = "Invalid request.";
    header("Location: forgot_password.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!$email) {
    $_SESSION['forgot_error'] = "Please enter your email.";
    header("Location: forgot_password.php");
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['forgot_error'] = "Email not found.";
    header("Location: forgot_password.php");
    exit;
}

// Generate 6-digit code
$token = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Save token to database
$update = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

// Send email via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // ⚠️ Replace these with your Gmail + App Password
    $mail->Username = 'alixsupangan2122324@gmail.com';
    $mail->Password = 'unlvjtubynjaffkr';
    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom($mail->Username, 'Groovify');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Groovify Password Reset Code';
    $mail->Body = "
        <h2>Groovify Password Reset</h2>
        <p>Your reset code is: <b>$token</b></p>
        <p>Use this code on the reset password page to update your password.</p>
        <a href='../frontEnd/reset_password.php' style='display:inline-block; padding:10px 20px; background:#1db954; color:#fff; text-decoration:none; border-radius:5px;'>Reset Password</a>
    ";
    $mail->AltBody = "Your reset code is: $token\nVisit reset_password.php to reset your password.";

    $mail->send();

    // Save the email in session so reset_password.php knows which account
$_SESSION['reset_email'] = $email;

// Redirect user to reset_password.php
header("Location: reset_password.php");
exit;

} catch (Exception $e) {
    $_SESSION['forgot_error'] = "Could not send email. Please check your Gmail App Password or try again.";
    header("Location: forgot_password.php");
    exit;
}