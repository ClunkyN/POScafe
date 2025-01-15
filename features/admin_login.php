<?php
session_start();
include "../conn/connection.php";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    if(!empty($username) && !empty($password)) {
        // Modified query to check for admin role
        $query = "SELECT * FROM user_db WHERE user_name = ? AND role = 'admin'";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['user_id'];
                $_SESSION['role'] = 'admin'; // Set admin role in session
                header("Location: ../dashboard/admin_dashboard.php");
                die;
            }
        }
        $error = "Invalid admin credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - POSCafe</title>
    <link rel="stylesheet" href="../src/output.css">
</head>

<body class="bg-[#F2DBBE] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side - Login Form -->
        <div class="w-1/2 flex items-center justify-center bg-[#F2DBBE]">
            <div class="w-[400px]">
                <div class="text-center mb-8">
                    <h1 class="text-[128px] font-bold">ADMIN</h1>
                </div>

                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Admin Username</label>
                        <input type="text" 
                            name="username" 
                            id="username" 
                            required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" 
                            name="password" 
                            id="password" 
                            required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>

                    <div>
                        <button type="submit" 
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Login as Admin
                        </button>
                    </div>

                    <div class="text-start">
                        <a href="../features/login.php" class="text-blue-700 underline">
                            Back to regular login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Logo -->
        <div class="w-1/2 bg-[#C2A47E] flex items-center justify-center">
            <img src="../assets/header_logo.svg" alt="Logo" class="w-2/3 h-2/3 object-contain">
        </div>
    </div>
</body>
</html>