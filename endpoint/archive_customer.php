<?php
include "../conn/connection.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = mysqli_real_escape_string($con, $data['id']);

    mysqli_begin_transaction($con);
    try {
        // Get customer data first
        $select = "SELECT * FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($con, $select);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$customer = mysqli_fetch_assoc($result)) {
            throw new Exception("Customer not found");
        }

        // Archive the customer
        $customer_insert = "INSERT INTO archive_customers (id, name, birthday, archived_at, created_at, updated_at) 
                            VALUES (?, ?, ?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($con, $customer_insert);
        mysqli_stmt_bind_param($stmt, "issss", 
            $customer['id'],
            $customer['name'],
            $customer['birthday'],
            $customer['created_at'],
            $customer['updated_at']
        );
        mysqli_stmt_execute($stmt);

        // Delete the customer from the customers table
        $customer_delete = "DELETE FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($con, $customer_delete);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        mysqli_commit($con);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("Archive customer error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

mysqli_close($con);
?>
