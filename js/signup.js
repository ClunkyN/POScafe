document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    const termsCheckbox = document.getElementById('terms');
    
    // Disable button by default
    submitButton.disabled = true;
    submitButton.classList.add('opacity-50', 'cursor-not-allowed');

    async function validateForm() {
        const password = document.getElementById('password').value;
        const cpassword = document.getElementById('cpassword').value;
        const termsAccepted = termsCheckbox.checked;
        const emailValid = await checkEmail();

        // Password requirements
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /[0-9]/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const isLongEnough = password.length >= 8;
        
        const passwordValid = hasUpperCase && hasLowerCase && hasNumbers && 
                            hasSpecialChar && isLongEnough;
        const passwordsMatch = password === cpassword;

        // Enable/disable submit button
        if (passwordValid && passwordsMatch && termsAccepted && emailValid) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthBar = document.getElementById('strength-bar');
        let strength = 0;
        let messages = [];

        if (password.length >= 8) strength++;
        else messages.push('At least 8 characters');

        if (/[A-Z]/.test(password)) strength++;
        else messages.push('One uppercase letter');

        if (/[a-z]/.test(password)) strength++;
        else messages.push('One lowercase letter');

        if (/[0-9]/.test(password)) strength++;
        else messages.push('One number');

        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
        else messages.push('One special character');

        // Update strength bar UI
        strengthBar.innerHTML = `
            <div class="h-2 rounded-full" style="width: ${(strength/5)*100}%; 
                background-color: ${getStrengthColor(strength)}"></div>
            <div class="text-sm mt-1">${getStrengthText(strength)}</div>
            ${messages.length ? `<div class="text-sm text-red-500">Missing: ${messages.join(', ')}</div>` : ''}
        `;

        validateForm();
    }

    function getStrengthColor(strength) {
        switch(strength) {
            case 0: return '#ff0000';
            case 1: return '#ff4500';
            case 2: return '#ffa500';
            case 3: return '#9acd32';
            case 4: return '#90ee90';
            case 5: return '#008000';
            default: return '#ff0000';
        }
    }

    function getStrengthText(strength) {
        switch(strength) {
            case 0: return 'Very Weak';
            case 1: return 'Weak';
            case 2: return 'Fair';
            case 3: return 'Good';
            case 4: return 'Strong';
            case 5: return 'Very Strong';
            default: return 'Very Weak';
        }
    }

    async function checkEmail() {
        const emailInput = document.getElementById('email');
        const email = emailInput.value;
        
        try {
            const response = await fetch('../endpoint/check_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}`
            });
            
            const data = await response.json();
            const emailError = document.getElementById('email-error') || 
                             createEmailError();
            
            if (data.exists) {
                emailError.textContent = 'Email already exists';
                emailError.classList.remove('hidden');
                return false;
            } else {
                emailError.classList.add('hidden');
                return true;
            }
        } catch (error) {
            console.error('Error checking email:', error);
            return false;
        }
    }

    function createEmailError() {
        const emailInput = document.getElementById('email');
        const errorDiv = document.createElement('div');
        errorDiv.id = 'email-error';
        errorDiv.className = 'text-red-500 text-sm mt-1';
        emailInput.parentNode.appendChild(errorDiv);
        return errorDiv;
    }
    function setupRequiredFieldsAsterisks() {
        const requiredInputs = document.querySelectorAll('input[required]');
        
        requiredInputs.forEach(input => {
            // Find related asterisk
            const label = document.querySelector(`label[for="${input.id}"]`);
            const asterisk = label?.querySelector('.required-asterisk');
            
            if (asterisk) {
                // Check initial state
                if (input.value.trim() !== '') {
                    asterisk.style.display = 'none';
                }

                // Add input event listener
                input.addEventListener('input', () => {
                    asterisk.style.display = input.value.trim() === '' ? 'inline' : 'none';
                });
            }
        });
    }

    // Initialize asterisk handling
    setupRequiredFieldsAsterisks();
    // Event listeners
    document.getElementById('password').addEventListener('keyup', checkPasswordStrength);
    document.getElementById('cpassword').addEventListener('keyup', validateForm);
    termsCheckbox.addEventListener('change', validateForm);
    document.getElementById('email').addEventListener('blur', validateForm);
});