<?php
include "../conn/connection.php";
header('Content-Type: application/json');

if(isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($con, $_GET['user_id']);
    $query = "SELECT * FROM user_db WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        echo json_encode(['error' => 'User not found']);
    }
}
mysqli_close($con);
?>