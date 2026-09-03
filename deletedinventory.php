<?php
require_once __DIR__ . '/connect.php';

function h_deleted_inventory($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ensureDeletedInventoryPageSchema(mysqli $conn): void {
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

ensureDeletedInventoryPageSchema($conn);

$sessionOffice = $_SESSION['OfficeSRF'] ?? '';
$canViewAllOffices = $sessionOffice === '' || strtoupper($sessionOffice) === 'REGIONAL OFFICE';
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'Deleted';
$office = $_GET['office'] ?? ($canViewAllOffices ? 'All' : $sessionOffice);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if (!in_array($status, ['Deleted', 'Restored', 'All'], true)) {
    $status = 'Deleted';
}

if (!$canViewAllOffices) {
    $office = $sessionOffice;
}

$where = [];
$params = [];
$types = '';

if ($status !== 'All') {
    $where[] = 'delete_status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($office !== 'All' && $office !== '') {
    $where[] = 'office = ?';
    $params[] = $office;
    $types .= 's';
}

if ($dateFrom !== '') {
    $where[] = 'DATE(deleted_at) >= ?';
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo !== '') {
    $where[] = 'DATE(deleted_at) <= ?';
    $params[] = $dateTo;
    $types .= 's';
}

if ($search !== '') {
    $where[] = '(equipmentType LIKE ? OR brand LIKE ? OR propertyNumber LIKE ? OR serialNumber LIKE ? OR accountablePerson LIKE ? OR actualUser LIKE ? OR deleted_by_name LIKE ?)';
    $likeSearch = '%' . $search . '%';
    for ($i = 0; $i < 7; $i++) {
        $params[] = $likeSearch;
        $types .= 's';
    }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$recordsSql = "SELECT * FROM inv_deleted_inventory $whereSql ORDER BY deleted_at DESC, id DESC";
$recordsStmt = $conn->prepare($recordsSql);
if ($types !== '') {
    $recordsStmt->bind_param($types, ...$params);
}
$recordsStmt->execute();
$recordsResult = $recordsStmt->get_result();
$records = [];
while ($row = $recordsResult->fetch_assoc()) {
    $records[] = $row;
}

$summary = ['total' => 0, 'deleted' => 0, 'restored' => 0];
$summaryResult = $conn->query("SELECT COUNT(*) AS total, SUM(delete_status = 'Deleted') AS deleted, SUM(delete_status = 'Restored') AS restored FROM inv_deleted_inventory");
if ($summaryResult) {
    $summary = array_merge($summary, $summaryResult->fetch_assoc() ?: []);
}

$officeOptions = [];
if ($canViewAllOffices) {
    $officeResult = $conn->query('SELECT DISTINCT office FROM inv_deleted_inventory WHERE office <> "" ORDER BY office ASC');
    if ($officeResult) {
        while ($officeRow = $officeResult->fetch_assoc()) {
            $officeOptions[] = $officeRow['office'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Inventory</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: Arial, sans-serif; }
        .page-header { background: linear-gradient(135deg, #7f1d1d, #dc2626); color: #fff; border-radius: 18px; padding: 26px; margin-bottom: 20px; }
        .metric-card, .content-card { background: #fff; border: 0; border-radius: 16px; box-shadow: 0 10px 28px rgba(15, 23, 42, .08); }
        .metric-card { padding: 18px; }
        .metric-card span { display: block; color: #64748b; font-size: 13px; }
        .metric-card strong { font-size: 28px; color: #111827; }
        .status-pill { border-radius: 999px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; padding: 6px 10px; }
        .status-deleted { background: #fee2e2; color: #991b1b; }
        .status-restored { background: #dcfce7; color: #166534; }
        .muted-small { color: #64748b; font-size: 12px; }
        .equipment-title { font-weight: 700; color: #111827; }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="page-header">
        <h1 class="mb-1"><i class="fas fa-trash-alt mr-2"></i>Deleted Inventory</h1>
        <p class="mb-0">Records deleted from active inventory are archived here and can be restored when needed.</p>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= h_deleted_inventory($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= h_deleted_inventory($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="metric-card"><span>Total Archived</span><strong><?= number_format((int) $summary['total']); ?></strong></div></div>
        <div class="col-md-4 mb-3"><div class="metric-card"><span>Currently Deleted</span><strong><?= number_format((int) $summary['deleted']); ?></strong></div></div>
        <div class="col-md-4 mb-3"><div class="metric-card"><span>Restored</span><strong><?= number_format((int) $summary['restored']); ?></strong></div></div>
    </div>

    <div class="content-card p-3 mb-4">
        <form method="GET" action="mainmenu.php" class="row align-items-end">
            <input type="hidden" name="dir" value="deletedinventory">
            <div class="col-md-3 mb-3">
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="<?= h_deleted_inventory($search); ?>" placeholder="Property, serial, user, deleted by">
            </div>
            <div class="col-md-2 mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <?php foreach (['Deleted', 'Restored', 'All'] as $option): ?>
                        <option value="<?= h_deleted_inventory($option); ?>" <?= $status === $option ? 'selected' : ''; ?>><?= h_deleted_inventory($option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label>Office</label>
                <?php if ($canViewAllOffices): ?>
                    <select name="office" class="form-control">
                        <option value="All" <?= $office === 'All' ? 'selected' : ''; ?>>All</option>
                        <?php foreach ($officeOptions as $officeOption): ?>
                            <option value="<?= h_deleted_inventory($officeOption); ?>" <?= $office === $officeOption ? 'selected' : ''; ?>><?= h_deleted_inventory($officeOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" class="form-control" value="<?= h_deleted_inventory($office); ?>" disabled>
                <?php endif; ?>
            </div>
            <div class="col-md-2 mb-3">
                <label>Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= h_deleted_inventory($dateFrom); ?>">
            </div>
            <div class="col-md-2 mb-3">
                <label>Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= h_deleted_inventory($dateTo); ?>">
            </div>
            <div class="col-md-1 mb-3">
                <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-filter"></i></button>
            </div>
        </form>
    </div>

    <div class="content-card p-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Equipment</th>
                        <th>Property / Serial</th>
                        <th>Accountability</th>
                        <th>Office</th>
                        <th>Deleted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No deleted inventory records found.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td>
                                <div class="equipment-title"><?= h_deleted_inventory($record['equipmentType']); ?></div>
                                <div><?= h_deleted_inventory($record['brand']); ?></div>
                                <div class="muted-small"><?= h_deleted_inventory($record['yearAcquired']); ?> acquired</div>
                            </td>
                            <td>
                                <div><?= h_deleted_inventory($record['propertyNumber']); ?></div>
                                <div class="muted-small"><?= h_deleted_inventory($record['serialNumber']); ?></div>
                            </td>
                            <td>
                                <div><?= h_deleted_inventory($record['accountablePerson']); ?></div>
                                <div class="muted-small">Actual user: <?= h_deleted_inventory($record['actualUser']); ?></div>
                            </td>
                            <td>
                                <div><?= h_deleted_inventory($record['office']); ?></div>
                                <div class="muted-small"><?= h_deleted_inventory($record['officeDivision']); ?></div>
                            </td>
                            <td>
                                <div><?= h_deleted_inventory(date('M d, Y', strtotime($record['deleted_at']))); ?></div>
                                <div class="muted-small">By <?= h_deleted_inventory($record['deleted_by_name'] ?: 'System'); ?></div>
                            </td>
                            <td>
                                <?php if ($record['delete_status'] === 'Deleted'): ?>
                                    <span class="status-pill status-deleted"><i class="fas fa-trash-alt"></i>Deleted</span>
                                <?php else: ?>
                                    <span class="status-pill status-restored"><i class="fas fa-check-circle"></i>Restored</span>
                                    <div class="muted-small mt-1"><?= $record['restored_at'] ? h_deleted_inventory(date('M d, Y', strtotime($record['restored_at']))) : ''; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($record['delete_status'] === 'Deleted'): ?>
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#restoreDeleted<?= (int) $record['id']; ?>">
                                        <i class="fas fa-undo mr-1"></i>Undo
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">Restored to ID <?= h_deleted_inventory($record['restore_inventory_id']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($record['delete_reason'])): ?>
                            <tr class="bg-light"><td colspan="7"><strong>Delete reason:</strong> <?= h_deleted_inventory($record['delete_reason']); ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($records as $record): ?>
    <?php if ($record['delete_status'] === 'Deleted'): ?>
        <div class="modal fade" id="restoreDeleted<?= (int) $record['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="restoreDeletedInventory.php" method="POST" class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-undo mr-2"></i>Restore Deleted Inventory</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Restore this inventory record back to active inventory?</p>
                        <div class="border rounded p-3 bg-light">
                            <strong><?= h_deleted_inventory($record['equipmentType']); ?> - <?= h_deleted_inventory($record['brand']); ?></strong>
                            <div class="muted-small">Property No: <?= h_deleted_inventory($record['propertyNumber']); ?></div>
                            <div class="muted-small">Serial No: <?= h_deleted_inventory($record['serialNumber']); ?></div>
                        </div>
                        <input type="hidden" name="id" value="<?= (int) $record['id']; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-undo mr-1"></i>Confirm Restore</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
