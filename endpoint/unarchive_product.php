<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        // Get archived product data first
        $select = "SELECT * FROM archive_products WHERE id = '$id'";
        $result = mysqli_query($con, $select);
        $product = mysqli_fetch_assoc($result);
        
        // Insert back into products
        $insert = "INSERT INTO products 
                  (id, product_name, category_id, price) 
                  VALUES ('$id', '{$product['product_name']}', 
                          '{$product['category_id']}', '{$product['price']}')";
        mysqli_query($con, $insert);
        
        // Delete from archive_products
        $delete = "DELETE FROM archive_products WHERE id = '$id'";
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