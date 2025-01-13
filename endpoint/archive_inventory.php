<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $prod_id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        $insert = "INSERT INTO archive_inventory (id, item, qty, status)
                  SELECT id, item, qty, status 
                  FROM inventory WHERE id = '$id'";
        mysqli_query($con, $insert);
        
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>