<?php
session_start();
include "../conn/connection.php";

// Check if 'month' parameter is provided
if (!isset($_GET['month'])) {
    echo json_encode(["error" => "Month parameter is required."]);
    exit;
}

$month = $_GET['month'];

// Fetch data from the sold table and join with the products table
$query = "
    SELECT 
        sold.*, 
        products.name, 
        products.price, 
        SUM(sold.qty) AS tqty
    FROM sold
    INNER JOIN products ON sold.product_id = products.id
    WHERE DATE_FORMAT(sold.date, '%Y-%m') = ?
    GROUP BY sold.product_id
";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $month);
$stmt->execute();
$result = $stmt->get_result();

// Check if any rows are returned
if ($result->num_rows === 0) {
    echo json_encode(["error" => "No records found for the selected month."]);
    exit;
}

$soldItems = [];
while ($row = $result->fetch_assoc()) {
    $row['total_sales'] = number_format($row['tqty'] * $row['price'], 2);
    $soldItems[] = $row;
}

echo json_encode($soldItems);

// Close the connection
$stmt->close();
$con->close();
?>
