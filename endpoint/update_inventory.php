<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $available_qty = mysqli_real_escape_string($con, $_POST['available_qty']);
    $additional_qty = mysqli_real_escape_string($con, $_POST['additional_qty']);
    
    // Calculate new total quantity
    $new_qty = $available_qty + $additional_qty;
    
    $query = "UPDATE inventory SET 
              item = ?,
              qty = ?
              WHERE id = ?";
              
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sii", $item, $new_qty, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

mysqli_close($con);
?>
