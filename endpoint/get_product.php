<?php
include "../conn/connection.php";

$id = mysqli_real_escape_string($con, $_GET['id']);
$query = "SELECT * FROM products WHERE prod_id = '$id'";
$result = mysqli_query($con, $query);
$product = mysqli_fetch_assoc($result);

echo json_encode($product);
?>