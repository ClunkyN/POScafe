<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);

    try {
        // Insert item into the archive table
        $insert = "INSERT INTO archive_inventory (id, item, qty, status)
                   SELECT id, item, qty, status FROM inventory WHERE id = '$id'";
        
        if (!mysqli_query($con, $insert)) {
            throw new Exception("Error archiving item: " . mysqli_error($con));
        }

        // Commit the transaction
        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}
