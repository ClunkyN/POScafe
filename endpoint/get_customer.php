<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "SELECT * FROM customers WHERE id = '$id'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
} else {
    echo json_encode(['error' => 'ID parameter is missing']);
}

mysqli_close($con);
?>
