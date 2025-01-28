<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
        $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
        $price = mysqli_real_escape_string($con, $_POST['price']);
        $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
        $required_items = $_POST['required_items'];

        $query = "UPDATE products SET 
                  product_name = ?,
                  category_id = ?,
                  price = ?,
                  quantity = ?,
                  required_items = ?
                  WHERE id = ?";
                  
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sidisi", $product_name, $category_id, $price, $quantity, $required_items, $id);
        
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception(mysqli_error($con));
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
mysqli_close($con);
?>