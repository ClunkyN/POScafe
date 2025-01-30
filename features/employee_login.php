<?php
session_start();
include "../conn/connection.php";

// Modified session check to prevent redirect loop
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'employee') {
        header("Location: ../dashboard/employee_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'new_user') {
        header("Location: ../dashboard/new_user.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    if (!empty($username) && !empty($password)) {
        // First check if user is archived
        $archive_check = "SELECT * FROM archive_users WHERE user_name = ?";
        $stmt = mysqli_prepare($con, $archive_check);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $archive_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($archive_result) > 0) {
            $error = "Account is Archived. Please contact your administrator.";
        } else {
            $query = "SELECT * FROM user_db WHERE user_name = ? AND (role = 'employee' OR role = 'new_user')";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);

                if (password_verify($password, $user_data['password'])) {
                    $_SESSION['user_id'] = $user_data['user_id'];
                    $_SESSION['role'] = $user_data['role'];

                    if ($user_data['role'] == 'new_user') {
                        header("Location: ../dashboard/new_user.php");
                    } else {
                        header("Location: ../dashboard/employee_dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        }
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/sessionMonitor.js"></script>
    <script>
        // Prevent back navigation after logout
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-[#F2DBBE] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side - Login Form -->
        <div class="w-1/2 flex items-center justify-center bg-[#F2DBBE]">
            <div class="w-[400px]">
                <div class="text-center mb-8">
                    <h1 class="text-[128px] font-bold">LOGIN</h1>
                </div>

                <?php if (isset($error)): ?>
                    <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div id="errorMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
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
                        <a href="#" onclick="showForgotPasswordModal()" class="text-sm text-blue-600 hover:underline">
                            Forgot Password?
                        </a>
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Sign In
                        </button>
                    </div>

                    <div class="text-start flex">
                        <p class="text-black">No account yet?</p> <a href="../features/signup.php" class=" pl-2 hover:underline text-blue-700"> Register here</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Image -->
        <div class="w-1/2 bg-[#C2A47E] flex items-center justify-center">
            <img src="../assets/header_logo.svg" alt="Logo" class="w-2/3 h-2/3 object-contain">
        </div>
    </div>

    <!-- Email Modal -->
    <div id="emailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Forgot Password</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Enter your email address to receive OTP
                    </p>
                    <input type="email" id="resetEmail"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Enter your email">
                </div>
                <div class="items-center px-4 py-3">
                    <button id="sendOtpBtn" onclick="sendOTP()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Send OTP
                    </button>
                    <button onclick="closeEmailModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Modal -->
    <div id="otpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Enter OTP</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Enter the OTP sent to your email
                    </p>
                    <input type="text" id="otpInput" maxlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Enter 6-digit OTP">
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="verifyOTP()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Verify OTP
                    </button>
                    <button onclick="closeOtpModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Reset Password</h3>
                <div class="mt-2 px-7 py-3">
                    <input type="password" id="newPassword"
                        class="w-full px-3 py-2 mb-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="New Password">
                    <input type="password" id="confirmNewPassword"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]"
                        placeholder="Confirm New Password">
                    <div id="passwordStrength" class="mt-2 text-sm"></div>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="resetPassword()"
                        class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                        Reset Password
                    </button>
                    <button onclick="closeResetPasswordModal()"
                        class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md transition duration-200">
                        Cancel
                    </button>
                </div>
            </div>
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
    <script>
        // Store user email globally for use across functions
        let userEmail = '';

        function showForgotPasswordModal() {
            document.getElementById('emailModal').classList.remove('hidden');
        }

        function closeEmailModal() {
            document.getElementById('emailModal').classList.add('hidden');
        }

        function closeOtpModal() {
            document.getElementById('otpModal').classList.add('hidden');
        }

        function closeResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
        }
        // OTP Request and Verification
        async function sendOTP() {
            const email = document.getElementById('resetEmail').value;
            if (!email) {
                alert('Please enter your email');
                return;
            }
            userEmail = email;

            try {
                const response = await fetch('../endpoint/send_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}`
                });

                const data = await response.json();
                if (data.success) {
                    closeEmailModal();
                    document.getElementById('otpModal').classList.remove('hidden');
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error sending OTP');
            }
        }
        // OTP Validation
        async function verifyOTP() {
            const otp = document.getElementById('otpInput').value;
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
                    body: `email=${encodeURIComponent(userEmail)}&otp=${encodeURIComponent(otp)}`
                });

                const data = await response.json();
                if (data.success) {
                    closeOtpModal();
                    document.getElementById('resetPasswordModal').classList.remove('hidden');
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error verifying OTP');
            }
        }
        // Password Reset Handler
        async function resetPassword() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmNewPassword').value;

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
                    body: `email=${encodeURIComponent(userEmail)}&password=${encodeURIComponent(newPassword)}`
                });

                const data = await response.json();
                if (data.success) {
                    alert('Password reset successful');
                    closeResetPasswordModal();
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                alert('Error resetting password');
            }
        }
        // Password Validation Rules
        function isPasswordValid(password) {
            return password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>]/.test(password);
        }

        // Add password strength check
        document.getElementById('newPassword').addEventListener('keyup', function() {
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

            const strengthDiv = document.getElementById('passwordStrength');
            let strengthText = '';
            let strengthColor = '';

            // Determine password strength level
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
            // Update strength indicator UI
            strengthDiv.innerHTML = `
            <div style="width: ${(strength/5)*100}%; background-color: ${strengthColor}; height: 5px; transition: all 0.3s;"></div>
            <div class="text-sm mt-1">${strengthText}</div>
            <div class="text-sm text-gray-600">${message.length ? 'Required: ' + message.join(', ') : ''}</div>
        `;
        });
    </script>
</body>

</html>