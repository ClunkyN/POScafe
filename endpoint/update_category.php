<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['category_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    
    $query = "UPDATE categories SET category_name = '$name', description = '$description' WHERE id = '$id'";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>