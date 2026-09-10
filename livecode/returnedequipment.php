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

ensureReturnedReceiptSchema($conn);

$sessionOffice = $_SESSION['OfficeSRF'] ?? '';
$canViewAllOffices = $sessionOffice === '' || strtoupper($sessionOffice) === 'REGIONAL OFFICE';
$currentUserId = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : 0;
$currentUserRole = $_SESSION['User_RoleSRF'] ?? '';
$isSuperAdmin = $currentUserRole === 'Super_admin';

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? 'Returned';
$equipmentType = $_GET['equipmentType'] ?? 'All';
$office = $_GET['office'] ?? ($canViewAllOffices ? 'All' : $sessionOffice);
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$printReturnId = isset($_GET['print_return_id']) ? (int) $_GET['print_return_id'] : 0;

if (!in_array($status, ['Returned', 'Restored', 'All'], true)) {
    $status = 'Returned';
}

if (!$canViewAllOffices) {
    $office = $sessionOffice;
}

$where = [];
$params = [];
$types = '';

if ($status !== 'All') {
    $where[] = 'return_status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($equipmentType !== 'All' && $equipmentType !== '') {
    $where[] = 'equipmentType = ?';
    $params[] = $equipmentType;
    $types .= 's';
}

if ($office !== 'All' && $office !== '') {
    $where[] = 'office = ?';
    $params[] = $office;
    $types .= 's';
}

if ($dateFrom !== '') {
    $where[] = 'DATE(returned_at) >= ?';
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo !== '') {
    $where[] = 'DATE(returned_at) <= ?';
    $params[] = $dateTo;
    $types .= 's';
}

