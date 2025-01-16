<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        // Get product data first
        $select = "SELECT * FROM products WHERE id = '$id'";
        $result = mysqli_query($con, $select);
        $product = mysqli_fetch_assoc($result);
        
        // Insert into archive_products
        $insert = "INSERT INTO archive_products 
                  (id, product_name, category_id, price, stock, category_name) 
                  VALUES ('$id', '{$product['product_name']}', 
                          '{$product['category_id']}', '{$product['price']}', 
                          '{$product['stock']}', '{$product['category_name']}')";
        mysqli_query($con, $insert);
        
        // Delete from products
        $delete = "DELETE FROM products WHERE id = '$id'";
        mysqli_query($con, $delete);
        
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
mysqli_close($con);
?>