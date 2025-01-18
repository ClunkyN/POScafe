<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']); 
    
    mysqli_begin_transaction($con);
    try {
        // Get archived customer data
        $cust_select = "SELECT * FROM archive_customers WHERE id = ?";
        $stmt = mysqli_prepare($con, $cust_select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$customer = mysqli_fetch_assoc($result)) {
            throw new Exception("Customer not found in archives");
        }

        // Restore customer
        $cust_insert = "INSERT INTO customers (id, name, birthday, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $cust_insert);
        mysqli_stmt_bind_param($stmt, "issss", 
            $customer['id'],
            $customer['name'],
            $customer['birthday'],
            $customer['created_at'],
            $customer['updated_at']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to restore customer: " . mysqli_error($con));
        }

        // Delete customer from archive
        $cust_delete = "DELETE FROM archive_customers WHERE id = ?";
        $stmt = mysqli_prepare($con, $cust_delete);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to delete archived customer: " . mysqli_error($con));
        }

        mysqli_commit($con);
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Restore customer error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

mysqli_close($con);
?>
