<?php
session_start();
include "../conn/connection.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];
$userId = $_SESSION['user_id'];

// Validate new password
if (strlen($newPassword) < 8 || 
    !preg_match('/[A-Z]/', $newPassword) || 
    !preg_match('/[a-z]/', $newPassword) || 
    !preg_match('/[0-9]/', $newPassword) || 
    !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
    echo json_encode(['success' => false, 'message' => 'New password does not meet requirements']);
    exit();
}

try {
    // Verify current password - Note we use $con instead of $connection
    $stmt = $con->prepare("SELECT password FROM user_db WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $stmt->bind_param("s", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }
    
    // Update with new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $con->prepare("UPDATE user_db SET password = ? WHERE user_id = ?");
    if (!$updateStmt) {
        throw new Exception("Prepare update failed: " . $con->error);
    }
    
    $updateStmt->bind_param("ss", $hashedPassword, $userId);
    if (!$updateStmt->execute()) {
        throw new Exception("Password update failed: " . $updateStmt->error);
    }
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        throw new Exception("No rows were updated");
    }

} catch (Exception $e) {
    error_log("Password update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating password: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($con)) $con->close();
}
?>