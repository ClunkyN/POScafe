<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $username = mysqli_real_escape_string($con, $_POST['user_name']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    $query = "UPDATE user_db SET fname = '$fname', lname = '$lname', user_name = '$username', role = '$role' WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>