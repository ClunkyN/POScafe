<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $qty = mysqli_real_escape_string($con, $_POST['qty']);
    
    
    $query = "INSERT INTO inventory (id, item, qty) 
              VALUES ('$id', '$item', '$qty')";
    $result = mysqli_query($con, $query);
    
    echo json_encode(['success' => $result]);
}
mysqli_close($con);
?>