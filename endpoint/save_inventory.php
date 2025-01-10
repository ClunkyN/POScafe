<?php
session_start();
include "../conn/connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inventory_id = mysqli_real_escape_string($con, $_POST['id']); // Sanitize the ID
    $item = mysqli_real_escape_string($con, $_POST['item']);
    $qty = mysqli_real_escape_string($con, $_POST['qty']);

    // Insert inventory item into the inventory table
    $query = "INSERT INTO inventory (id, item, qty) 
              VALUES ('$inventory_id', '$item', '$qty')";

    if (mysqli_query($con, $query)) {
        // Generate a column name for the item in the products table
        $item_column = str_replace(' ', '_', $item);

        // Add the new column to the products table
        $alter_query = "ALTER TABLE products ADD `$item_column` VARCHAR(255) DEFAULT '0';";
        $alter_result = mysqli_query($con, $alter_query);

        if ($alter_result) {
            header("Location: ../endpoint/add_inventory_button.php?success=1");
        } else {
            header("Location: ../endpoint/add_inventory_button.php?error=" . mysqli_error($con));
        }
    } else {
        header("Location: ../endpoint/add_inventory_button.php?error=" . mysqli_error($con));
    }
} else {
    header("Location: ../endpoint/add_inventory_button.php");
}

mysqli_close($con);
?>
