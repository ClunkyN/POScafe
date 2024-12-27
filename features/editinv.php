<?php
session_start();
include('connection.php');
$id = $_POST['id'];
$a = $_POST['item'];
$n = $_POST['nitem'];
$b = $_POST['qty'];

$query = "UPDATE inventory SET item='$n', qty='$b' WHERE id='$id'";
mysqli_query($con, $query);

$a1 = str_replace(' ', '_', $a);
$n1 = str_replace(' ', '_', $n);

$query1 = "ALTER TABLE products CHANGE $a1 $n1 varchar(255)";
//"ALTER TABLE `products` CHANGE `try_wie` `try` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;"
mysqli_query($con, $query1);
header("location: inventory.php");