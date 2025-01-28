<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
    
    $query = "UPDATE products SET 
              product_name = ?,
              category_id = ?,
              price = ?,
              quantity = ?
              WHERE id = ?";
              
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sidii", $product_name, $category_id, $price, $quantity, $id);
    $result = mysqli_stmt_execute($stmt);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>