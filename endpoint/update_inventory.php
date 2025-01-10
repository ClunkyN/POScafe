<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $inventory_id = mysqli_real_escape_string($con, $_POST['id']);
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $qty = mysqli_real_escape_string($con, $_POST['qty']);

    // Update the inventory table
    $query = "UPDATE inventory SET 
        item = '$item',
        qty = '$qty'
        WHERE id = '$inventory_id'";

    $result = mysqli_query($con, $query);

    if ($result) {
        // Rename the column in the item table if the item name changes
        $old_item_column = mysqli_real_escape_string($con, $_POST['old_item_column']);
        $new_item_column = str_replace(' ', '_', $item);
        if ($old_item_column !== $new_item_column) {
            $alter_query = "ALTER TABLE item CHANGE `$old_item_column` `$new_item_column` VARCHAR(255) DEFAULT '0'";
            $alter_result = mysqli_query($con, $alter_query);

            if ($alter_result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error renaming column: ' . mysqli_error($con)]);
            }
        } else {
            echo json_encode(['success' => true]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Error updating inventory: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

mysqli_close($con);
?>
