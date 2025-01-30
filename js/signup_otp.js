document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reuse existing validation
        if (!validateForm()) {
            alert('Please fill all required fields correctly');
            return;
        }
        
        const formData = new FormData(this);
        const userData = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch('../endpoint/otp_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'generate',
                    userData: userData
                })
            });
            
            const data = await response.json();
            if (data.success) {
                // Show OTP modal
                document.getElementById('otpModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                alert(data.message || 'Failed to send verification code');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    });
});

// Import validation functions from signup.js
function validateForm() {
    const password = document.getElementById('password').value;
    const cpassword = document.getElementById('cpassword').value;
    const termsAccepted = document.getElementById('terms').checked;

    // Basic validation
    if (!password || !cpassword || !termsAccepted) {
        return false;
    }

    // Password requirements
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    const isLongEnough = password.length >= 8;
    
    if (!(hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar && isLongEnough)) {
        return false;
    }

    // Check if passwords match
    if (password !== cpassword) {
        return false;
    }

    return true;
}

async function verifyOTP() {
    const otp = document.getElementById('otpInput').value;
    if (!otp || otp.length !== 6) {
        alert('Please enter valid 6-digit code');
        return;
    }
    
    try {
        const formData = new FormData(document.getElementById('signupForm'));
        const userData = Object.fromEntries(formData.entries());
        
        const response = await fetch('../endpoint/otp_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'verify',
                otp: otp,
                userData: userData
            })
        });
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        console.log('Verification response:', data);
        
        if (data.success) {
            alert('Registration successful! Please login to continue.');
            window.location.href = '../features/employee_login.php';
        } else {
            alert(data.message || 'Verification failed');
        }
    } catch (error) {
        console.error('Verification Error:', error);
        alert('Error during verification. Please check console for details.');
    }
}

async function resendOTP() {
    const formData = new FormData(document.getElementById('signupForm'));
    const userData = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('../endpoint/otp_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'generate',
                userData: userData
            })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('New verification code sent');
        } else {
            alert(data.message || 'Failed to send new code');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}
