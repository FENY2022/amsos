<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";
require_once "role_access.php";

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== ($_SESSION['usernameSRF'] ?? null)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
    exit;
}

if (!amsos_can_manage_configuration($_SESSION['User_RoleSRF'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to manage AMSOS users.']);
    exit;
}

$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
$office = trim($_POST['office'] ?? '');
$officeDivision = trim($_POST['officeDivision'] ?? '');

if ($id <= 0 || $office === '' || $officeDivision === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required update data.']);
    exit;
}

$officeId = null;
$divisionStmt = $conn->prepare("SELECT id FROM office_divisions WHERE office = ? AND officeDivision = ? LIMIT 1");
$divisionStmt->bind_param('ss', $office, $officeDivision);
$divisionStmt->execute();
$divisionStmt->bind_result($existingOfficeId);
if ($divisionStmt->fetch()) {
    $officeId = (int)$existingOfficeId;
}
$divisionStmt->close();

if ($officeId === null) {
    $insertDivisionStmt = $conn->prepare("INSERT INTO office_divisions (office, officeDivision) VALUES (?, ?)");
    $insertDivisionStmt->bind_param('ss', $office, $officeDivision);
    $insertDivisionStmt->execute();
    $officeId = (int)$insertDivisionStmt->insert_id;
    $insertDivisionStmt->close();
}

$stmt = $conn->prepare("UPDATE inventory_people SET officeDivision = ?, office_id = ? WHERE id = ? AND office = ?");
$stmt->bind_param('siis', $officeDivision, $officeId, $id, $office);
$stmt->execute();
$updated = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

echo json_encode([
    'success' => $updated,
    'message' => $updated ? 'Office division updated.' : 'Unable to update office division.',
]);
?>
