<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Get the user_id to be restored
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id'];

    // Move user from archive_users back to user_db
    $query = "INSERT INTO user_db (user_id, fname, lname, user_name, role, email)
              SELECT user_id, fname, lname, user_name, role, email
              FROM archive_users
              WHERE user_id = $user_id";
    
    // Delete user from archive_users after restoring
    $deleteQuery = "DELETE FROM archive_users WHERE user_id = $user_id";

    if (mysqli_query($con, $query) && mysqli_query($con, $deleteQuery)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error restoring user: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
}
?>
