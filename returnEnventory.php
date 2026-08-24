<?php
require_once __DIR__ . '/connect.php';

function finish_return_request($message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    echo '<script>window.top.location.href = "mainmenu.php?dir=edupdate";</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mainmenu.php?dir=edupdate');
    exit();
}

$inventoryId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$returnReason = trim($_POST['return_reason'] ?? '');
$requestedById = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : null;
$requestedByName = $_SESSION['Full_NameSRF'] ?? ($_SESSION['usernameSRF'] ?? 'System');

if ($inventoryId <= 0) {
    finish_return_request('Invalid inventory record.', true);
}

$conn->begin_transaction();

try {
    $inventoryStmt = $conn->prepare('SELECT id FROM inv_inventory WHERE id = ? FOR UPDATE');
    $inventoryStmt->bind_param('i', $inventoryId);
    $inventoryStmt->execute();

    if ($inventoryStmt->get_result()->num_rows === 0) {
        throw new Exception('Inventory record was not found.');
    }

    $pendingStmt = $conn->prepare("SELECT id FROM inv_return_requests WHERE inventory_id = ? AND status = 'Pending' LIMIT 1");
    $pendingStmt->bind_param('i', $inventoryId);
    $pendingStmt->execute();

    if ($pendingStmt->get_result()->num_rows > 0) {
        throw new Exception('This equipment already has a pending return request.');
    }

    $approverStmt = $conn->prepare("SELECT user_id, full_name FROM inv_return_approvers WHERE is_active = 1 ORDER BY is_default DESC, id ASC LIMIT 1");
    $approverStmt->execute();
    $approver = $approverStmt->get_result()->fetch_assoc();

    if (!$approver) {
        throw new Exception('No active return approver is configured.');
    }

    $assignedToId = (int) $approver['user_id'];
    $assignedToName = $approver['full_name'];

    $requestStmt = $conn->prepare('INSERT INTO inv_return_requests (inventory_id, requested_by_id, requested_by_name, assigned_to_id, assigned_to_name, return_reason, status) VALUES (?, ?, ?, ?, ?, ?, "Pending")');
    $requestStmt->bind_param('iisiss', $inventoryId, $requestedById, $requestedByName, $assignedToId, $assignedToName, $returnReason);

    if (!$requestStmt->execute()) {
        throw new Exception('Unable to create return request.');
    }

    $conn->commit();
    finish_return_request('Return request submitted to ' . $assignedToName . ' for approval.');
} catch (Throwable $e) {
    $conn->rollback();
    finish_return_request('Return request failed: ' . $e->getMessage(), true);
}
?>
