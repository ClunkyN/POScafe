<?php
session_start();
include "../conn/connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inventory_id = mysqli_real_escape_string($con, $_POST['id']); // Sanitize the ID
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $qty = mysqli_real_escape_string($con, $_POST['qty']);

    mysqli_begin_transaction($con);

    try {
        // Insert inventory item into the inventory table
        $query = "INSERT INTO inventory (id, item, qty) 
                  VALUES ('$inventory_id', '$item', '$qty')";

        if (!mysqli_query($con, $query)) {
            throw new Exception("Error inserting inventory: " . mysqli_error($con));
        }

        // Generate a column name for the item in the products table
        $item_column = str_replace(' ', '_', $item);
        $alter_query = "ALTER TABLE products ADD `$item_column` VARCHAR(255) DEFAULT '0';";

        if (!mysqli_query($con, $alter_query)) {
            throw new Exception("Error altering products table: " . mysqli_error($con));
        }

        // Commit the transaction
        mysqli_commit($con);
        header("Location: ../endpoint/add_inventory_button.php?success=1");
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($con);
        header("Location: ../endpoint/add_inventory_button.php?error=" . $e->getMessage());
    }
} else {
    header("Location: ../endpoint/add_inventory_button.php");
}

mysqli_close($con);
