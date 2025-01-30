<?php
session_start();
include "../conn/connection.php";
include "../conn/function.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Trim input values
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $user_name = trim($_POST['user_name']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = 'new_user'; // Set default role

    // Validation flags
    $validationPassed = true;
    $error_message = [];

    // Check if fields are empty or contain only spaces
    if (empty($fname) || ctype_space($_POST['fname']) || strlen($fname) === 0) {
        $error_message[] = "First name cannot be empty or contain only spaces";
        $validationPassed = false;
    }

    if (empty($lname) || ctype_space($_POST['lname']) || strlen($lname) === 0) {
        $error_message[] = "Last name cannot be empty or contain only spaces";
        $validationPassed = false;
    }

    if (empty($user_name) || ctype_space($_POST['user_name']) || strlen($user_name) === 0) {
        $error_message[] = "Username cannot be empty or contain only spaces";
        $validationPassed = false;
    }

    // Username validation with no spaces allowed
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $user_name)) {
        $error_message[] = "Username can only contain letters, numbers, and underscores";
        $validationPassed = false;
    }

    // Check if username exists
    $stmt = mysqli_prepare($con, "SELECT user_name FROM user_db WHERE user_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $user_name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error_message[] = "Username already exists";
        $validationPassed = false;
    }

    // Email validation
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error_message[] = "Invalid email format";
        $validationPassed = false;
    }

    // Check if email exists 
    $stmt = mysqli_prepare($con, "SELECT email FROM user_db WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $_POST['email']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error_message[] = "Email already registered";
        $validationPassed = false;
    }

    // Password validation
    $passwordErrors = [];
    if (strlen($password) < 8) {
        $passwordErrors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $passwordErrors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $passwordErrors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $passwordErrors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $passwordErrors[] = "Password must contain at least one special character";
    }

    if (!empty($passwordErrors)) {
        $error_message = array_merge($error_message, $passwordErrors);
        $validationPassed = false;
    }

    // Check if passwords match
    if ($password !== $cpassword) {
        $error_message[] = "Passwords do not match";
        $validationPassed = false;
    }

    // If all validations pass, proceed with registration
    if ($validationPassed) {
        $h_password = password_hash($password, PASSWORD_DEFAULT);
        $user_id = random_num(20);

        $query = "INSERT INTO user_db (user_id, fname, lname, user_name, password, role, email) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);

        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($con));
            $error_message[] = "Registration failed. Please try again.";
        } else {
            mysqli_stmt_bind_param($stmt, "sssssss", $user_id, $fname, $lname, $user_name, $h_password, $role, $_POST['email']);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Account created successfully! Please login to continue.";
                header("Location: ./employee_login.php");
                exit();
            } else {
                error_log("Execute failed: " . mysqli_stmt_error($stmt));
                $error_message[] = "Registration failed: " . mysqli_stmt_error($stmt);
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
    <title>Sign Up - POSCafe</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/signup.js" defer></script>
    <script src="../js/signup_otp.js" defer></script>
</head>

<body class="bg-[#F2DBBE] min-h-screen">
    <div class="flex min-h-screen">
        <!-- Left Side - Logo -->
        <div class="w-1/2 bg-[#C2A47E] flex items-center justify-center">
            <img src="../assets/header_logo.svg" alt="Logo" class="w-2/3 h-2/3 object-contain">
        </div>

        <!-- Right Side - Signup Form -->
        <div class="w-1/2 flex items-center justify-center bg-[#F2DBBE]">
            <div class="w-[400px]">
                <div class="text-center mb-8">
                    <h1 class="text-[64px] font-bold">SIGN UP</h1>
                </div>

                <!-- Add this after the form title -->
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php
                        foreach ($error_message as $error) {
                            echo $error . "<br>";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form id="signupForm" method="post" class="space-y-4">
                    <div>
                        <label for="user_name" class="block text-sm font-medium text-gray-700">Username<span class="text-red-500 required-asterisk"> *</span></label>
                        <input type="text"
                            name="user_name"
                            id="user_name"
                            placeholder=""
                            value="<?php echo isset($_POST['user_name']) ? trim($_POST['user_name']) : ''; ?>"
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>
                    <div class="flex space-x-4 mb-4">
                        <div class="w-1/2">
                            <label for="fname" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500 required-asterisk"> *</span></label>
                            <input type="text"
                                name="fname"
                                id="fname"
                                placeholder=""
                                value="<?php echo isset($_POST['fname']) ? trim($_POST['fname']) : ''; ?>"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                        </div>

                        <div class="w-1/2">
                            <label for="lname" class="block text-sm font-medium text-gray-700">Last Name<span class="text-red-500 required-asterisk"> *</span></label>
                            <input type="text"
                                name="lname"
                                id="lname"
                                placeholder=""
                                value="<?php echo isset($_POST['lname']) ? trim($_POST['lname']) : ''; ?>"
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email<span class="text-red-500 required-asterisk"> *</span></label>
                        <input type="email"
                            name="email"
                            id="email"
                            placeholder=""
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password<span class="text-red-500 required-asterisk"> *</span></label>
                        <div class="relative group">
                            <input type="password"
                                name="password"
                                id="password"
                                placeholder=""
                                required
                                onkeyup="checkPasswordStrength()"
                                onpaste="return false"
                                oncopy="return false"
                                class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                            <button type="button"
                                onclick="togglePassword('password')"
                                class="absolute top-1/2 right-2 transform -translate-y-1/2">
                                <i class="fa fa-eye text-gray-500 hover:text-gray-700" id="password-toggle"></i>
                            </button>
                        </div>
                        <div class="strength mt-1" id="strength-bar">
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password<span class="text-red-500 required-asterisk"> *</span></label>
                        <div class="relative group">
                            <input type="password"
                                name="cpassword"
                                id="cpassword"
                                placeholder=""
                                required
                                class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                            <button type="button"
                                onclick="togglePassword('cpassword')"
                                onpaste="return false"
                                oncopy="return false"
                                class="absolute top-1/2 right-2 transform -translate-y-1/2">
                                <i class="fa fa-eye text-gray-500 hover:text-gray-700" id="cpassword-toggle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="terms" name="terms" required class="mr-2">
                        <label for="terms" class="text-sm">I agree to the <a href="#" onclick="showTermsModal(event)" class="text-blue-600 hover:underline">Terms and Conditions</a></label>
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Sign up
                        </button>
                    </div>

                    <div class="text-start flex">
                        <p>Have an account? </p>
                        <a class="pl-2 hover:underline text-blue-700" href="../features/employee_login.php">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="termsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="relative top-20 mx-auto p-6 border w-[600px] shadow-lg rounded-md bg-white">
            <h2 class="text-2xl font-bold mb-4">Terms and Conditions</h2>
            <div class="max-h-[400px] overflow-y-auto mb-4">
                <p class="mb-4">Welcome to Cafe POS System. By accessing and using this system, you agree to be bound by these terms and conditions:</p>

                <h3 class="font-bold mb-2">1. Account Security</h3>
                <p class="mb-4">You are responsible for maintaining the confidentiality of your account credentials. Any activities that occur under your account are your responsibility.</p>

                <h3 class="font-bold mb-2">2. Acceptable Use</h3>
                <p class="mb-4">You agree to use the system only for its intended purpose and in compliance with all applicable laws and regulations.</p>

                <h3 class="font-bold mb-2">3. Privacy</h3>
                <p class="mb-4">Your use of this system is also governed by our Privacy Policy. We respect your privacy and protect your personal information.</p>

                <h3 class="font-bold mb-2">4. System Access</h3>
                <p class="mb-4">Access to certain features may be restricted based on your user role and permissions.</p>

                <h3 class="font-bold mb-2">5. Modifications</h3>
                <p>We reserve the right to modify these terms at any time. Continued use of the system constitutes acceptance of modified terms.</p>
            </div>
            <div class="flex justify-end">
                <button onclick="closeTermsModal()" class="bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Add before closing body tag -->
    <div id="otpModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Email Verification</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">Please enter the verification code sent to your email</p>
                    <input type="text" id="otpInput" maxlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        placeholder="Enter 6-digit code">
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="verifyOTP()"
                        class="w-full bg-[#6E6A43] text-white py-2 px-4 rounded-md mb-2">
                        Verify
                    </button>
                    <button onclick="resendOTP()"
                        class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md">
                        Resend Code
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--TERMS AND CONDITIONS SCRIPT-->
    <script>
        function showTermsModal(event) {
            event.preventDefault();
            document.getElementById('termsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTermsModal() {
            document.getElementById('termsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('termsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTermsModal();
            }
        });
    </script>
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