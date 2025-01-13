<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input data
    $inventory_id = mysqli_real_escape_string($con, $_POST['id']);
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $qty = mysqli_real_escape_string($con, $_POST['qty']);
    $old_item_column = isset($_POST['old_item_column']) ? mysqli_real_escape_string($con, $_POST['old_item_column']) : null;

    // Update the inventory table
    $query = "UPDATE inventory SET 
        item = '$item',
        qty = '$qty'
        WHERE id = '$inventory_id'";

    $result = mysqli_query($con, $query);

    if ($result) {
        // If old_item_column is provided, attempt to rename the column
        if ($old_item_column) {
            $new_item_column = str_replace(' ', '_', $item);
            if ($old_item_column !== $new_item_column) {
                $alter_query = "ALTER TABLE item CHANGE `$old_item_column` `$new_item_column` VARCHAR(255) DEFAULT '0'";
                $alter_result = mysqli_query($con, $alter_query);

                if (!$alter_result) {
                    echo json_encode(['success' => false, 'error' => 'Error renaming column: ' . mysqli_error($con)]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error updating inventory: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

mysqli_close($con);
?>
