<?php
require_once '../conn/connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $h_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = mysqli_prepare($con, "UPDATE user_db SET password = ? WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $h_password, $email);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
}
?>