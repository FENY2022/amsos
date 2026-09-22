<?php
header('Content-Type: application/json');

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$inventoryId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$officeDivision = trim($_POST['officeDivision'] ?? '');

if ($inventoryId <= 0 || $officeDivision === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing inventory ID or office division.']);
    exit;
}

$divisionExists = false;
$divisionStmt = $conn->prepare('SELECT 1 FROM office_divisions WHERE officeDivision = ? LIMIT 1');
if ($divisionStmt) {
    $divisionStmt->bind_param('s', $officeDivision);
    $divisionStmt->execute();
    $divisionStmt->store_result();
    $divisionExists = $divisionStmt->num_rows > 0;
    $divisionStmt->close();
}

if (!$divisionExists) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Selected office division is not valid.']);
    exit;
}

$stmt = $conn->prepare('UPDATE inv_inventory SET officeDivision = ? WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare update.']);
    exit;
}

$stmt->bind_param('si', $officeDivision, $inventoryId);
$success = $stmt->execute();
$stmt->close();

if (!$success) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update office division.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Office division updated.']);
