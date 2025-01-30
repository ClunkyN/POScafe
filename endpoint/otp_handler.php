<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once "../conn/connection.php";
require_once "../conn/function.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception('Invalid JSON data received');
        }
        
        if (!isset($data['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($data['action']) {
            case 'verify':
                if (!isset($_SESSION['signup_otp'])) {
                    echo json_encode(['success' => false, 'message' => 'No OTP found']);
                    exit;
                }
                
                if ($data['otp'] == $_SESSION['signup_otp']) {
                    $result = registerUser($data['userData']);
                    echo json_encode($result);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
                }
                break;
                
            case 'generate':
                $otp = rand(100000, 999999);
                $_SESSION['signup_otp'] = $otp;
                $_SESSION['otp_timestamp'] = time();
                
                $result = sendOTP($data['userData']['email'], $otp);
                echo json_encode($result);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function sendOTP($email, $otp) {
    $data = [
        'email' => $email,
        'otp' => $otp
    ];
    
    $ch = curl_init('http://localhost/POScafe/endpoint/mail_handler.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function registerUser($data) {
    global $con;
    
    $h_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $user_id = random_num(20);
    
    $query = "INSERT INTO user_db (user_id, fname, lname, user_name, password, role, email) 
              VALUES (?, ?, ?, ?, ?, 'new_user', ?)";
              
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", 
        $user_id, 
        $data['fname'], 
        $data['lname'], 
        $data['user_name'], 
        $h_password,
        $data['email']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        return ['success' => true, 'message' => 'Registration successful'];
    } else {
        return ['success' => false, 'message' => 'Registration failed'];
    }
}