<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $username = mysqli_real_escape_string($con, $_POST['user_name']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    
    $query = "INSERT INTO user_db (user_id, fname, lname, user_name, role) VALUES ('$user_id', '$fname', '$lname', '$username', '$role')";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>