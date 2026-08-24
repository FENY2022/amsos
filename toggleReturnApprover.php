<?php
require_once __DIR__ . '/connect.php';

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

$approverId = isset($_POST['approver_id']) ? (int) $_POST['approver_id'] : 0;
$isActive = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 0;
$isActive = $isActive === 1 ? 1 : 0;

if ($approverId <= 0) {
    redirect_approver('Invalid approver.', true);
}

$stmt = $conn->prepare('UPDATE inv_return_approvers SET is_active = ?, is_default = IF(? = 0, 0, is_default) WHERE id = ?');
$stmt->bind_param('iii', $isActive, $isActive, $approverId);

if ($stmt->execute()) {
    redirect_approver('Return approver status updated.');
}

redirect_approver('Unable to update return approver: ' . $conn->error, true);
?>
