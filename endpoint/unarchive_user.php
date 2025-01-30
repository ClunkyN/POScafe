<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = intval($data['user_id']);
    
    mysqli_begin_transaction($con);
    
    try {
        // Include password in the restoration query
        $query = "INSERT INTO user_db (user_id, fname, lname, user_name, password, role, email)
                 SELECT user_id, fname, lname, user_name, password, role, email
                 FROM archive_users
                 WHERE user_id = ?";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to restore user");
        }

        $deleteQuery = "DELETE FROM archive_users WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to remove from archive");
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
}

mysqli_close($con);
?>