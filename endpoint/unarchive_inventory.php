<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);
    
    mysqli_begin_transaction($con);

    try {
        // Get archived item data
        $select = "SELECT * FROM archive_inventory WHERE id = ?";
        $stmt = mysqli_prepare($con, $select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(!$item = mysqli_fetch_assoc($result)) {
            throw new Exception("Archived item not found");
        }

        // Insert back into inventory
        $insert = "INSERT INTO inventory (id, item, qty) 
                  VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $insert);
        mysqli_stmt_bind_param($stmt, "isi", 
            $item['id'],
            $item['item'],
            $item['qty']
        );
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to restore item");
        }
        
        // Delete from archive
        $delete = "DELETE FROM archive_inventory WHERE id = ?";
        $stmt = mysqli_prepare($con, $delete);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to remove from archive");
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

mysqli_close($con);
?>
