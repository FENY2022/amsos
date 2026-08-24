<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

$returnedId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$restoredBy = $_SESSION['usernameSRF'] ?? 'System';

if ($returnedId <= 0) {
    $_SESSION['error_message'] = 'Invalid returned equipment record.';
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

$conn->begin_transaction();

try {
    $checkStmt = $conn->prepare("SELECT id FROM inv_returned_equipment WHERE id = ? AND return_status = 'Returned' FOR UPDATE");
    $checkStmt->bind_param('i', $returnedId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new Exception('Returned equipment was not found or already restored.');
    }

    $restoreSql = "INSERT INTO inv_inventory (
            employeeName, employee_person_id, equipmentType, computer_specs, yearAcquired, shelfLife,
            brand, specifications, rangeCategory, softwareInstalled, licensingModel,
            softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done, created_at, updated_at
        )
        SELECT
            employeeName, employee_person_id, equipmentType, computer_specs, yearAcquired, shelfLife,
            brand, specifications, rangeCategory, softwareInstalled, licensingModel,
            softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done,
            COALESCE(inventory_created_at, CURRENT_TIMESTAMP), CURRENT_TIMESTAMP
        FROM inv_returned_equipment
        WHERE id = ? AND return_status = 'Returned'";

    $restoreStmt = $conn->prepare($restoreSql);
    $restoreStmt->bind_param('i', $returnedId);

    if (!$restoreStmt->execute() || $restoreStmt->affected_rows !== 1) {
        throw new Exception('Unable to restore equipment to active inventory.');
    }

    $newInventoryId = $conn->insert_id;
    $updateStmt = $conn->prepare("UPDATE inv_returned_equipment SET return_status = 'Restored', restored_by = ?, restored_at = CURRENT_TIMESTAMP, restore_inventory_id = ? WHERE id = ?");
    $updateStmt->bind_param('sii', $restoredBy, $newInventoryId, $returnedId);

    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        throw new Exception('Unable to update returned equipment status.');
    }

    $conn->commit();
    $_SESSION['success_message'] = 'Equipment restored to active inventory successfully.';
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['error_message'] = 'Restore failed: ' . $e->getMessage();
}

header('Location: mainmenu.php?dir=returnedequipment');
exit();
?>
