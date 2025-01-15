<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $stock = mysqli_real_escape_string($con, $_POST['stock']);
    
    $query = "INSERT INTO products (product_name, category_id, price, stock) 
              VALUES ('$product_name', '$category_id', '$price', '$stock')";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>