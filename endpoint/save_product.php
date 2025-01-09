<?php
session_start();
include "../conn/connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prod_id = mysqli_real_escape_string($con, $_POST['prod_id']);
    $prod_name = mysqli_real_escape_string($con, $_POST['prod_name']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $status = $_POST['is_available'] == '1' ? 'Available' : 'Unavailable';

    $query = "INSERT INTO products (prod_id, prod_name, category, price, status) 
              VALUES ('$prod_id', '$prod_name', '$category', '$price', '$status')";

    if (mysqli_query($con, $query)) {
        header("Location: ../features/add_products.php?success=1");
    } else {
        header("Location: ../features/add_products.php?error=" . mysqli_error($con));
    }
} else {
    header("Location: ../features/add_products.php");
}

mysqli_close($con);
?>