<?php
session_start();
include "../conn/connection.php";
header('Content-Type: application/json');

try {
    $customer = $_POST['customer_id'];
    $order_number = $_POST['order_number'];
    $total_amount = $_POST['total_amount'];
    $amount_tendered = $_POST['amount_tendered'];
    $order_items = json_decode($_POST['order_items'], true);
    $ref_no = 'REF-' . time() . '-' . rand(100, 999); // Generate unique ref_no

    // Start transaction
    $con->begin_transaction();

    // Insert order header with ref_no
    $sql = "INSERT INTO orders (customer, ref_no, order_number, total_amount, amount_tendered, date_created) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssdd", $customer, $ref_no, $order_number, $total_amount, $amount_tendered);
    $stmt->execute();
    
    $order_id = $con->insert_id;

    // Insert order items
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    foreach ($order_items as $item) {
        $stmt->bind_param("iidd", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    // Update customer's order count
    $sql = "UPDATE customers SET orders = orders + 1 WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $customer);
    $stmt->execute();

    // Commit transaction
    $con->commit();
    echo json_encode(['success' => true, 'message' => 'Order saved successfully']);

} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$con->close();
?>