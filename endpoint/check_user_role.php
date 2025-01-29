<?php
session_start();
include "../conn/connection.php";
header('Content-Type: application/json');

if (isset($_POST['user_id'])) {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    
    // Check if user is archived
    $archive_query = "SELECT * FROM archive_users WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $archive_query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $archive_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($archive_result) > 0) {
        echo json_encode(['archived' => true]);
        exit();
    }
    
    // Check current role
    $query = "SELECT role FROM user_db WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && $user['role'] === 'employee') {
        $_SESSION['role'] = 'employee'; // Update session role
        echo json_encode([
            'success' => true,
            'role' => 'employee',
            'redirect' => '../dashboard/employee_dashboard.php'
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'role' => $user['role'] ?? 'new_user'
    ]);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>