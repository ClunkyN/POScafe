<?php
include "../conn/connection.php";
header('Content-Type: application/json');

// Validate the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method. Only POST is allowed."]);
    exit;
}

// Validate input data
if (!isset($_POST['id']) || !isset($_POST['quantity'])) {
    echo json_encode(["error" => "Missing required fields (id, quantity)."]);
    exit;
}

$id = $_POST['id'];
$quantity = $_POST['quantity'];

// Update the sold table with the new quantity
$query = "UPDATE sold SET qty = ? WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $quantity, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => "Sold item updated successfully."]);
} else {
    echo json_encode(["error" => "Failed to update sold item: " . $stmt->error]);
}

$stmt->close();
$con->close();
?>