if ($search !== '') {
    $where[] = '(employeeName LIKE ? OR accountablePerson LIKE ? OR actualUser LIKE ? OR propertyNumber LIKE ? OR serialNumber LIKE ? OR brand LIKE ? OR equipmentType LIKE ?)';
    $likeSearch = '%' . $search . '%';
    for ($i = 0; $i < 7; $i++) {
        $params[] = $likeSearch;
        $types .= 's';
    }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$summaryWhere = [];
$summaryParams = [];
$summaryTypes = '';
if (!$canViewAllOffices && $sessionOffice !== '') {
    $summaryWhere[] = 'office = ?';
    $summaryParams[] = $sessionOffice;
    $summaryTypes .= 's';
}
$summaryWhereSql = $summaryWhere ? 'WHERE ' . implode(' AND ', $summaryWhere) : '';

$summary = [
    'total_returned' => 0,
    'currently_returned' => 0,
    'restored' => 0,
    'this_month' => 0,
];

$summarySql = "SELECT
        COUNT(*) AS total_returned,
        SUM(return_status = 'Returned') AS currently_returned,
        SUM(return_status = 'Restored') AS restored,
        SUM(YEAR(returned_at) = YEAR(CURRENT_DATE()) AND MONTH(returned_at) = MONTH(CURRENT_DATE())) AS this_month
    FROM inv_returned_equipment $summaryWhereSql";
$summaryStmt = $conn->prepare($summarySql);
if ($summaryTypes !== '') {
    $summaryStmt->bind_param($summaryTypes, ...$summaryParams);
}
$summaryStmt->execute();
$summaryResult = $summaryStmt->get_result()->fetch_assoc();
if ($summaryResult) {
    $summary = array_map('intval', $summaryResult);
}

$typeOptions = [];
$typeSql = 'SELECT DISTINCT equipmentType FROM inv_returned_equipment';
if (!$canViewAllOffices && $sessionOffice !== '') {
    $typeSql .= ' WHERE office = ?';
}
$typeSql .= ' ORDER BY equipmentType ASC';
$typeStmt = $conn->prepare($typeSql);
if (!$canViewAllOffices && $sessionOffice !== '') {
    $typeStmt->bind_param('s', $sessionOffice);
}
$typeStmt->execute();
$typeResult = $typeStmt->get_result();
while ($row = $typeResult->fetch_assoc()) {
    if ($row['equipmentType'] !== '') {
        $typeOptions[] = $row['equipmentType'];
    }
}

$officeOptions = [];
if ($canViewAllOffices) {
    $officeResult = $conn->query('SELECT DISTINCT office FROM inv_returned_equipment WHERE office <> "" ORDER BY office ASC');
    while ($row = $officeResult->fetch_assoc()) {
        $officeOptions[] = $row['office'];
    }
}

$recordsSql = "SELECT * FROM inv_returned_equipment $whereSql ORDER BY returned_at DESC, id DESC";
$recordsStmt = $conn->prepare($recordsSql);
if ($types !== '') {
    $recordsStmt->bind_param($types, ...$params);
}
$recordsStmt->execute();
$records = $recordsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$chartLabels = [];
$chartValues = [];
$chartSql = "SELECT equipmentType, COUNT(*) AS total FROM inv_returned_equipment $summaryWhereSql GROUP BY equipmentType ORDER BY total DESC LIMIT 10";
$chartStmt = $conn->prepare($chartSql);
if ($summaryTypes !== '') {
    $chartStmt->bind_param($summaryTypes, ...$summaryParams);
}
$chartStmt->execute();
$chartResult = $chartStmt->get_result();
while ($row = $chartResult->fetch_assoc()) {
    $chartLabels[] = $row['equipmentType'] ?: 'Unspecified';
    $chartValues[] = (int) $row['total'];
}

$approvers = [];
$isReturnApprover = false;
$approverResult = $conn->query('SELECT * FROM inv_return_approvers ORDER BY is_default DESC, is_active DESC, full_name ASC');
while ($row = $approverResult->fetch_assoc()) {
    if ((int) $row['user_id'] === $currentUserId && (int) $row['is_active'] === 1) {
        $isReturnApprover = true;
    }
    $approvers[] = $row;
}

$pendingRequests = [];
if ($isSuperAdmin || $isReturnApprover) {
    $pendingSql = "SELECT r.*, i.equipmentType, i.brand, i.propertyNumber, i.serialNumber, i.accountablePerson, i.actualUser, i.office, i.officeDivision
        FROM inv_return_requests r
        INNER JOIN inv_inventory i ON i.id = r.inventory_id
        WHERE r.status = 'Pending'";
    $pendingParams = [];
    $pendingTypes = '';

    if (!$isSuperAdmin) {
        $pendingSql .= ' AND r.assigned_to_id = ?';
        $pendingParams[] = $currentUserId;
        $pendingTypes .= 'i';
    }

    $pendingSql .= ' ORDER BY r.created_at ASC';
    $pendingStmt = $conn->prepare($pendingSql);
    if ($pendingTypes !== '') {
        $pendingStmt->bind_param($pendingTypes, ...$pendingParams);
    }
    $pendingStmt->execute();
    $pendingRequests = $pendingStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$approverSearch = trim($_GET['approver_search'] ?? '');
$approverCandidates = [];
if ($isSuperAdmin && $approverSearch !== '') {
    require_once __DIR__ . '/connect_otos.php';
    $searchTerm = '%' . $approverSearch . '%';
    $candidateStmt = $conn_otos->prepare('SELECT id, Full_Name, username, Office, Station, User_Role FROM useremployee WHERE Full_Name LIKE ? OR username LIKE ? ORDER BY Full_Name ASC LIMIT 20');
    $candidateStmt->bind_param('ss', $searchTerm, $searchTerm);
    $candidateStmt->execute();
    $approverCandidates = $candidateStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .returned-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #1f2937;
    }

    .returned-hero {
        background: linear-gradient(135deg, #0f766e 0%, #155e75 55%, #1e3a8a 100%);
        color: #fff;
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 20px 45px rgba(15, 118, 110, 0.25);
        margin-bottom: 24px;
        overflow: hidden;
        position: relative;
    }

    .returned-hero:after {
        content: '';
        position: absolute;
        width: 230px;
        height: 230px;
        border-radius: 50%;
        right: -70px;
        top: -80px;
        background: rgba(255,255,255,0.13);
    }

    .returned-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
    }

    .returned-hero p {
        max-width: 720px;
        margin: 8px 0 0;
        opacity: 0.88;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .metric-card, .panel-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .metric-card {
        padding: 20px;
    }

    .metric-card span {
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .metric-card strong {
        display: block;
        color: #0f172a;
        font-size: 2rem;
        line-height: 1;
        margin-top: 10px;
    }

    .filter-panel {
        padding: 20px;
        margin-bottom: 24px;
    }

    .panel-card {
        padding: 22px;
        margin-bottom: 24px;
    }

    .table thead th {
        background: #0f172a;
        color: #fff;
        border: 0;
        white-space: nowrap;
    }

    .equipment-title {
        font-weight: 800;
        color: #0f172a;
    }

    .muted-small {
        color: #64748b;
        font-size: 0.84rem;
    }

    .status-pill {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 6px 10px;
    }

    .status-returned {
        background: #fef3c7;
        color: #92400e;
    }

    .status-restored {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #dbeafe;
        color: #1d4ed8;
    }

    @media (max-width: 992px) {
        .metric-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .returned-hero {
            padding: 24px 18px;
            border-radius: 18px;
        }

        .metric-grid {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .no-print, .navbar, .sidebar {
            display: none !important;
        }

        .content {
            margin: 0 !important;
        }
    }
</style>

<div class="returned-page container-fluid py-4">
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
            <?= h($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
            <?= h($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    <?php endif; ?>

    <section class="returned-hero">
        <div class="position-relative">
            <h1><i class="fas fa-undo-alt mr-2"></i>Returned Equipment</h1>
            <p>Track equipment removed from active inventory, review return history, and restore records when needed.</p>
        </div>
    </section>

    <section class="metric-grid">
        <div class="metric-card"><span>Total Return History</span><strong><?= number_format($summary['total_returned']); ?></strong></div>
        <div class="metric-card"><span>Currently Returned</span><strong><?= number_format($summary['currently_returned']); ?></strong></div>
        <div class="metric-card"><span>Restored</span><strong><?= number_format($summary['restored']); ?></strong></div>
        <div class="metric-card"><span>Returned This Month</span><strong><?= number_format($summary['this_month']); ?></strong></div>
        <div class="metric-card"><span>Pending Approval</span><strong><?= number_format(count($pendingRequests)); ?></strong></div>
    </section>

    <?php if ($isSuperAdmin || $isReturnApprover): ?>
        <section class="panel-card no-print">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i>Return Requests for Approval</h5>
                    <div class="muted-small">Accept moves equipment to returned records. Disapprove keeps it in active inventory.</div>
                </div>
                <span class="status-pill status-pending"><i class="fas fa-hourglass-half"></i><?= number_format(count($pendingRequests)); ?> Pending</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Requested Equipment</th>
                            <th>Requested By</th>
                            <th>Assigned To</th>
                            <th>Reason</th>
                            <th>Review Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingRequests)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No pending return requests.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($pendingRequests as $request): ?>
                            <tr>
                                <td>
                                    <div class="equipment-title"><?= h($request['equipmentType']); ?> - <?= h($request['brand']); ?></div>
                                    <div><?= h($request['propertyNumber']); ?></div>
                                    <div class="muted-small"><?= h($request['serialNumber']); ?></div>
                                    <div class="muted-small"><?= h($request['office']); ?> / <?= h($request['officeDivision']); ?></div>
                                </td>
                                <td>
                                    <div><?= h($request['requested_by_name'] ?: 'System'); ?></div>
                                    <div class="muted-small"><?= h(date('M d, Y h:i A', strtotime($request['created_at']))); ?></div>
                                </td>
                                <td><?= h($request['assigned_to_name']); ?></td>
                                <td><?= h($request['return_reason'] ?: 'No reason provided.'); ?></td>
                                <td style="min-width: 220px;">
                                    <button type="button" class="btn btn-sm btn-success mb-2" data-toggle="modal" data-target="#acceptReturn<?= (int) $request['id']; ?>">
                                        <i class="fas fa-check mr-1"></i>Accept Return
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger mb-2" data-toggle="modal" data-target="#disapproveReturn<?= (int) $request['id']; ?>">
                                        <i class="fas fa-times mr-1"></i>Disapprove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php foreach ($pendingRequests as $request): ?>
                <div class="modal fade" id="acceptReturn<?= (int) $request['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="acceptReturnLabel<?= (int) $request['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="approveReturnEquipment.php" method="POST" class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="acceptReturnLabel<?= (int) $request['id']; ?>"><i class="fas fa-check-circle mr-2"></i>Accept Return</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">Accept this return request and move the equipment to returned records?</p>
                                <div class="border rounded p-3 bg-light mb-3">
                                    <strong><?= h($request['equipmentType']); ?> - <?= h($request['brand']); ?></strong>
                                    <div class="muted-small">Property No: <?= h($request['propertyNumber']); ?></div>
                                    <div class="muted-small">Serial No: <?= h($request['serialNumber']); ?></div>
                                </div>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id']; ?>">
                                <div class="form-group mb-0">
                                    <label for="acceptRemarks<?= (int) $request['id']; ?>">Remarks</label>
                                    <textarea name="review_remarks" id="acceptRemarks<?= (int) $request['id']; ?>" class="form-control" rows="3" placeholder="Approval remarks (optional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i>Accept Return</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="disapproveReturn<?= (int) $request['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="disapproveReturnLabel<?= (int) $request['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="disapproveReturnEquipment.php" method="POST" class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="disapproveReturnLabel<?= (int) $request['id']; ?>"><i class="fas fa-times-circle mr-2"></i>Disapprove Return</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">Disapprove this return request? The equipment will remain in active inventory.</p>
                                <div class="border rounded p-3 bg-light mb-3">
                                    <strong><?= h($request['equipmentType']); ?> - <?= h($request['brand']); ?></strong>
                                    <div class="muted-small">Property No: <?= h($request['propertyNumber']); ?></div>
                                    <div class="muted-small">Serial No: <?= h($request['serialNumber']); ?></div>
                                </div>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id']; ?>">
                                <div class="form-group mb-0">
                                    <label for="disapproveRemarks<?= (int) $request['id']; ?>">Remarks <span class="text-danger">*</span></label>
                                    <textarea name="review_remarks" id="disapproveRemarks<?= (int) $request['id']; ?>" class="form-control" rows="3" required placeholder="Reason for disapproval"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-times mr-1"></i>Disapprove</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if ($isSuperAdmin): ?>
        <section class="panel-card no-print">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-users-cog mr-2"></i>Return Approver Settings</h5>
                    <div class="muted-small">Default approver receives new return requests. Rodelo L. Tanudtanud is seeded as the default GSS account.</div>
                </div>
            </div>

            <form method="GET" action="mainmenu.php" class="mb-3">
                <input type="hidden" name="dir" value="returnedequipment">
                <div class="input-group">
                    <input type="text" name="approver_search" class="form-control" value="<?= h($approverSearch); ?>" placeholder="Search OTOS user by name or username">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search mr-1"></i>Search User</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($approverCandidates)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr><th>Name</th><th>Username</th><th>Station</th><th>Role</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approverCandidates as $candidate): ?>
                                <tr>
                                    <td><?= h($candidate['Full_Name']); ?></td>
                                    <td><?= h($candidate['username']); ?></td>
                                    <td><?= h($candidate['Station']); ?></td>
                                    <td><?= h($candidate['User_Role']); ?></td>
                                    <td>
                                        <form action="saveReturnApprover.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?= (int) $candidate['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Add / Activate</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($approverSearch !== ''): ?>
                <div class="alert alert-info">No OTOS users found for your search.</div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>Approver</th><th>Username</th><th>Station</th><th>Status</th><th>Default</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvers as $approver): ?>
                            <tr>
                                <td><?= h($approver['full_name']); ?></td>
                                <td><?= h($approver['username']); ?></td>
                                <td><?= h($approver['station']); ?></td>
                                <td><?= (int) $approver['is_active'] === 1 ? '<span class="status-pill status-restored">Active</span>' : '<span class="status-pill status-returned">Inactive</span>'; ?></td>
                                <td><?= (int) $approver['is_default'] === 1 ? '<span class="status-pill status-pending">Default</span>' : '<span class="text-muted small">No</span>'; ?></td>
                                <td class="d-flex flex-wrap" style="gap: 6px;">
                                    <?php if ((int) $approver['is_active'] === 1 && (int) $approver['is_default'] !== 1): ?>
                                        <form action="setDefaultReturnApprover.php" method="POST">
                                            <input type="hidden" name="approver_id" value="<?= (int) $approver['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="toggleReturnApprover.php" method="POST">
                                        <input type="hidden" name="approver_id" value="<?= (int) $approver['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?= (int) $approver['is_active'] === 1 ? 0 : 1; ?>">
                                        <button type="submit" class="btn btn-sm <?= (int) $approver['is_active'] === 1 ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                            <?= (int) $approver['is_active'] === 1 ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="panel-card no-print">
        <form method="GET" action="mainmenu.php" class="mb-0">
            <input type="hidden" name="dir" value="returnedequipment">
            <div class="form-row align-items-end">
                <div class="form-group col-lg-3 col-md-6">
                    <label for="search">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= h($search); ?>" placeholder="Property no., user, brand...">
                </div>
                <div class="form-group col-lg-2 col-md-6">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <?php foreach (['Returned', 'Restored', 'All'] as $option): ?>
                            <option value="<?= h($option); ?>" <?= $status === $option ? 'selected' : ''; ?>><?= h($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-lg-2 col-md-6">
                    <label for="equipmentType">Equipment Type</label>
                    <select class="form-control" id="equipmentType" name="equipmentType">
                        <option value="All">All</option>
                        <?php foreach ($typeOptions as $option): ?>
                            <option value="<?= h($option); ?>" <?= $equipmentType === $option ? 'selected' : ''; ?>><?= h($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-lg-2 col-md-6">
                    <label for="office">Office</label>
                    <select class="form-control" id="office" name="office" <?= !$canViewAllOffices ? 'disabled' : ''; ?>>
                        <option value="All">All</option>
                        <?php foreach ($officeOptions as $option): ?>
                            <option value="<?= h($option); ?>" <?= $office === $option ? 'selected' : ''; ?>><?= h($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$canViewAllOffices): ?>
                        <input type="hidden" name="office" value="<?= h($sessionOffice); ?>">
                    <?php endif; ?>
                </div>
                <div class="form-group col-lg-1 col-md-6">
                    <label for="date_from">From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?= h($dateFrom); ?>">
                </div>
                <div class="form-group col-lg-1 col-md-6">
                    <label for="date_to">To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?= h($dateTo); ?>">
                </div>
                <div class="form-group col-lg-1 col-md-12 d-flex">
                    <button type="submit" class="btn btn-primary flex-fill mr-2"><i class="fas fa-filter"></i></button>
                    <a href="mainmenu.php?dir=returnedequipment" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </div>
        </form>
    </section>

    <div class="row">
        <div class="col-xl-4">
            <section class="panel-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 font-weight-bold">Top Returned Types</h5>
                    <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()"><i class="fas fa-print mr-1"></i>Print</button>
                </div>
                <canvas id="returnedEquipmentChart" height="260"></canvas>
            </section>
        </div>

        <div class="col-xl-8">
            <section class="panel-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0 font-weight-bold">Returned Equipment Records</h5>
                        <div class="muted-small"><?= number_format(count($records)); ?> record(s) in current view</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Equipment</th>
                                <th>Property / Serial</th>
                                <th>Accountability</th>
                                <th>Office</th>
                                <th>Returned</th>
                                <th>Status</th>
                                <th class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No returned equipment records found.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td>
                                        <div class="equipment-title"><?= h($record['equipmentType']); ?></div>
                                        <div><?= h($record['brand']); ?></div>
                                        <div class="muted-small"><?= h($record['yearAcquired']); ?> acquired</div>
                                    </td>
                                    <td>
                                        <div><?= h($record['propertyNumber']); ?></div>
                                        <div class="muted-small"><?= h($record['serialNumber']); ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($record['accountablePerson']); ?></div>
                                        <div class="muted-small">Actual user: <?= h($record['actualUser']); ?></div>
                                    </td>
                                    <td>
                                        <div><?= h($record['office']); ?></div>
                                        <div class="muted-small"><?= h($record['officeDivision']); ?></div>
                                    </td>
                                    <td>
                                        <div><?= h(date('M d, Y', strtotime($record['returned_at']))); ?></div>
                                        <div class="muted-small">By <?= h($record['returned_by'] ?: 'System'); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($record['return_status'] === 'Returned'): ?>
                                            <span class="status-pill status-returned"><i class="fas fa-box-open"></i>Returned</span>
                                        <?php else: ?>
                                            <span class="status-pill status-restored"><i class="fas fa-check-circle"></i>Restored</span>
                                            <div class="muted-small mt-1"><?= $record['restored_at'] ? h(date('M d, Y', strtotime($record['restored_at']))) : ''; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="no-print">
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 js-return-print-preview" data-print-url="printReturnedEquipment.php?id=<?= (int) $record['id']; ?>" data-print-title="Return Receipt - <?= h($record['propertyNumber']); ?>">
                                            <i class="fas fa-print mr-1"></i>Print Form
                                        </button>
                                        <?php if ($record['return_status'] === 'Returned'): ?>
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#restoreReturned<?= (int) $record['id']; ?>">
                                                <i class="fas fa-history mr-1"></i>Restore
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Restored to ID <?= h($record['restore_inventory_id']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($record['return_reason'])): ?>
                                    <tr class="bg-light">
                                        <td colspan="7"><strong>Return reason:</strong> <?= h($record['return_reason']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php foreach ($records as $record): ?>
                    <?php if ($record['return_status'] === 'Returned'): ?>
                        <div class="modal fade" id="restoreReturned<?= (int) $record['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="restoreReturnedLabel<?= (int) $record['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="restoreReturnedEquipment.php" method="POST" class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="restoreReturnedLabel<?= (int) $record['id']; ?>"><i class="fas fa-history mr-2"></i>Restore Equipment</h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="mb-2">Restore this equipment to active inventory?</p>
                                        <div class="border rounded p-3 bg-light mb-0">
                                            <strong><?= h($record['equipmentType']); ?> - <?= h($record['brand']); ?></strong>
                                            <div class="muted-small">Property No: <?= h($record['propertyNumber']); ?></div>
                                            <div class="muted-small">Serial No: <?= h($record['serialNumber']); ?></div>
                                        </div>
                                        <input type="hidden" name="id" value="<?= (int) $record['id']; ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success"><i class="fas fa-history mr-1"></i>Confirm Restore</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>
        </div>
    </div>
</div>

<div class="modal fade" id="returnReceiptPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border:0;border-radius:18px;overflow:hidden;">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0" id="returnReceiptPreviewTitle">Return Receipt</h5>
                    <small class="text-white-50">Edit signatory details, save, then print.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0 position-relative" style="background:#f8fafc;">
                <div id="returnReceiptLoading" class="flex-column align-items-center justify-content-center position-absolute w-100 h-100" style="display:none;top:0;left:0;z-index:2;background:rgba(248,250,252,.94);">
                    <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                    <div class="font-weight-bold text-primary">Loading Return Receipt...</div>
                    <small class="text-muted">Please wait while the form is being prepared.</small>
                </div>
                <iframe id="returnReceiptPreviewFrame" src="about:blank" onload="hideReturnReceiptLoading()" style="width:100%;height:78vh;border:0;background:#fff;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="returnReceiptPrintBtn"><i class="fas fa-print mr-1"></i>Print</button>
            </div>
        </div>
    </div>
</div>

<script>
    function hideReturnReceiptLoading() {
        const loadingOverlay = document.getElementById('returnReceiptLoading');
        const printButton = document.getElementById('returnReceiptPrintBtn');

        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }

        if (printButton) {
            printButton.disabled = false;
        }
    }

    const returnedChartLabels = <?= json_encode($chartLabels); ?>;
    const returnedChartValues = <?= json_encode($chartValues); ?>;
    const chartCanvas = document.getElementById('returnedEquipmentChart');

    if (chartCanvas) {
        new Chart(chartCanvas, {
            type: 'doughnut',
            data: {
                labels: returnedChartLabels.length ? returnedChartLabels : ['No data'],
                datasets: [{
                    data: returnedChartValues.length ? returnedChartValues : [1],
                    backgroundColor: ['#0f766e', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2', '#16a34a', '#be123c', '#4338ca', '#ca8a04'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const previewFrame = document.getElementById('returnReceiptPreviewFrame');
        const previewTitle = document.getElementById('returnReceiptPreviewTitle');
        const printButton = document.getElementById('returnReceiptPrintBtn');
        const loadingOverlay = document.getElementById('returnReceiptLoading');
        const autoPrintReturnId = <?= (int) $printReturnId; ?>;
        let receiptLoadingTimer = null;

        function setReceiptLoading(isLoading) {
            if (loadingOverlay) {
                loadingOverlay.style.display = isLoading ? 'flex' : 'none';
            }

            if (printButton) {
                printButton.disabled = isLoading;
            }
        }

        function openReceiptPreview(url, title) {
            if (!previewFrame || !window.jQuery) {
                window.open(url, '_blank');
                return;
            }

            previewTitle.textContent = title || 'Return Receipt';
            setReceiptLoading(true);
            clearTimeout(receiptLoadingTimer);
            receiptLoadingTimer = setTimeout(function () {
                hideReturnReceiptLoading();
            }, 7000);
            previewFrame.src = url;
            $('#returnReceiptPreviewModal').modal('show');
        }

        if (previewFrame) {
            previewFrame.addEventListener('load', function () {
                clearTimeout(receiptLoadingTimer);
                receiptLoadingTimer = setTimeout(function () {
                    hideReturnReceiptLoading();
                }, 150);
            });
        }

        document.querySelectorAll('.js-return-print-preview').forEach(function (button) {
            button.addEventListener('click', function () {
                openReceiptPreview(button.getAttribute('data-print-url'), button.getAttribute('data-print-title'));
            });
        });

        if (printButton) {
            printButton.addEventListener('click', function () {
                if (previewFrame && previewFrame.contentWindow) {
                    previewFrame.contentWindow.focus();
                    previewFrame.contentWindow.print();
                }
            });
        }

        if (window.jQuery) {
            $('#returnReceiptPreviewModal').on('hidden.bs.modal', function () {
                clearTimeout(receiptLoadingTimer);
                if (previewFrame) {
                    previewFrame.src = 'about:blank';
                }
                hideReturnReceiptLoading();
            });
        }

        if (autoPrintReturnId > 0) {
            openReceiptPreview('printReturnedEquipment.php?id=' + encodeURIComponent(autoPrintReturnId), 'Return Receipt');
        }
    });
</script>
