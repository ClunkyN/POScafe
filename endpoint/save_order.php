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

    // Insert order items and update the sold table
    $sql_order_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt_order_items = $con->prepare($sql_order_items);

    $sql_sold = "INSERT INTO sold (product_id, qty, date) 
                 VALUES (?, ?, CURDATE())
                 ON DUPLICATE KEY UPDATE 
                 qty = qty + VALUES(qty)";
    $stmt_sold = $con->prepare($sql_sold);

    foreach ($order_items as $item) {
        // Insert into order_items
        $stmt_order_items->bind_param("iidd", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt_order_items->execute();

        // Update or insert into sold
        $stmt_sold->bind_param("ii", $item['id'], $item['quantity']);
        $stmt_sold->execute();
    }

    // Update customer's order count
    $sql_update_customer = "UPDATE customers SET orders = orders + 1 WHERE id = ?";
    $stmt_update_customer = $con->prepare($sql_update_customer);
    $stmt_update_customer->bind_param("i", $customer);
    $stmt_update_customer->execute();

    // Commit transaction
    $con->commit();
    echo json_encode(['success' => true, 'message' => 'Order saved successfully']);

} catch (Exception $e) {
    // Rollback transaction in case of error
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$con->close();
?>
