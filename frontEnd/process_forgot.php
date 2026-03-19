<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$email = trim($_POST['email'] ?? '');

if (!$email) {
    die("Email is required.");
}

// CHECK IF EMAIL EXISTS
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Email not found.");
}

// GENERATE A RANDOM 6-DIGIT TOKEN AND SET EXPIRY TIME
$token = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// SAVE DATA TO DATABASE
$update = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hajings18@gmail.com';
    $mail->Password = 'PUT_YOUR_NEW_APP_PASSWORD_HERE';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->setFrom('hajings18@gmail.com', 'Groovify');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Code';
    $mail->Body = "Your reset code is: <b>$token</b>";
    $mail->AltBody = "Your reset code is: $token";

    $mail->send();

    echo "Code sent to your email!";
    echo "<br><a href='reset_password.php'>Reset Password</a>";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}