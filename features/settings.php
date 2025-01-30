<?php
session_start();
include "../conn/connection.php";

// RBAC Check
if (
    !isset($_SESSION['user_id']) || !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employee')
) {
    session_unset();
    session_destroy();
    header("Location: ../features/homepage.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../src/output.css">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-[#FFF0DC]">
    <div class="relative z-50">
        <?php include '../features/component/topbar.php'; ?>
    </div>
    <div class="relative z-70">
        <?php include '../features/component/sidebar.php'; ?>
    </div>

    <main class="ml-[230px] mt-[171px] p-6">
        <div class="flex flex-col justify-between items-start mb-6">
            <h1 class="text-2xl font-bold mb-4">Change Password</h1>
        </div>
        <div class="bg-[#A88B68] p-6 rounded-lg w-[450px] text-white">
            <div class="bg-[#5A3A1B] p-6 rounded-lg">
                <form id="changePasswordForm">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Current Password:</label>
                        <div class="flex items-center">
                            <input type="password" 
                                id="currentPassword" 
                                name="currentPassword" 
                                class="w-full p-2 border rounded text-black" 
                                required />
                            <span><i class="fa fa-eye ms-2 text-white cursor-pointer" id="currentPassword-toggle" onclick="togglePassword('currentPassword')"></i></span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">New Password:</label>
                        <div class="flex items-center">
                            <input type="password" 
                                id="newPassword" 
                                name="newPassword" 
                                class="w-full p-2 border rounded text-black" 
                                required 
                                minlength="8"
                                onkeyup="checkPasswordStrength()" />
                            <span><i class="fa fa-eye ms-2 text-white cursor-pointer" id="newPassword-toggle" onclick="togglePassword('newPassword')"></i></span>
                        </div>
                        <div class="strength mt-1" id="strength-bar">
                            <span></span>
                        </div>
                        <p class="text-xs text-gray-300 mt-1">
                            Password must contain at least 8 characters with uppercase, lowercase, number and special character
                        </p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Confirm Password:</label>
                        <div class="flex items-center">
                            <input type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                class="w-full p-2 border rounded text-black" 
                                required />
                            <span><i class="fa fa-eye ms-2 text-white cursor-pointer" id="confirmPassword-toggle" onclick="togglePassword('confirmPassword')"></i></span>
                        </div>
                    </div>
                    
                    <!-- Error and Success Messages -->
                    <div id="errorMessage" class="hidden text-red-500 bg-red-100 p-2 rounded mb-2"></div>
                    <div id="successMessage" class="hidden text-green-500 bg-green-100 p-2 rounded mb-2"></div>
                    
                    <button type="submit" 
                        class="bg-[#F0BB78] hover:bg-[#C2A47E] text-white font-semibold px-4 py-2 rounded w-full">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
    function checkPasswordStrength() {
        const password = document.getElementById('newPassword').value;
        const strengthBar = document.getElementById('strength-bar');
        const confirmPassword = document.getElementById('confirmPassword');
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
            case 0: strengthColor = '#ff0000'; strengthText = 'Very Weak'; break;
            case 1: strengthColor = '#ff4500'; strengthText = 'Weak'; break;
            case 2: strengthColor = '#ffa500'; strengthText = 'Fair'; break;
            case 3: strengthColor = '#9acd32'; strengthText = 'Good'; break;
            case 4: strengthColor = '#90ee90'; strengthText = 'Strong'; break;
            case 5: strengthColor = '#008000'; strengthText = 'Very Strong'; break;
        }

        strengthBar.innerHTML = `
            <div style="width: ${(strength/5)*100}%; background-color: ${strengthColor}; height: 5px; transition: all 0.3s;"></div>
            <div class="text-sm mt-1 text-white">${strengthText}</div>
            <div class="text-sm text-gray-300">${messages.length ? 'Required: ' + messages.join(', ') : ''}</div>
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

    // Form submission
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        
        // Reset messages
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');
        
        // Validation
        if (newPassword !== confirmPassword) {
            errorMessage.textContent = 'Passwords do not match';
            errorMessage.classList.remove('hidden');
            return;
        }
        
        // Send to backend
        fetch('../endpoint/update_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                successMessage.textContent = 'Password updated successfully!';
                successMessage.classList.remove('hidden');
                
                // Show success alert and redirect to logout
                setTimeout(() => {
                    alert('Password changed successfully! Please login again.');
                    window.location.href = '../endpoint/employee_logout.php';
                }, 1000);
            } else {
                errorMessage.textContent = data.message || 'Error updating password';
                errorMessage.classList.remove('hidden');
            }
        })
        .catch(error => {
            errorMessage.textContent = 'An error occurred. Please try again.';
            errorMessage.classList.remove('hidden');
        });
    });

    // Add event listeners for password strength
    document.getElementById('newPassword').addEventListener('keyup', checkPasswordStrength);
    document.getElementById('confirmPassword').addEventListener('keyup', checkPasswordStrength);
    </script>
</body>
</html>
