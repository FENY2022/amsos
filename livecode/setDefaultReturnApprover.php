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

if ($approverId <= 0) {
    redirect_approver('Invalid approver.', true);
}

$conn->begin_transaction();

try {
    $checkStmt = $conn->prepare('SELECT id, full_name FROM inv_return_approvers WHERE id = ? AND is_active = 1 FOR UPDATE');
    $checkStmt->bind_param('i', $approverId);
    $checkStmt->execute();
    $approver = $checkStmt->get_result()->fetch_assoc();

    if (!$approver) {
        throw new Exception('Active approver was not found.');
    }

    $conn->query('UPDATE inv_return_approvers SET is_default = 0');
    $updateStmt = $conn->prepare('UPDATE inv_return_approvers SET is_default = 1 WHERE id = ?');
    $updateStmt->bind_param('i', $approverId);
    $updateStmt->execute();

    $conn->commit();
    redirect_approver($approver['full_name'] . ' is now the default return approver.');
} catch (Throwable $e) {
    $conn->rollback();
    redirect_approver('Unable to set default approver: ' . $e->getMessage(), true);
}
?>
