<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';
require_once 'connect_otos.php';
require_once 'role_access.php';

header('Content-Type: application/json; charset=UTF-8');

function sendRoleJson($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== ($_SESSION['usernameSRF'] ?? null)) {
    sendRoleJson(['success' => false, 'message' => 'Your session has expired. Please log in again.'], 401);
}

$currentRole = $_SESSION['User_RoleSRF'] ?? '';
if (!amsos_can_manage_configuration($currentRole)) {
    sendRoleJson(['success' => false, 'message' => 'You do not have permission to manage user roles.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendRoleJson(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$userId = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$newRole = trim((string)($_POST['role'] ?? ''));

if ($userId <= 0 || $newRole === '') {
    sendRoleJson(['success' => false, 'message' => 'User and role are required.'], 422);
}

$stmt = $conn_otos->prepare('SELECT id, Full_Name, Office, User_Role FROM useremployee WHERE id = ? LIMIT 1');
if (!$stmt) {
    sendRoleJson(['success' => false, 'message' => 'Unable to prepare user lookup.'], 500);
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    sendRoleJson(['success' => false, 'message' => 'User account was not found.'], 404);
}

$office = trim((string)$user['Office']);
$baseOtosRole = trim((string)$user['User_Role']);

if (!amsos_ensure_user_roles_table($conn)) {
    sendRoleJson(['success' => false, 'message' => 'Unable to initialize the AMSOS role table.'], 500);
}

if (!amsos_is_valid_chief_role_for_office($newRole, $office)) {
    if (amsos_role_key($newRole) === 'DIVISIONCHIEF') {
        sendRoleJson(['success' => false, 'message' => 'Division Chief can only be assigned to Regional Office accounts.'], 422);
    }

    if (amsos_role_key($newRole) === 'SECTIONCHIEF') {
        sendRoleJson(['success' => false, 'message' => 'Section Chief can only be assigned to PENRO or CENRO accounts.'], 422);
    }

    sendRoleJson(['success' => false, 'message' => 'The selected role is not valid for this office.'], 422);
}

$knownRoles = [];
$rolesResult = $conn_otos->query("SELECT DISTINCT User_Role FROM useremployee WHERE User_Role IS NOT NULL AND TRIM(User_Role) <> ''");
if ($rolesResult) {
    while ($roleRow = $rolesResult->fetch_assoc()) {
        $roleValue = trim((string)$roleRow['User_Role']);
        if ($roleValue !== '') {
            $knownRoles[amsos_role_key($roleValue)] = $roleValue;
        }
    }
}

$roleKey = amsos_role_key($newRole);
if (!isset($knownRoles[$roleKey]) && !in_array($roleKey, ['DIVISIONCHIEF', 'SECTIONCHIEF'], true)) {
    sendRoleJson(['success' => false, 'message' => 'Unknown role selected.'], 422);
}

if ($roleKey === 'DIVISIONCHIEF') {
    $newRole = 'Division Chief';
} elseif ($roleKey === 'SECTIONCHIEF') {
    $newRole = 'Section Chief';
} elseif (isset($knownRoles[$roleKey])) {
    $newRole = $knownRoles[$roleKey];
}

$effectiveRole = $newRole;
$updatedBy = (int)($_SESSION['idSRF'] ?? 0);

if (amsos_role_key($newRole) === amsos_role_key($baseOtosRole)) {
    $deleteStmt = $conn->prepare('DELETE FROM amsos_user_roles WHERE otos_user_id = ?');
    if (!$deleteStmt) {
        sendRoleJson(['success' => false, 'message' => 'Unable to prepare the AMSOS role reset.'], 500);
    }
    $deleteStmt->bind_param('i', $userId);
    $deleteStmt->execute();
    $deleteStmt->close();
    $effectiveRole = $baseOtosRole;
} else {
    $updateStmt = $conn->prepare(
        'INSERT INTO amsos_user_roles (otos_user_id, role, updated_by)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE role = VALUES(role), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP'
    );

    if (!$updateStmt) {
        sendRoleJson(['success' => false, 'message' => 'Unable to prepare the AMSOS role update.'], 500);
    }

    $updateStmt->bind_param('isi', $userId, $newRole, $updatedBy);

    if (!$updateStmt->execute()) {
        $updateStmt->close();
        sendRoleJson(['success' => false, 'message' => 'Unable to update the AMSOS user role.'], 500);
    }

    $updateStmt->close();
}

if ((int)($_SESSION['idSRF'] ?? 0) === $userId) {
    $_SESSION['User_RoleSRF'] = $effectiveRole;
}

$conn->close();
$conn_otos->close();

sendRoleJson([
    'success' => true,
    'message' => 'Role updated successfully.',
    'user_id' => $userId,
    'full_name' => $user['Full_Name'],
    'office' => $office,
    'role' => $effectiveRole,
]);
?>