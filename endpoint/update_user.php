<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Get form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $fname = isset($_POST['fname']) ? mysqli_real_escape_string($con, $_POST['fname']) : null;
    $lname = isset($_POST['lname']) ? mysqli_real_escape_string($con, $_POST['lname']) : null;
    $user_name = isset($_POST['user_name']) ? mysqli_real_escape_string($con, $_POST['user_name']) : null;
    $role = isset($_POST['role']) ? mysqli_real_escape_string($con, $_POST['role']) : null;
    $email = isset($_POST['email']) ? mysqli_real_escape_string($con, $_POST['email']) : null;

    // Validate inputs
    if ($user_id && $fname && $lname && $user_name && $role && $email) {
        $query = "UPDATE user_db SET 
            fname = '$fname',
            lname = '$lname',
            user_name = '$user_name',
            role = '$role',
            email = '$email'
            WHERE user_id = $user_id";

        if (mysqli_query($con, $query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
