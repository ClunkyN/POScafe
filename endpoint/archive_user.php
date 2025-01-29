<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = intval($data['user_id']);
    
    mysqli_begin_transaction($con);
    
    try {
        // Debug output
        error_log("Attempting to archive user ID: " . $user_id);

        // Check if user exists first
        $checkQuery = "SELECT * FROM user_db WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $user = mysqli_fetch_assoc($result);
        if (!$user) {
            error_log("User not found in database: " . $user_id);
            throw new Exception("User not found in database");
        }

        // Reset result pointer
        mysqli_data_seek($result, 0);

        // Move user to archive with explicit fields
        $query = "INSERT INTO archive_users 
                (user_id, fname, lname, user_name, password, role, email)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                 
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "issssss", 
            $user['user_id'],
            $user['fname'],
            $user['lname'],
            $user['user_name'],
            $user['password'],
            $user['role'],
            $user['email']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Failed to archive user: " . mysqli_error($con));
            throw new Exception("Failed to archive user");
        }

        // Delete from user_db
        $deleteQuery = "DELETE FROM user_db WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Failed to delete user: " . mysqli_error($con));
            throw new Exception("Failed to delete user");
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Archive error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
}

mysqli_close($con);
?>