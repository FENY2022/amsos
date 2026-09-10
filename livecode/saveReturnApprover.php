<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/connect_otos.php';

function redirect_approver($message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

if (($_SESSION['User_RoleSRF'] ?? '') !== 'Super_admin') {
    redirect_approver('Only Super_admin can manage return approvers.', true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_approver('Invalid request.', true);
}

$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

if ($userId <= 0) {
    redirect_approver('Please select a valid user.', true);
}

$userStmt = $conn_otos->prepare('SELECT id, Full_Name, username, Office, Station FROM useremployee WHERE id = ?');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    redirect_approver('Selected OTOS user was not found.', true);
}

$otosUserId = (int) $user['id'];
$fullName = $user['Full_Name'];
$username = $user['username'];
$office = $user['Office'];
$station = $user['Station'];

$stmt = $conn->prepare('INSERT INTO inv_return_approvers (user_id, full_name, username, office, station, is_default, is_active) VALUES (?, ?, ?, ?, ?, 0, 1) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), username = VALUES(username), office = VALUES(office), station = VALUES(station), is_active = 1');
$stmt->bind_param('issss', $otosUserId, $fullName, $username, $office, $station);

if ($stmt->execute()) {
    redirect_approver($fullName . ' added as return approver.');
}

redirect_approver('Unable to save return approver: ' . $conn->error, true);
?>
