<?php
header('Content-Type: application/json');

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$inventoryId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$employeeName = trim($_POST['employeeName'] ?? '');
$officeDivision = trim($_POST['officeDivision'] ?? '');

if ($inventoryId <= 0 || ($employeeName === '' && $officeDivision === '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing inventory ID or update value.']);
    exit;
}

if ($employeeName !== '') {
    $employeeExists = false;
    $employeeStmt = $conn->prepare('SELECT 1 FROM inv_inventory WHERE employeeName = ? LIMIT 1');
    if ($employeeStmt) {
        $employeeStmt->bind_param('s', $employeeName);
        $employeeStmt->execute();
        $employeeStmt->store_result();
        $employeeExists = $employeeStmt->num_rows > 0;
        $employeeStmt->close();
    }

    if (!$employeeExists) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Selected employee name is not valid.']);
        exit;
    }
}

if ($officeDivision !== '') {
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
}

$updates = [];
$types = '';
$params = [];

if ($employeeName !== '') {
    $updates[] = 'employeeName = ?';
    $types .= 's';
    $params[] = $employeeName;
}

if ($officeDivision !== '') {
    $updates[] = 'officeDivision = ?';
    $types .= 's';
    $params[] = $officeDivision;
}

$types .= 'i';
$params[] = $inventoryId;

$stmt = $conn->prepare('UPDATE inv_inventory SET ' . implode(', ', $updates) . ' WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare update.']);
    exit;
}

$stmt->bind_param($types, ...$params);
$success = $stmt->execute();
$stmt->close();

if (!$success) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update inventory row.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Inventory row updated.']);
