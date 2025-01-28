<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']); // Fixed variable name from $id to $data['id']

    mysqli_begin_transaction($con);
    try {
        // Get archived category data
        $cat_select = "SELECT * FROM archive_categories WHERE id = ?";
        $stmt = mysqli_prepare($con, $cat_select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$category = mysqli_fetch_assoc($result)) {
            throw new Exception("Category not found in archives");
        }

        // Restore category first
        $cat_insert = "INSERT INTO categories (id, category_name, description) 
                      VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $cat_insert);
        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $category['id'],
            $category['category_name'],
            $category['description']
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to restore category: " . mysqli_error($con));
        }

        // Get all archived products for this category
        $prod_select = "SELECT * FROM archive_products WHERE category_id = ?";
        $stmt = mysqli_prepare($con, $prod_select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $products = mysqli_stmt_get_result($stmt);

        // Restore each product in the category
        while ($product = mysqli_fetch_assoc($products)) {
            // Insert back into products with all fields
            $prod_insert = "INSERT INTO products 
                  (id, product_name, category_id, price, quantity, required_items) 
                  VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $prod_insert);
            mysqli_stmt_bind_param(
                $stmt,
                "isidis",
                $product['id'],
                $product['product_name'],
                $product['category_id'],
                $product['price'],
                $product['quantity'],      
                $product['required_items'] 
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to restore product: " . mysqli_error($con));
            }
            // Delete from archive_products
            $prod_delete = "DELETE FROM archive_products WHERE id = ?";
            $stmt = mysqli_prepare($con, $prod_delete);
            mysqli_stmt_bind_param($stmt, "i", $product['id']);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to delete archived product: " . mysqli_error($con));
            }
        }

        // Finally delete the category from archive
        $cat_delete = "DELETE FROM archive_categories WHERE id = ?";
        $stmt = mysqli_prepare($con, $cat_delete);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete archived category: " . mysqli_error($con));
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Restore category error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

mysqli_close($con);
