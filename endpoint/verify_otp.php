<?php
require_once '../conn/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $submitted_otp = $_POST['otp'];
    
    // Check if OTP session variables exist
    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email']) || !isset($_SESSION['otp_timestamp'])) {
        echo json_encode(['success' => false, 'message' => 'OTP session expired. Please request a new OTP.']);
        exit;
    }

    // Verify email matches
    if ($_SESSION['reset_email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Check OTP expiration (10 minutes)
    if (time() - $_SESSION['otp_timestamp'] > 600) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_timestamp']);
        exit;
    }

    // Verify OTP
    if ($submitted_otp == $_SESSION['reset_otp']) {
        echo json_encode(['success' => true]);
        // Keep email session for password reset but clear OTP
        unset($_SESSION['reset_otp']);
        unset($_SESSION['otp_timestamp']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
    }
}
?>