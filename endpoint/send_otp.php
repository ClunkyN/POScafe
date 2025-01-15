<?php
require_once '../conn/connection.php';
require '../vendor/autoload.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Verify email exists in database
    $stmt = mysqli_prepare($con, "SELECT user_id FROM user_db WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
        exit;
    }
    
    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_email'] = $email;
    $_SESSION['otp_timestamp'] = time();

    // Get mail config
    $config = require '../config/mail_config.php';

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port = $config['smtp_port'];

        // Recipients
        $mail->setFrom($config['smtp_username'], $config['from_name']);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #6E6A43;'>Password Reset OTP</h2>
                <p>Your OTP for password reset is: <strong style='font-size: 24px; color: #6E6A43;'>{$otp}</strong></p>
                <p>This OTP will expire in 10 minutes.</p>
                <p style='color: #666;'>If you didn't request this, please ignore this email.</p>
            </div>
        ";

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Mail Error: {$mail->ErrorInfo}"]);
    }
}
?>