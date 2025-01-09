<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prod_id = mysqli_real_escape_string($con, $_POST['prod_id']);
    $prod_name = mysqli_real_escape_string($con, $_POST['prod_name']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    $query = "UPDATE products SET 
        prod_name = '$prod_name',
        category = '$category',
        price = '$price',
        status = '$status'
        WHERE prod_id = '$prod_id'";

    $result = mysqli_query($con, $query);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

mysqli_close($con);
?>