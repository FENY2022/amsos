<?php
require_once __DIR__ . '/connect.php';

function redirect_return_page($message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

function redirect_return_print_page($message, $returnedEquipmentId) {
    $_SESSION['success_message'] = $message;
    header('Location: mainmenu.php?dir=returnedequipment&print_return_id=' . (int) $returnedEquipmentId);
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

if ($requestId <= 0) {
    redirect_return_page('Invalid return request.', true);
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
        throw new Exception('You are not allowed to approve this return request.');
    }

    $inventoryId = (int) $request['inventory_id'];
    $inventoryStmt = $conn->prepare('SELECT id FROM inv_inventory WHERE id = ? FOR UPDATE');
    $inventoryStmt->bind_param('i', $inventoryId);
    $inventoryStmt->execute();

    if ($inventoryStmt->get_result()->num_rows === 0) {
        throw new Exception('Inventory record was not found or already moved.');
    }

    $insertSql = "INSERT INTO inv_returned_equipment (
            original_inventory_id, employeeName, employee_person_id, equipmentType, computer_specs,
            yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled,
            licensingModel, softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done,
            inventory_created_at, inventory_updated_at, return_status, return_reason, returned_by
        )
        SELECT
            id, employeeName, employee_person_id, equipmentType, computer_specs,
            yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled,
            licensingModel, softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done,
            created_at, updated_at, 'Returned', ?, ?
        FROM inv_inventory
        WHERE id = ?";

    $approvedReturnReason = $request['return_reason'];
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('ssi', $approvedReturnReason, $reviewedByName, $inventoryId);

    if (!$insertStmt->execute() || $insertStmt->affected_rows !== 1) {
        throw new Exception('Unable to move equipment to returned equipment.');
    }

    $returnedEquipmentId = $conn->insert_id;
    $deleteStmt = $conn->prepare('DELETE FROM inv_inventory WHERE id = ?');
    $deleteStmt->bind_param('i', $inventoryId);

    if (!$deleteStmt->execute() || $deleteStmt->affected_rows !== 1) {
        throw new Exception('Unable to remove equipment from active inventory.');
    }

    $updateStmt = $conn->prepare("UPDATE inv_return_requests SET status = 'Approved', reviewed_by_id = ?, reviewed_by_name = ?, reviewed_at = CURRENT_TIMESTAMP, review_remarks = ?, returned_equipment_id = ? WHERE id = ?");
    $updateStmt->bind_param('issii', $reviewedById, $reviewedByName, $reviewRemarks, $returnedEquipmentId, $requestId);

    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        throw new Exception('Unable to update request status.');
    }

    $conn->commit();
    redirect_return_print_page('Return request approved. Equipment moved to Returned Equipment.', $returnedEquipmentId);
} catch (Throwable $e) {
    $conn->rollback();
    redirect_return_page('Approval failed: ' . $e->getMessage(), true);
}
?>
