<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug received data
        error_log("Received POST data: " . print_r($_POST, true));
        
        $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
        $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
        $price = mysqli_real_escape_string($con, $_POST['price']);
        $quantity = mysqli_real_escape_string($con, $_POST['quantity']);
        
        // Ensure required_items exists and is valid JSON
        if (!isset($_POST['required_items'])) {
            throw new Exception("Required items are missing");
        }
        
        $required_items = $_POST['required_items'];
        // Validate JSON format
        if (!json_decode($required_items)) {
            throw new Exception("Invalid required items format");
        }

        mysqli_begin_transaction($con);

        // Insert product
        $query = "INSERT INTO products (product_name, category_id, price, quantity, required_items) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sidis", $product_name, $category_id, $price, $quantity, $required_items);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting product: " . mysqli_error($con));
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Error in add_product.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>