<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        // Get product data first
        $select = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($con, $select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if (!$product) {
            throw new Exception("Product not found");
        }

        // Insert into archive_products with all fields
        $insert = "INSERT INTO archive_products 
                  (id, product_name, category_id, price, quantity, required_items, archived_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $insert);
        mysqli_stmt_bind_param($stmt, "isidis", 
            $product['id'],
            $product['product_name'],
            $product['category_id'],
            $product['price'],
            $product['quantity'],
            $product['required_items']
        );
        mysqli_stmt_execute($stmt);
        
        // Delete from products
        $delete = "DELETE FROM products WHERE id = ?";
        $stmt = mysqli_prepare($con, $delete);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
mysqli_close($con);
?>