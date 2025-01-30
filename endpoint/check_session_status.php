<?php
session_start();
include "../conn/connection.php";
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check if user is archived
    $query = "SELECT * FROM archive_users WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        session_destroy();
        echo json_encode([
            'status' => 'archived',
            'message' => 'Account has been deactivated. Please contact your administrator.'
        ]);
        exit;
    }
    
    echo json_encode(['status' => 'active']);
} else {
    echo json_encode(['status' => 'not_logged_in']);
}
?>