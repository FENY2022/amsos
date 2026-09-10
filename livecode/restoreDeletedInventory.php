<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mainmenu.php?dir=deletedinventory');
    exit();
}

$deletedId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$restoredById = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : null;
$restoredByName = $_SESSION['Full_NameSRF'] ?? ($_SESSION['usernameSRF'] ?? 'System');

if ($deletedId <= 0) {
    $_SESSION['error_message'] = 'Invalid deleted inventory record.';
    header('Location: mainmenu.php?dir=deletedinventory');
    exit();
}

$conn->begin_transaction();

try {
    $checkStmt = $conn->prepare("SELECT id FROM inv_deleted_inventory WHERE id = ? AND delete_status = 'Deleted' FOR UPDATE");
    $checkStmt->bind_param('i', $deletedId);
    $checkStmt->execute();

    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception('Deleted inventory record was not found or already restored.');
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
        FROM inv_deleted_inventory
        WHERE id = ? AND delete_status = 'Deleted'";

    $restoreStmt = $conn->prepare($restoreSql);
    $restoreStmt->bind_param('i', $deletedId);

    if (!$restoreStmt->execute() || $restoreStmt->affected_rows !== 1) {
        throw new Exception('Unable to restore inventory record.');
    }

    $newInventoryId = $conn->insert_id;
    $updateStmt = $conn->prepare("UPDATE inv_deleted_inventory SET delete_status = 'Restored', restored_by_id = ?, restored_by_name = ?, restored_at = CURRENT_TIMESTAMP, restore_inventory_id = ? WHERE id = ?");
    $updateStmt->bind_param('isii', $restoredById, $restoredByName, $newInventoryId, $deletedId);

    if (!$updateStmt->execute() || $updateStmt->affected_rows !== 1) {
        throw new Exception('Unable to update deleted inventory status.');
    }

    $conn->commit();
    $_SESSION['success_message'] = 'Inventory record restored successfully.';
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['error_message'] = 'Restore failed: ' . $e->getMessage();
}

header('Location: mainmenu.php?dir=deletedinventory');
exit();
?>
