<?php
session_start();
include "../conn/connection.php";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']); // Changed from user_name to username
    $password = mysqli_real_escape_string($con, $_POST['password']);

    if(!empty($username) && !empty($password)) {
        $query = "select * from user_db where user_name = '$username'";
        $result = mysqli_query($con, $query);

        if($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['user_id'];
                header("Location: ../features/admin_dashboard.php");
                die;
            }
        }
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POSCafe</title>
    <link rel="stylesheet" href="../src/output.css">
</head>

<body class="bg-[#FFF0DC] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side - Login Form -->
        <div class="w-1/2 flex items-center justify-center bg-white">
            <div class="w-[400px] p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome Back!</h2>
                    <p class="text-gray-600">Please sign in to continue</p>
                </div>

                <?php if(isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
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
                            class="w-full bg-[#F0BB78] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="w-1/2 bg-[#C2A47E] flex items-center justify-center">
            <img src="../assets/header_logo.svg" alt="Logo" class="w-2/3 h-2/3 object-contain">
        </div>
    </div>
</body>
</html>