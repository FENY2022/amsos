<?php
require_once __DIR__ . '/connect.php';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

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

function value_or_default($value, $default) {
    $value = trim((string) $value);
    return $value !== '' ? $value : $default;
}

function format_money($value) {
    return 'Php ' . number_format((float) $value, 2);
}

function format_date_display($value) {
    if (!$value) {
        return '';
    }

    return date('m/d/Y', strtotime($value));
}

function load_receipt_users(mysqli $conn): array {
    $tableCheck = $conn->query("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'useremployee'");
    $hasUserEmployee = (int) ($tableCheck->fetch_assoc()['total'] ?? 0) > 0;

    if (!$hasUserEmployee) {
        return [];
    }

    $users = [];
    $sql = "SELECT Full_Name, Position FROM useremployee WHERE Full_Name IS NOT NULL AND TRIM(Full_Name) <> '' ORDER BY Full_Name ASC LIMIT 1000";
    $result = $conn->query($sql);

    if (!$result) {
        return [];
    }

    while ($row = $result->fetch_assoc()) {
        $name = trim((string) ($row['Full_Name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $users[$name] = [
            'name' => $name,
            'position' => trim((string) ($row['Position'] ?? '')),
        ];
    }

    return array_values($users);
}

function render_name_options(array $users, $selectedName): void {
    $selectedName = trim((string) $selectedName);
    $hasSelected = $selectedName === '';
    echo '<option value="">Select name</option>';

    foreach ($users as $user) {
        $name = $user['name'];
        $position = $user['position'];
        $selected = strcasecmp($name, $selectedName) === 0;
        if ($selected) {
            $hasSelected = true;
        }

        echo '<option value="' . h($name) . '" data-position="' . h($position) . '"' . ($selected ? ' selected' : '') . '>' . h(strtoupper($name)) . '</option>';
    }

    if (!$hasSelected && $selectedName !== '') {
        echo '<option value="' . h($selectedName) . '" selected>' . h(strtoupper($selectedName)) . '</option>';
    }
}

ensureReturnedReceiptSchema($conn);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid returned equipment record.';
    exit();
}

$stmt = $conn->prepare('SELECT * FROM inv_returned_equipment WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();

if (!$record) {
    http_response_code(404);
    echo 'Returned equipment record was not found.';
    exit();
}

$amount = (float) ($record['amount'] ?? 0);
$isPpe = $amount > 50000;
$formTitle = $isPpe ? 'RECEIPT OF RETURN PROPERTY PLANT AND EQUIPMENT' : 'RECEIPT OF RETURNED SEMI-EXPENDABLE PROPERTY';
$receiptLabel = $isPpe ? 'RRPP&E No.' : 'RRSP No.';
$propertyTypeText = $isPpe ? 'Property Plant and Equipment' : 'Semi-Expendable Property';
$receiptNo = value_or_default($record['return_receipt_no'] ?? '', date('Y-m', strtotime($record['returned_at'])) . '-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT));
$returnedByName = value_or_default($record['returned_by_name'] ?? '', $record['accountablePerson'] ?: ($record['actualUser'] ?: $record['returned_by']));
$returnedByPosition = value_or_default($record['returned_by_position'] ?? '', '');
$returnedByDate = $record['returned_by_date'] ?? '';
$receivedByName = value_or_default($record['received_by_name'] ?? '', 'ANNABEL B. SALAZAR');
$receivedByPosition = value_or_default($record['received_by_position'] ?? '', 'Chief, GSS');
$receivedByDate = $record['received_by_date'] ?? '';
$receivedItemByName = value_or_default($record['received_item_by_name'] ?? '', 'RODELO L. TANUDTANUD');
$receivedItemByPosition = value_or_default($record['received_item_by_position'] ?? '', 'AO-I');
$receivedItemByDate = $record['received_item_by_date'] ?? '';
$receiptNote = value_or_default($record['return_receipt_note'] ?? '', 'Pool at GSS for disposal');
$itemDescription = trim((string) $record['equipmentType']);
$detailLines = array_filter([
    'brand: ' . $record['brand'],
    $record['specifications'] ? 'specs: ' . $record['specifications'] : '',
    $record['serialNumber'] ? 'sn: ' . $record['serialNumber'] : '',
    $record['propertyNumber'] ? 'Property No.: ' . $record['propertyNumber'] : '',
    $record['yearAcquired'] ? $record['yearAcquired'] : '',
    'Unit Cost : ' . format_money($amount),
]);
$remarks = value_or_default($record['return_reason'] ?? '', 'Unserviceable');
$endUser = value_or_default($record['officeDivision'] ?? '', $record['actualUser'] ?? '');
$receiptUsers = load_receipt_users($conn);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($formTitle); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #e5e7eb; color: #111827; font-family: "Times New Roman", Times, serif; }
        .toolbar { position: sticky; top: 0; z-index: 5; background: #fff; border-bottom: 1px solid #d1d5db; padding: 10px 16px; display: flex; gap: 8px; justify-content: flex-end; }
        .sheet { width: 8.27in; min-height: 11.69in; margin: 18px auto; background: #fff; padding: .45in; box-shadow: 0 15px 35px rgba(15, 23, 42, .18); }
        .receipt-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 14px; }
        .receipt-table th, .receipt-table td { border: 1px solid #111; padding: 4px 6px; vertical-align: top; }
        .receipt-title-cell { position: relative; text-align: center; height: 72px; padding-top: 38px !important; }
        .form-title { font-family: Arial, sans-serif; font-size: 16px; font-weight: 800; letter-spacing: .2px; }
        .receipt-title-cell .annex { position: absolute; right: 8px; top: 10px; }
        .annex { text-align: right; font-style: italic; font-size: 13px; }
        .center { text-align: center; }
        .field-input, .line-input, .note-input, .line-select { border: 0; border-bottom: 1px solid #111; border-radius: 0; width: 100%; min-height: 24px; padding: 0 4px; background: #fff8dc; text-align: center; font-family: "Times New Roman", Times, serif; font-weight: 700; }
        .line-select { text-align-last: center; }
        .line-input.position { font-weight: 400; }
        .note-input { text-align: left; font-style: italic; font-weight: 400; }
        .item-block { min-height: 330px; line-height: 1.35; }
        .blank-row td { height: 25px; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; margin-top: 0; }
        .signature-box { padding-top: 4px; }
        .signature-label { margin-bottom: 24px; }
        .signature-label.spaced { margin-bottom: 48px; }
        .date-line { margin-top: 12px; }
        .small-print { font-size: 13px; }
        .alert { font-family: Arial, sans-serif; }
        @media print {
            body { background: #fff; }
            .toolbar, .alert, .no-print { display: none !important; }
            .sheet { width: auto; min-height: auto; margin: 0; padding: .25in; box-shadow: none; }
            .field-input, .line-input, .note-input, .line-select { background: transparent; appearance: none; -webkit-appearance: none; -moz-appearance: none; }
            @page { size: A4 portrait; margin: .35in; }
        }
    </style>
</head>
<body>
    <form method="POST" action="saveReturnedEquipmentReceipt.php">
        <input type="hidden" name="id" value="<?= (int) $id; ?>">
        <div class="toolbar no-print">
            <button type="submit" class="btn btn-success btn-sm">Save Editable Fields</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
        </div>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success mx-auto mt-3" style="max-width:8.27in;"><?= h($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger mx-auto mt-3" style="max-width:8.27in;"><?= h($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <main class="sheet">
            <table class="receipt-table">
                <tr>
                    <td colspan="5" class="receipt-title-cell">
                        <span class="form-title"><?= h($formTitle); ?></span>
                        <span class="annex">Annex 4.6</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" rowspan="2">
                        Entity Name: <strong>DENR CARAGA Regional Office</strong>
                    </td>
                    <td colspan="2">
                        Date: <strong><?= h(format_date_display($record['returned_at'])); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?= h($receiptLabel); ?>
                        <input class="field-input" type="text" name="return_receipt_no" value="<?= h($receiptNo); ?>">
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="center">This is to acknowledge receipt of the returned <?= h($propertyTypeText); ?></td>
                </tr>
                <tr class="center">
                    <th style="width:40%;">Item Description</th>
                    <th style="width:12%;">Quantity</th>
                    <th style="width:17%;"><?= $isPpe ? 'PAR No.' : 'ICS No.'; ?> /<br>PAR No.</th>
                    <th style="width:15%;">End-User</th>
                    <th style="width:16%;">Remarks</th>
                </tr>
                <tr>
                    <td class="item-block">
                        <strong>1.) <?= h(strtoupper($itemDescription)); ?></strong><br>
                        <?php foreach ($detailLines as $line): ?>
                            <?= h($line); ?><br>
                        <?php endforeach; ?>
                    </td>
                    <td class="center"><br>1</td>
                    <td><?= h($record['propertyNumber']); ?></td>
                    <td class="center"><br><strong><?= h($endUser); ?></strong></td>
                    <td class="center"><br><?= h($remarks); ?></td>
                </tr>
                <?php for ($i = 0; $i < 10; $i++): ?>
                    <tr class="blank-row"><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; ?>
                <tr>
                    <td colspan="5" style="padding:0;">
                        <div class="signature-grid">
                            <div class="signature-box">
                                <div class="signature-label spaced">Returned by:</div>
                                <select class="line-select js-signatory-select" name="returned_by_name" data-position-target="returned_by_position">
                                    <?php render_name_options($receiptUsers, $returnedByName); ?>
                                </select>
                                <input class="line-input position" type="text" name="returned_by_position" id="returned_by_position" value="<?= h($returnedByPosition); ?>" placeholder="Position">
                                <div class="date-line"><input class="line-input position" type="date" name="returned_by_date" value="<?= h($returnedByDate); ?>"><div class="center small-print">Date</div></div>
                            </div>
                            <div class="signature-box">
                                <div class="signature-label spaced">Received by:</div>
                                <select class="line-select js-signatory-select" name="received_by_name" data-position-target="received_by_position">
                                    <?php render_name_options($receiptUsers, $receivedByName); ?>
                                </select>
                                <input class="line-input position" type="text" name="received_by_position" id="received_by_position" value="<?= h($receivedByPosition); ?>" placeholder="Position">
                                <div class="date-line"><input class="line-input position" type="date" name="received_by_date" value="<?= h($receivedByDate); ?>"><div class="center small-print">Date</div></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <strong><em>Note:</em></strong><br>
                        <input class="note-input" type="text" name="return_receipt_note" value="<?= h($receiptNote); ?>">
                        <div style="height:20px;border-bottom:1px solid #111;"></div>
                        <div style="height:20px;border-bottom:1px solid #111;"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <div class="signature-label spaced">Received Item by:</div>
                        <div style="max-width:360px;">
                            <select class="line-select js-signatory-select" name="received_item_by_name" data-position-target="received_item_by_position">
                                <?php render_name_options($receiptUsers, $receivedItemByName); ?>
                            </select>
                            <input class="line-input position" type="text" name="received_item_by_position" id="received_item_by_position" value="<?= h($receivedItemByPosition); ?>" placeholder="Position">
                            <input class="line-input position mt-2" type="date" name="received_item_by_date" value="<?= h($receivedItemByDate); ?>">
                        </div>
                    </td>
                </tr>
            </table>
        </main>
    </form>
    <script>
        document.querySelectorAll('.js-signatory-select').forEach(function (select) {
            select.addEventListener('change', function () {
                var target = document.getElementById(select.getAttribute('data-position-target'));
                var selectedOption = select.options[select.selectedIndex];
                var position = selectedOption ? selectedOption.getAttribute('data-position') : '';

                if (target && position) {
                    target.value = position;
                }
            });
        });
    </script>
</body>
</html>
