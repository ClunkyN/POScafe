<?php
session_start();
include('../conn/connection.php');
	$id=$_GET['id'];
	$query = "DELETE FROM inventory WHERE item= '$id'";
    mysqli_query($con, $query);

    $ids = str_replace(' ', '_', $id);
    $query1 ="ALTER TABLE products DROP COLUMN $ids ;";
    mysqli_query($con, $query1);

    header("location: inventory.php");
?>