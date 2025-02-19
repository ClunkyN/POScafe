<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id']; // Don't convert to int
    
    mysqli_begin_transaction($con);
    
    try {
        // Check if user exists
        $checkQuery = "SELECT * FROM user_db WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $user_id); // Use string parameter
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$user = mysqli_fetch_assoc($result)) {
            throw new Exception("User not found");
        }

        // Insert into archive
        $query = "INSERT INTO archive_users 
            (user_id, fname, lname, user_name, password, role, email)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
                 
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sssssss", 
            $user['user_id'],
            $user['fname'],
            $user['lname'],
            $user['user_name'],
            $user['password'],
            $user['role'],
            $user['email']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to archive user");
        }

        // Delete from user_db
        $deleteQuery = "DELETE FROM user_db WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete user");
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
}
?>