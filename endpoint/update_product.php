<?php
include "../../conn/connection.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    echo json_encode(['success' => $result ? true : false]);
}
?>