<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);
    try {
        $delete = "DELETE FROM archive_products WHERE id = '$id'";
        mysqli_query($con, $delete);
        
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>