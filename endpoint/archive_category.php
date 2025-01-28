<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        // Get category data first
        $select = "SELECT * FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($con, $select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(!$category = mysqli_fetch_assoc($result)) {
            throw new Exception("Category not found");
        }

        // Get all products in this category
        $prod_select = "SELECT * FROM products WHERE category_id = ?";
        $stmt = mysqli_prepare($con, $prod_select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $products = mysqli_stmt_get_result($stmt);

        // Archive each product in the category
        while($product = mysqli_fetch_assoc($products)) {
            $prod_insert = "INSERT INTO archive_products 
                          (id, product_name, category_id, price, category_name, quantity, required_items) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $prod_insert);
            mysqli_stmt_bind_param($stmt, "isidsis", 
                $product['id'],
                $product['product_name'],
                $product['category_id'],
                $product['price'],
                $category['category_name'],
                $product['quantity'],          
                $product['required_items']  
            );
            mysqli_stmt_execute($stmt);
        

            // Delete product from products table
            $prod_delete = "DELETE FROM products WHERE id = ?";
            $stmt = mysqli_prepare($con, $prod_delete);
            mysqli_stmt_bind_param($stmt, "i", $product['id']);
            mysqli_stmt_execute($stmt);
        }
        
        // Archive the category
        $cat_insert = "INSERT INTO archive_categories (id, category_name, description) 
                      VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $cat_insert);
        mysqli_stmt_bind_param($stmt, "iss", 
            $id, 
            $category['category_name'], 
            $category['description']
        );
        mysqli_stmt_execute($stmt);
        
        // Delete the category
        $cat_delete = "DELETE FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($con, $cat_delete);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Archive category error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

mysqli_close($con);
?>