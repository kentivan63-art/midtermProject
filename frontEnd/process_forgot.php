<?php

// ✅ 1. TOP OF FILE (IMPORTS — MUST BE FIRST)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ 2. REQUIRE FILES (RIGHT AFTER USE)
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// ✅ 3. DATABASE CONNECTION
include("../config/db.php");

// ✅ 4. GET EMAIL FROM FORM
$email = $_POST['email'];

// check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Email not found.");
}

// ✅ 5. GENERATE CODE
$token = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// save to database
$update = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

// ✅ 6. CREATE MAIL OBJECT (PUT HERE)
$mail = new PHPMailer(true);

// ✅ 7. DEBUG MODE (PUT RIGHT AFTER MAIL OBJECT)
//$mail->SMTPDebug = 2;

try {

    // ✅ 8. SMTP SETTINGS (PUT INSIDE TRY)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'alixsupangan2122324@gmail.com'; // ✅ YOUR GMAIL
$mail->Password = 'cencywncnaffwpsc';           // ✅ APP PASSWORD (NO SPACES)

    

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // ✅ 9. EMAIL CONTENT
    $mail->setFrom('yourgmail@gmail.com', 'Groovify');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Code';
    $mail->Body = "Your reset code is: <b>$token</b>";

    // ✅ 10. SEND EMAIL
    $mail->send();

    echo "Code sent to your email!";
    echo "<br><a href='reset_password.php'>Reset Password</a>";

} catch (Exception $e) {

    // ✅ 11. ERROR OUTPUT
    echo "Mailer Error: " . $mail->ErrorInfo;
}