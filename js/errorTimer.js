      // Auto-hide error message after 3 seconds
      document.addEventListener('DOMContentLoaded', function() {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.classList.add('fade-out');
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 1000); // Wait for fade animation to complete
            }, 2000); // Show for 3 seconds
        }
    });