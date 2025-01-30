<?php
session_start();
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTPEmail($email, $otp) {
    $config = require '../config/mail_config.php';
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port = $config['smtp_port'];

        $mail->setFrom($config['smtp_username'], $config['from_name']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Signup OTP';
        $mail->Body = "Your OTP for signup is: <b>{$otp}</b><br>This OTP will expire in 5 minutes.";

        if($mail->send()) {
            return ['success' => true];
        }
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['email']) && isset($data['otp'])) {
        $result = sendOTPEmail($data['email'], $data['otp']);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    }
}