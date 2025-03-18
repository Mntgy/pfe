<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer

// Function to generate a 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to store OTP in the database
function storeOTP($user_id, $otp) {
    global $pdo; // Assuming $pdo is your database connection

    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes')); // OTP expires in 5 minutes

    $stmt = $pdo->prepare("INSERT INTO otp_store (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $otp, $expires_at]);
}

// Function to send OTP via email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'tickettourelle@gmail.com '; // Replace with your email
        $mail->Password = 'ebfi dijo fbix zadn'; // Replace with your email password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@yourapp.com', 'Your App');
        $mail->addAddress($email); // User's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your One-Time Password (OTP)';
        $mail->Body    = "Your OTP is: <strong>$otp</strong>. It will expire in 5 minutes.";

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Failed to send email
    }
}
?>