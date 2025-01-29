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

    // Validate inputs
    if ($user_id && $fname && $lname && $user_name && $role) {
        // Add role change validation
        $originalRoleQuery = "SELECT role FROM user_db WHERE user_id = $user_id";
        $originalRoleResult = mysqli_query($con, $originalRoleQuery);
        $originalRoleRow = mysqli_fetch_assoc($originalRoleResult);
        $originalRole = $originalRoleRow['role'];

        if ($originalRole === 'employee' && $role === 'new_user') {
            echo json_encode(['success' => false, 'message' => 'Cannot change role back to new user']);
            exit();
        }

        $query = "UPDATE user_db SET 
            fname = '$fname',
            lname = '$lname',
            user_name = '$user_name',
            role = '$role'
            WHERE user_id = $user_id";

        if (mysqli_query($con, $query)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($con)]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields',
            'debug' => [
                'user_id' => $user_id,
                'fname' => $fname,
                'lname' => $lname,
                'user_name' => $user_name,
                'role' => $role
            ]
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
