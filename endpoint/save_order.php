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
    $ref_no = 'REF-' . time() . '-' . rand(100, 999);

    // Start transaction
    $con->begin_transaction();

    // Insert order header
    $sql = "INSERT INTO orders (customer, ref_no, order_number, total_amount, amount_tendered, date_created) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssdd", $customer, $ref_no, $order_number, $total_amount, $amount_tendered);
    $stmt->execute();
    $order_id = $con->insert_id;

    // For each ordered product
    foreach ($order_items as $item) {
        // 1. Get product details including required items
        $product_query = "SELECT required_items, quantity FROM products WHERE id = ?";
        $stmt = $con->prepare($product_query);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        // 2. Check if enough product quantity available
        if ($product['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient product quantity for " . $item['name']);
        }

        // 3. Update product quantity
        $new_quantity = $product['quantity'] - $item['quantity'];
        $update_product = "UPDATE products SET quantity = ? WHERE id = ?";
        $stmt = $con->prepare($update_product);
        $stmt->bind_param("ii", $new_quantity, $item['id']);
        $stmt->execute();

        // 4. Deduct required items from inventory
        if (!empty($product['required_items'])) {
            $required_items = json_decode($product['required_items'], true);
            foreach ($required_items as $req_item) {
                // Calculate total quantity needed
                $total_needed = $req_item['quantity'] * $item['quantity'];
                
                // Update inventory
                $update_inventory = "UPDATE inventory 
                                   SET qty = qty - ? 
                                   WHERE id = ? AND qty >= ?";
                $stmt = $con->prepare($update_inventory);
                $stmt->bind_param("iii", $total_needed, $req_item['id'], $total_needed);
                if (!$stmt->execute()) {
                    throw new Exception("Insufficient inventory for " . $req_item['name']);
                }
                if ($stmt->affected_rows == 0) {
                    throw new Exception("Insufficient inventory for " . $req_item['name']);
                }
            }
        }

        // 5. Insert order items
        $sql_order_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($sql_order_items);
        $stmt->bind_param("iidd", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();

        // 6. Update sold table
        $sql_sold = "INSERT INTO sold (product_id, qty, date) VALUES (?, ?, CURDATE())
                    ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)";
        $stmt = $con->prepare($sql_sold);
        $stmt->bind_param("ii", $item['id'], $item['quantity']);
        $stmt->execute();
    }

    // Commit transaction
    $con->commit();
    echo json_encode(['success' => true, 'message' => 'Order completed successfully']);

} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$con->close();
?>
