<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
    exit;
}

// Validate input data
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(["error" => "Missing required fields (product_id, quantity)."]);
    exit;
}

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Fetch the product details
$product_query = "SELECT price FROM products WHERE id = ?";
$stmt = $con->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Product not found."]);
    exit;
}

$product = $result->fetch_assoc();
$price = $product['price'];

// Insert data into the sold table
$insert_query = "INSERT INTO sold (product_id, qty, price, date) VALUES (?, ?, ?, NOW())";
$stmt = $con->prepare($insert_query);
$stmt->bind_param("iid", $product_id, $quantity, $price);

if ($stmt->execute()) {
    echo json_encode(["success" => "Sold item recorded successfully."]);
} else {
    echo json_encode(["error" => "Failed to record sold item: " . $stmt->error]);
}

$stmt->close();
$con->close();
?>
