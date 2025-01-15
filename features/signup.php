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
    if(mysqli_stmt_num_rows($stmt) > 0) {
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
                <?php if(!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php 
                            foreach($error_message as $error) {
                                echo $error . "<br>";
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-4">
                    <div>
                        <label for="user_name" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text"
                            name="user_name"
                            id="user_name"
                            placeholder=""
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>
                    <div class="flex space-x-4 mb-4">
                        <div class="w-1/2">
                            <label for="fname" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text"
                                name="fname"
                                id="fname"
                                placeholder=""
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                        </div>

                        <div class="w-1/2">
                            <label for="lname" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text"
                                name="lname"
                                id="lname"
                                placeholder=""
                                required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email"
                            name="email"
                            id="email"
                            placeholder=""
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password"
                            name="password"
                            id="password"
                            placeholder=""
                            required
                            onkeyup="checkPasswordStrength()"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                        <div class="strength mt-1" id="strength-bar">
                            <span></span>
                        </div>
                    </div>

                    <div>
                        <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password"
                            name="cpassword"
                            id="cpassword"
                            placeholder=""
                            required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#C2A47E] focus:border-[#C2A47E]">
                    </div>

                    <div>
                        <button type="submit"
                            class="w-full bg-[#6E6A43] hover:bg-[#C2A47E] text-white font-bold py-2 px-4 rounded-md transition duration-200">
                            Sign up
                        </button>
                    </div>

                    <div class="text-start flex">
                        <p>Have an account? </p>
                        <a class="pl-2 underline text-blue-700" href="../features/employee_login.php" >Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<!-- Add this JavaScript before closing body tag -->
<script>
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strength-bar');
    const confirmPassword = document.getElementById('cpassword');
    let strength = 0;
    let messages = [];

    // Reset strength bar
    strengthBar.innerHTML = '';

    // Check length
    if (password.length >= 8) {
        strength += 1;
    } else {
        messages.push('At least 8 characters');
    }

    // Check uppercase
    if (password.match(/[A-Z]/)) {
        strength += 1;
    } else {
        messages.push('One uppercase letter');
    }

    // Check lowercase
    if (password.match(/[a-z]/)) {
        strength += 1;
    } else {
        messages.push('One lowercase letter');
    }

    // Check numbers
    if (password.match(/[0-9]/)) {
        strength += 1;
    } else {
        messages.push('One number');
    }

    // Check special characters
    if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) {
        strength += 1;
    } else {
        messages.push('One special character');
    }

    // Update strength bar
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

    strengthBar.innerHTML = `
        <div style="width: ${(strength/5)*100}%; background-color: ${strengthColor}; height: 5px; transition: all 0.3s;"></div>
        <div class="text-sm mt-1">${strengthText}</div>
        <div class="text-sm text-gray-600">${messages.length ? 'Required: ' + messages.join(', ') : ''}</div>
    `;

    // Check password match
    if (confirmPassword.value) {
        if (password === confirmPassword.value) {
            confirmPassword.style.borderColor = '#008000';
        } else {
            confirmPassword.style.borderColor = '#ff0000';
        }
    }
}

// Add event listeners
document.getElementById('password').addEventListener('keyup', checkPasswordStrength);
document.getElementById('cpassword').addEventListener('keyup', checkPasswordStrength);
</script>