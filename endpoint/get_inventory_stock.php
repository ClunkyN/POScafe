<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "SELECT qty as stock, item as name FROM inventory WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['stock'] <= 0) {
            echo json_encode([
                'success' => false, 
                'error' => "No more " . $row['name']
            ]);
            exit;
        }
        echo json_encode([
            'success' => true, 
            'stock' => $row['stock'],
            'name' => $row['name']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Item not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}
?>