<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

$office = $_SESSION['OfficeSRF'] ?? '';
$inventoryId = isset($_GET['inventoryId']) && is_numeric($_GET['inventoryId']) ? (int) $_GET['inventoryId'] : 0;
$officeDivision = trim($_GET['officeDivision'] ?? '');
$employeeName = trim($_GET['employeeName'] ?? '');
$statusFilter = $_GET['statusFilter'] ?? '';
$sortBy = $_GET['sortBy'] ?? 'id_desc';

$divisionOptions = '';
if ($office !== '') {
    $divisionStmt = $conn->prepare("SELECT officeDivision FROM office_divisions WHERE office = ? ORDER BY officeDivision ASC");
    if ($divisionStmt) {
        $divisionStmt->bind_param('s', $office);
        $divisionStmt->execute();
        $divisionResult = $divisionStmt->get_result();
        while ($divisionRow = $divisionResult->fetch_assoc()) {
            $divisionName = strtoupper($divisionRow['officeDivision']);
            $selected = ($officeDivision === $divisionName) ? ' selected' : '';
            $divisionOptions .= '<option value="' . htmlspecialchars($divisionName) . '"' . $selected . '>' . htmlspecialchars($divisionName) . '</option>';
        }
        $divisionStmt->close();
    }
}

$query = "SELECT id, employeeName, equipmentType, yearAcquired, brand, officeDivision, mark_as_done FROM inv_inventory WHERE Office = ?";
$params = [$office];
$types = 's';

if ($inventoryId > 0) {
    $query .= " AND id = ?";
    $params[] = $inventoryId;
    $types .= 'i';
}

if ($officeDivision !== '') {
    $query .= " AND officeDivision = ?";
    $params[] = $officeDivision;
    $types .= 's';
}

$employeeNameParts = array_filter(preg_split('/\s+/', $employeeName));
foreach ($employeeNameParts as $employeeNamePart) {
    $query .= " AND employeeName LIKE ?";
    $params[] = '%' . $employeeNamePart . '%';
    $types .= 's';
}

if ($statusFilter !== '') {
    $query .= " AND mark_as_done = ?";
    $params[] = (int) $statusFilter;
    $types .= 'i';
}

if ($sortBy === 'name_asc') {
    $query .= " ORDER BY employeeName ASC, id DESC";
} else {
    $query .= " ORDER BY id DESC";
}

$stmt = $conn->prepare($query);
$result = null;
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<style>
    .generate-qr-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .generate-qr-header {
        margin-bottom: 20px;
    }

    .generate-qr-header h2 {
        font-weight: 700;
        color: #2b2b2b;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
        align-items: end;
    }

    .filter-grid label {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .table-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .table-action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .select-cell {
        width: 48px;
        text-align: center;
    }

    .qr-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .generate-qr-page {
            padding: 12px;
        }

        .table-actions {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<div class="generate-qr-page">
    <div class="generate-qr-header">
        <h2><i class="fas fa-tags"></i> Generate Equipment QR Stickers</h2>
        <p class="text-muted mb-0">Filter inventory, mark equipment, then generate a compact A4 sticker sheet.</p>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-search"></i> Search Filters</div>
        <div class="card-body">
            <form action="mainmenu.php" method="GET">
                <input type="hidden" name="dir" value="generateQR">
                <div class="filter-grid">
                    <div>
                        <label for="inventoryId">Inventory ID:</label>
                        <input type="number" class="form-control" id="inventoryId" name="inventoryId" min="1" placeholder="e.g. 455" value="<?php echo htmlspecialchars($inventoryId > 0 ? (string) $inventoryId : ''); ?>">
                    </div>
                    <div>
                        <label for="officeDivision">Office Division:</label>
                        <select class="form-control" id="officeDivision" name="officeDivision">
                            <option value="">All</option>
                            <?php echo $divisionOptions; ?>
                        </select>
                    </div>
                    <div>
                        <label for="employeeName">Employee Name:</label>
                        <input type="text" class="form-control" id="employeeName" name="employeeName" placeholder="Eugreg Baptisma" value="<?php echo htmlspecialchars($employeeName); ?>">
                    </div>
                    <div>
                        <label for="statusFilter">Status:</label>
                        <select class="form-control" id="statusFilter" name="statusFilter">
                            <option value=""<?php echo $statusFilter === '' ? ' selected' : ''; ?>>All</option>
                            <option value="1"<?php echo $statusFilter === '1' ? ' selected' : ''; ?>>Done</option>
                            <option value="0"<?php echo $statusFilter === '0' ? ' selected' : ''; ?>>Not Done</option>
                        </select>
                    </div>
                    <div>
                        <label for="sortBy">Sort By:</label>
                        <select class="form-control" id="sortBy" name="sortBy">
                            <option value="id_desc"<?php echo $sortBy === 'id_desc' ? ' selected' : ''; ?>>Newest First</option>
                            <option value="name_asc"<?php echo $sortBy === 'name_asc' ? ' selected' : ''; ?>>Employee Name (A-Z)</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-actions">
                <div id="selectedCount" class="font-weight-bold">0 selected</div>
                <div class="table-action-buttons">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setAllQrRows(true)"><i class="fas fa-check-square"></i> Mark All</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setAllQrRows(false)"><i class="far fa-square"></i> Unmark All</button>
                    <button type="button" class="btn btn-success btn-sm" onclick="generateMarkedQr()"><i class="fas fa-qrcode"></i> Generate QR</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th class="select-cell">Mark</th>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Equipment</th>
                            <th>Year</th>
                            <th>Brand</th>
                            <th>Office Division</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="select-cell">
                                        <input type="checkbox" class="qr-checkbox" value="<?php echo htmlspecialchars($row['id']); ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($row['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['employeeName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['equipmentType']); ?></td>
                                    <td><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($row['officeDivision']); ?></td>
                                    <td>
                                        <?php echo ((int) $row['mark_as_done'] === 1) ? '<span class="badge badge-success">Done</span>' : '<span class="badge badge-danger">Not Done</span>'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No inventory records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function getQrCheckboxes() {
    return Array.from(document.querySelectorAll('.qr-checkbox'));
}

function updateSelectedCount() {
    const selected = getQrCheckboxes().filter(checkbox => checkbox.checked).length;
    document.getElementById('selectedCount').textContent = `${selected} selected`;
}

function setAllQrRows(checked) {
    getQrCheckboxes().forEach(checkbox => {
        checkbox.checked = checked;
    });
    updateSelectedCount();
}

function generateMarkedQr() {
    const selectedIds = getQrCheckboxes()
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one inventory record.');
        return;
    }

    window.open(`generateQRBatch.php?ids=${encodeURIComponent(selectedIds.join(','))}`, '_blank');
}

document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>

<?php
if ($stmt) {
    $stmt->close();
}
?>
