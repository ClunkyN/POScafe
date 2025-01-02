<?php
session_start();
include("../conn/connection.php");
$a = $_POST['item'];
$b = $_POST['qty'];

$query = "INSERT INTO inventory (item,qty) VALUES ('$a','$b')";
mysqli_query($con, $query);

$a2 = str_replace(' ', '_', $a);

$query1 = "ALTER TABLE products ADD $a2 varchar(255) DEFAULT 0;";
mysqli_query($con, $query1);

header("location: ../features/inventory.php");
?>