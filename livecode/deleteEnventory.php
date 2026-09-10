<?php
require_once __DIR__ . '/connect.php';

function finish_delete_inventory($message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    $target = $isError ? 'mainmenu.php?dir=edupdate' : 'mainmenu.php?dir=deletedinventory';
    echo '<script>window.top.location.href = "' . $target . '";</script>';
    exit();
}

function ensureDeletedInventorySchema(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS inv_deleted_inventory (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        original_inventory_id INT(11) NOT NULL,
        employeeName TEXT NOT NULL,
        employee_person_id INT(11) NULL,
        equipmentType TEXT NOT NULL,
        computer_specs TEXT NULL,
        yearAcquired TEXT NOT NULL,
        shelfLife TEXT NOT NULL,
        brand TEXT NOT NULL,
        specifications LONGTEXT NOT NULL,
        rangeCategory TEXT NOT NULL,
        softwareInstalled TEXT NOT NULL,
        licensingModel TEXT NOT NULL,
        softwareInstalled_2 TEXT NOT NULL,
        licensingModel_2 TEXT NOT NULL,
        serialNumber LONGTEXT NOT NULL,
        propertyNumber TEXT NOT NULL,
        accountablePerson TEXT NOT NULL,
        accountable_person_id INT(11) NULL,
        sex TEXT NOT NULL,
        officeDivision TEXT NOT NULL,
        statusOfEmployment TEXT NOT NULL,
        actualUser TEXT NOT NULL,
        actual_user_id INT(11) NULL,
        actualUserSex TEXT NOT NULL,
        actualUserStatusOfEmployment TEXT NOT NULL,
        natureOfWork TEXT NOT NULL,
        remarks LONGTEXT NOT NULL,
        office TEXT NOT NULL,
        office_id INT(11) NULL,
        amount INT(11) NOT NULL DEFAULT 0,
        depreciation_value INT(11) NOT NULL DEFAULT 0,
        mark_as_done TEXT NOT NULL,
        inventory_created_at TIMESTAMP NULL,
        inventory_updated_at TIMESTAMP NULL,
        delete_status ENUM('Deleted','Restored') NOT NULL DEFAULT 'Deleted',
        delete_reason TEXT NULL,
        deleted_by_id INT(11) NULL,
        deleted_by_name VARCHAR(255) NULL,
        deleted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        restored_by_id INT(11) NULL,
        restored_by_name VARCHAR(255) NULL,
        restored_at TIMESTAMP NULL,
        restore_inventory_id INT(11) NULL,
        INDEX idx_original_inventory_id (original_inventory_id),
        INDEX idx_delete_status (delete_status),
        INDEX idx_deleted_at (deleted_at),
        INDEX idx_restore_inventory_id (restore_inventory_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mainmenu.php?dir=edupdate');
    exit();
}

$inventoryId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$deleteReason = trim($_POST['delete_reason'] ?? '');
$deletedById = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : null;
$deletedByName = $_SESSION['Full_NameSRF'] ?? ($_SESSION['usernameSRF'] ?? 'System');

if ($inventoryId <= 0) {
    finish_delete_inventory('Invalid inventory record.', true);
}

ensureDeletedInventorySchema($conn);
$conn->begin_transaction();

try {
    $inventoryStmt = $conn->prepare('SELECT id FROM inv_inventory WHERE id = ? FOR UPDATE');
    $inventoryStmt->bind_param('i', $inventoryId);
    $inventoryStmt->execute();

    if ($inventoryStmt->get_result()->num_rows === 0) {
        throw new Exception('Inventory record was not found.');
    }

    $insertSql = "INSERT INTO inv_deleted_inventory (
            original_inventory_id, employeeName, employee_person_id, equipmentType, computer_specs,
            yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled,
            licensingModel, softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done,
            inventory_created_at, inventory_updated_at, delete_status, delete_reason, deleted_by_id, deleted_by_name
        )
        SELECT
            id, employeeName, employee_person_id, equipmentType, computer_specs,
            yearAcquired, shelfLife, brand, specifications, rangeCategory, softwareInstalled,
            licensingModel, softwareInstalled_2, licensingModel_2, serialNumber, propertyNumber,
            accountablePerson, accountable_person_id, sex, officeDivision, statusOfEmployment,
            actualUser, actual_user_id, actualUserSex, actualUserStatusOfEmployment, natureOfWork,
            remarks, office, office_id, amount, depreciation_value, mark_as_done,
            created_at, updated_at, 'Deleted', ?, ?, ?
        FROM inv_inventory
        WHERE id = ?";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('sisi', $deleteReason, $deletedById, $deletedByName, $inventoryId);

    if (!$insertStmt->execute() || $insertStmt->affected_rows !== 1) {
        throw new Exception('Unable to move inventory record to Deleted Inventory.');
    }

    $deleteStmt = $conn->prepare('DELETE FROM inv_inventory WHERE id = ?');
    $deleteStmt->bind_param('i', $inventoryId);

    if (!$deleteStmt->execute() || $deleteStmt->affected_rows !== 1) {
        throw new Exception('Unable to remove inventory record from active inventory.');
    }

    $conn->commit();
    finish_delete_inventory('Inventory record moved to Deleted Inventory.');
} catch (Throwable $e) {
    $conn->rollback();
    finish_delete_inventory('Delete failed: ' . $e->getMessage(), true);
}
?>
