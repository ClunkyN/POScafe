<?php
session_start();
include "../conn/connection.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and has new_user role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'new_user') {
    session_unset();
    session_destroy();
    header("Location: ../features/login.php");
    exit();
}

// Double check new_user role from database
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM user_db WHERE user_id = ? AND role = 'new_user'";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../features/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending - POSCafe</title>
    <link rel="stylesheet" href="../src/output.css">
    <script>
        // Prevent going back
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, document.URL);
        });
    </script>
</head>

<body class="bg-[#FFF0DC] flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full flex flex-col items-center justify-center text-center space-y-6">
        <h1 class="text-2xl font-bold text-gray-800">Account Pending Approval</h1>

        <img src="../assets/denied_access.svg" alt="Logo" class="w-64 h-64 object-contain">

        <p class="text-gray-600 max-w-lg text-lg">
            Your account is currently pending administrator approval. Please contact your system administrator for account activation.
        </p>

        <div class="w-full pt-6 border-t flex justify-center">
            <form action="../endpoint/employee_logout.php" method="post" class="w-full flex justify-center">
                <button type="submit"
                    class="bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-6 rounded-md transition duration-200 w-1/2">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</body>

</html>