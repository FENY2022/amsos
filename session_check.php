<?php
session_start();
header('Content-Type: application/json');

// Check if the specific session variable exists
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'expired']);
    exit;
}

// Session is still active
echo json_encode(['status' => 'active']);

?> 

