<?php
session_start();
include "../conn/connection.php";

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: ../dashboard/admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Modified query to check for admin role
        $query = "SELECT * FROM user_db WHERE user_name = ? AND role = 'admin'";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);

            if (password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['user_id'];
                $_SESSION['role'] = 'admin'; // Set admin role in session
                $_SESSION['user_role'] = 'admin'; // Add this line
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        // Prevent back if logged in
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Force reload on first visit to prevent back
        window.onload = function() {
            if (!window.location.hash) {
                window.location = window.location + '#loaded';
                window.location.reload();
            }
        }
    </script>
</head>

<body class="bg-[#F2DBBE] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side - Login Form -->
        <div class="w-1/2 flex items-center justify-center bg-[#F2DBBE]">
            <div class="w-[400px]">
                <div class="text-center mb-8">
                    <h1 class="text-[128px] font-bold">ADMIN</h1>
                </div>

                <?php if (isset($error)): ?>
                    <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
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
                        <div class="relative group">
                            <input type="password"
                                name="password"
                                id="password"
                                required
                                onpaste="return false"
                                oncopy="return false"
                                class="mt-1 block w-full px-3 py-2 pr-12 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                            <button type="button"
                                onclick="togglePassword('password')"
                                class="absolute top-1/2 right-4 transform -translate-y-1/2">
                                <i class="fa fa-eye text-gray-500 hover:text-gray-700" id="password-toggle"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Login as Admin
                        </button>
                    </div>

                    <div class="text-start">
                        <a href="../features/employee_login.php" class="text-blue-700 underline">
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
    <script src="../js/errorTimer.js"></script>
    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(inputId + '-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>