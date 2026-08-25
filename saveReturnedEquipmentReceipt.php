<?php
require_once __DIR__ . '/connect.php';

function ensureReturnedReceiptSchema(mysqli $conn): void {
    $columns = [
        'return_receipt_no' => "VARCHAR(80) NULL",
        'returned_by_name' => "VARCHAR(255) NULL",
        'returned_by_position' => "VARCHAR(255) NULL",
        'returned_by_date' => "DATE NULL",
        'received_by_name' => "VARCHAR(255) NULL",
        'received_by_position' => "VARCHAR(255) NULL",
        'received_by_date' => "DATE NULL",
        'received_item_by_name' => "VARCHAR(255) NULL",
        'received_item_by_position' => "VARCHAR(255) NULL",
        'received_item_by_date' => "DATE NULL",
        'return_receipt_note' => "TEXT NULL",
    ];

    foreach ($columns as $column => $definition) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inv_returned_equipment' AND COLUMN_NAME = ?");
        $checkStmt->bind_param('s', $column);
        $checkStmt->execute();
        $exists = (int) ($checkStmt->get_result()->fetch_assoc()['total'] ?? 0);

        if ($exists === 0) {
            $conn->query("ALTER TABLE inv_returned_equipment ADD COLUMN `$column` $definition");
        }
    }
}

ensureReturnedReceiptSchema($conn);

function redirect_receipt($id, $message, $isError = false) {
    $_SESSION[$isError ? 'error_message' : 'success_message'] = $message;
    header('Location: printReturnedEquipment.php?id=' . (int) $id);
    exit();
}

function post_text($key) {
    return trim($_POST[$key] ?? '');
}

function post_date_or_null($key) {
    $value = trim($_POST[$key] ?? '');
    return $value === '' ? null : $value;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    header('Location: mainmenu.php?dir=returnedequipment');
    exit();
}

$returnReceiptNo = post_text('return_receipt_no');
$returnedByName = post_text('returned_by_name');
$returnedByPosition = post_text('returned_by_position');
$returnedByDate = post_date_or_null('returned_by_date');
$receivedByName = post_text('received_by_name');
$receivedByPosition = post_text('received_by_position');
$receivedByDate = post_date_or_null('received_by_date');
$receivedItemByName = post_text('received_item_by_name');
$receivedItemByPosition = post_text('received_item_by_position');
$receivedItemByDate = post_date_or_null('received_item_by_date');
$returnReceiptNote = post_text('return_receipt_note');

$stmt = $conn->prepare("UPDATE inv_returned_equipment SET
    return_receipt_no = ?,
    returned_by_name = ?,
    returned_by_position = ?,
    returned_by_date = ?,
    received_by_name = ?,
    received_by_position = ?,
    received_by_date = ?,
    received_item_by_name = ?,
    received_item_by_position = ?,
    received_item_by_date = ?,
    return_receipt_note = ?
    WHERE id = ?");

$stmt->bind_param(
    'sssssssssssi',
    $returnReceiptNo,
    $returnedByName,
    $returnedByPosition,
    $returnedByDate,
    $receivedByName,
    $receivedByPosition,
    $receivedByDate,
    $receivedItemByName,
    $receivedItemByPosition,
    $receivedItemByDate,
    $returnReceiptNote,
    $id
);

if (!$stmt->execute()) {
    redirect_receipt($id, 'Unable to save receipt details.', true);
}

redirect_receipt($id, 'Receipt details saved.');
?>
