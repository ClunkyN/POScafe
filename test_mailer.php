<?php
// 1. Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Try to load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    require 'vendor/autoload.php';
    
    // 3. Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // 4. Success message
    echo '<div style="color: green; padding: 20px;">
            PHPMailer is successfully installed!<br>
            Version: ' . PHPMailer::VERSION . '
          </div>';
          
} catch (Exception $e) {
    // 5. Error message
    echo '<div style="color: red; padding: 20px;">
            Error: PHPMailer is not properly installed.<br>
            Details: ' . $e->getMessage() . '
          </div>';
}
?>