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

                    <div class="text-left">
                        <a href="#" onclick="showAdminForgotPasswordModal()" class="text-sm text-blue-600 hover:underline">
                            Forgot Password?
                        </a>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Login as Admin
                        </button>
                    </div>

                    <div class="text-start">
                        <a href="../features/employee_login.php" class="text-blue-700 hover:underline">
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

    <!-- Email Modal -->
    <div id="adminEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Forgot Password</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Enter your email address to receive OTP
                    </p>
                    <input type="email" id="adminResetEmail"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Enter your email">
                </div>
                <div class="items-center px-4 py-3">
                    <button id="sendOtpBtn" onclick="sendAdminOTP()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Send OTP
                    </button>
                    <button onclick="closeAdminEmailModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div id="adminOtpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Enter OTP</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Enter the OTP sent to your email
                    </p>
                    <input type="text" id="adminOtpInput" maxlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Enter 6-digit OTP">
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="verifyAdminOTP()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Verify OTP
                    </button>
                    <button onclick="closeAdminOtpModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="adminResetPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Reset Password</h3>
                <div class="mt-2 px-7 py-3">
                    <input type="password" id="adminNewPassword"
                        class="w-full px-3 py-2 mb-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="New Password">
                    <input type="password" id="adminConfirmNewPassword"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Confirm New Password">
                    <div id="adminPasswordStrength" class="mt-2 text-sm"></div>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="resetAdminPassword()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Reset Password
                    </button>
                    <button onclick="closeAdminResetPasswordModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let adminEmail = '';

        function showAdminForgotPasswordModal() {
            document.getElementById('adminEmailModal').classList.remove('hidden');
        }

        function closeAdminEmailModal() {
            document.getElementById('adminEmailModal').classList.add('hidden');
        }

        function closeAdminOtpModal() {
            document.getElementById('adminOtpModal').classList.add('hidden');
        }

        function closeAdminResetPasswordModal() {
            document.getElementById('adminResetPasswordModal').classList.add('hidden');
        }

        async function sendAdminOTP() {
            const email = document.getElementById('adminResetEmail').value;
            if (!email) {
                alert('Please enter your email');
                return;
            }
            adminEmail = email;

            try {
                const response = await fetch('../endpoint/send_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}&role=admin`
                });

                const data = await response.json();
                if (data.success) {
                    closeAdminEmailModal();
                    document.getElementById('adminOtpModal').classList.remove('hidden');
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error sending OTP');
            }
        }

        async function verifyAdminOTP() {
            const otp = document.getElementById('adminOtpInput').value;
            if (!otp || otp.length !== 6) {
                alert('Please enter valid 6-digit OTP');
                return;
            }

            try {
                const response = await fetch('../endpoint/verify_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(adminEmail)}&otp=${encodeURIComponent(otp)}&role=admin`
                });

                const data = await response.json();
                if (data.success) {
                    closeAdminOtpModal();
                    document.getElementById('adminResetPasswordModal').classList.remove('hidden');
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error verifying OTP');
            }
        }

        async function resetAdminPassword() {
            const newPassword = document.getElementById('adminNewPassword').value;
            const confirmPassword = document.getElementById('adminConfirmNewPassword').value;

            if (!isPasswordValid(newPassword)) {
                alert('Password must meet all requirements');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            try {
                const response = await fetch('../endpoint/reset_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(adminEmail)}&password=${encodeURIComponent(newPassword)}&role=admin`
                });

                const data = await response.json();
                if (data.success) {
                    alert('Password reset successful');
                    closeAdminResetPasswordModal();
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error resetting password');
            }
        }

        function isPasswordValid(password) {
            return password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>]/.test(password);
        }

        document.getElementById('adminNewPassword').addEventListener('keyup', function() {
            const password = this.value;
            let strength = 0;
            let message = [];

            if (password.length >= 8) strength++;
            else message.push('At least 8 characters');

            if (/[A-Z]/.test(password)) strength++;
            else message.push('One uppercase letter');

            if (/[a-z]/.test(password)) strength++;
            else message.push('One lowercase letter');

            if (/[0-9]/.test(password)) strength++;
            else message.push('One number');

            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            else message.push('One special character');

            const strengthDiv = document.getElementById('adminPasswordStrength');
            let strengthText = '';
            let strengthColor = '';

            switch (strength) {
                case 0:
                    strengthColor = '#ff0000';
                    strengthText = 'Very Weak';
                    break;
                case 1:
                    strengthColor = '#ff4500';
                    strengthText = 'Weak';
                    break;
                case 2:
                    strengthColor = '#ffa500';
                    strengthText = 'Fair';
                    break;
                case 3:
                    strengthColor = '#9acd32';
                    strengthText = 'Good';
                    break;
                case 4:
                    strengthColor = '#90ee90';
                    strengthText = 'Strong';
                    break;
                case 5:
                    strengthColor = '#008000';
                    strengthText = 'Very Strong';
                    break;
            }

            strengthDiv.innerHTML = `
                <div style="width: ${(strength/5)*100}%; background-color: ${strengthColor}; height: 5px; transition: all 0.3s;"></div>
                <div class="text-sm mt-1">${strengthText}</div>
                <div class="text-sm text-gray-600">${message.length ? 'Required: ' + message.join(', ') : ''}</div>
            `;
        });
    </script>
</body>

</html>