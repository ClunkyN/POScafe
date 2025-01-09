<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['category_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    
    $query = "INSERT INTO categories (category_name, description) VALUES ('$name', '$description')";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>