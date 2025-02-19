function checkSessionStatus() {
    $.ajax({
        url: '../endpoint/check_session_status.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'archived') {
                alert(response.message);
                window.location.href = '../features/homepage.php';
            }
        },
        error: function(xhr, status, error) {
            console.error('Session check failed:', error);
        }
    });
}

// Check every 30 seconds
$(document).ready(function() {
    setInterval(checkSessionStatus, 5000);
});