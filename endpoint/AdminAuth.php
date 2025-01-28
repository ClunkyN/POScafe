<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check multiple session variables for role
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['role']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['user_role'] !== 'admin')) {
    session_unset();
    session_destroy();
    header("Location: ../features/admin_login.php");
    exit();
}
?>