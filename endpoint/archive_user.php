<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Get the user_id to be archived
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['user_id'])) {
    $user_id = $data['user_id'];

    // Move user to archive
    $query = "INSERT INTO archive_users (user_id, fname, lname, user_name, role, email)
              SELECT user_id, fname, lname, user_name, role, email
              FROM user_db WHERE user_id = $user_id";
    $deleteQuery = "DELETE FROM user_db WHERE user_id = $user_id";

    if (mysqli_query($con, $query) && mysqli_query($con, $deleteQuery)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error archiving user: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
}
?>
