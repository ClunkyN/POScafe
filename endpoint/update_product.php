<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $stock = mysqli_real_escape_string($con, $_POST['stock']);
    
    $query = "UPDATE products 
              SET product_name = '$product_name', 
                  category_id = '$category_id', 
                  price = '$price', 
                  stock = '$stock' 
              WHERE id = '$id'";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>