<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "SELECT * FROM inventory WHERE id = '$id'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $inventory_item = mysqli_fetch_assoc($result);
        echo json_encode($inventory_item);
    } else {
        echo json_encode(['error' => 'Inventory item not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}

mysqli_close($con);
