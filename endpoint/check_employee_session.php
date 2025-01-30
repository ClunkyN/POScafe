<?php
session_start();
header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate");

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'employee') {
    $_SESSION['last_activity'] = time();
    echo json_encode(['status' => 'active']);
} else {
    echo json_encode(['status' => 'invalid']);
}