<?php
require_once __DIR__ . '/connect.php';

function redirect_return_page($message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_return_page('Invalid request.', true);
}

$requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
$reviewRemarks = trim($_POST['review_remarks'] ?? '');
$reviewedById = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : 0;
$reviewedByName = $_SESSION['Full_NameSRF'] ?? ($_SESSION['usernameSRF'] ?? 'System');
$role = $_SESSION['User_RoleSRF'] ?? '';

if ($requestId <= 0 || $reviewRemarks === '') {
    redirect_return_page('Disapproval remarks are required.', true);
}

$conn->begin_transaction();

try {
    $requestStmt = $conn->prepare("SELECT * FROM inv_return_requests WHERE id = ? AND status = 'Pending' FOR UPDATE");
    $requestStmt->bind_param('i', $requestId);
    $requestStmt->execute();
    $request = $requestStmt->get_result()->fetch_assoc();

    if (!$request) {
        throw new Exception('Pending return request was not found.');
    }

    if ($role !== 'Super_admin' && (int) $request['assigned_to_id'] !== $reviewedById) {
        throw new Exception('You are not allowed to disapprove this return request.');
    }

    $updateStmt = $conn->prepare("UPDATE inv_return_requests SET status = 'Disapproved', reviewed_by_id = ?, reviewed_by_name = ?, reviewed_at = CURRENT_TIMESTAMP, review_remarks = ? WHERE id = ?");
    $updateStmt->bind_param('issi', $reviewedById, $reviewedByName, $reviewRemarks, $requestId);

    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        throw new Exception('Unable to disapprove return request.');
    }

    $conn->commit();
    redirect_return_page('Return request disapproved. Equipment remains in active inventory.');
} catch (Throwable $e) {
    $conn->rollback();
    redirect_return_page('Disapproval failed: ' . $e->getMessage(), true);
}
?>
