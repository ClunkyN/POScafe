<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $birthday = mysqli_real_escape_string($con, $_POST['birthday']);

    $query = "INSERT INTO customers (name, birthday, created_at, updated_at) VALUES ('$name', '$birthday', NOW(), NOW())";
    $result = mysqli_query($con, $query);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}
mysqli_close($con);
?>
