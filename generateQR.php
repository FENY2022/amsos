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

$pmdDivisionAliases = ['PMD', 'PLANNING AND MANAGEMENT DIVISION', 'PMD RICTU'];

function normalizeQrOfficeDivision($division) {
    return preg_replace('/\s+/', ' ', strtoupper(trim((string) $division)));
}

function getQrOfficeDivisionAliases($division, $pmdDivisionAliases) {
    $normalizedDivision = normalizeQrOfficeDivision($division);

    if (in_array($normalizedDivision, $pmdDivisionAliases, true)) {
        return $pmdDivisionAliases;
    }

    return [$normalizedDivision];
}

function getQrOfficeDivisionOptionValue($division, $pmdDivisionAliases) {
    $normalizedDivision = normalizeQrOfficeDivision($division);

    return in_array($normalizedDivision, $pmdDivisionAliases, true) ? 'PMD' : $normalizedDivision;
}

function appendQrOfficeDivisionFilter(&$query, &$params, &$types, $officeDivision, $pmdDivisionAliases) {
    if ($officeDivision === '') {
        return;
    }

    $divisionAliases = getQrOfficeDivisionAliases($officeDivision, $pmdDivisionAliases);
    $placeholders = implode(',', array_fill(0, count($divisionAliases), '?'));

    $query .= " AND UPPER(TRIM(officeDivision)) IN ($placeholders)";
    foreach ($divisionAliases as $divisionAlias) {
        $params[] = $divisionAlias;
        $types .= 's';
    }
}

$divisionOptions = '';
$seenDivisionOptions = [];
if ($office !== '') {
    $divisionStmt = $conn->prepare("SELECT officeDivision FROM office_divisions WHERE office = ? ORDER BY officeDivision ASC");
    if ($divisionStmt) {
        $divisionStmt->bind_param('s', $office);
        $divisionStmt->execute();
        $divisionResult = $divisionStmt->get_result();
        while ($divisionRow = $divisionResult->fetch_assoc()) {
            $divisionName = getQrOfficeDivisionOptionValue($divisionRow['officeDivision'], $pmdDivisionAliases);
            if (isset($seenDivisionOptions[$divisionName])) {
                continue;
            }
            $seenDivisionOptions[$divisionName] = true;
            $selected = (getQrOfficeDivisionOptionValue($officeDivision, $pmdDivisionAliases) === $divisionName) ? ' selected' : '';
            $divisionOptions .= '<option value="' . htmlspecialchars($divisionName) . '"' . $selected . '>' . htmlspecialchars($divisionName) . '</option>';
        }
        $divisionStmt->close();
    }
}

$employeeOptions = '';
if ($office !== '') {
    $employeeQuery = "SELECT DISTINCT employeeName FROM inv_inventory WHERE Office = ? AND employeeName IS NOT NULL AND TRIM(employeeName) <> ''";
    $employeeParams = [$office];
    $employeeTypes = 's';

    appendQrOfficeDivisionFilter($employeeQuery, $employeeParams, $employeeTypes, $officeDivision, $pmdDivisionAliases);
    $employeeQuery .= " ORDER BY employeeName ASC";

    $employeeStmt = $conn->prepare($employeeQuery);
    if ($employeeStmt) {
        $employeeStmt->bind_param($employeeTypes, ...$employeeParams);
        $employeeStmt->execute();
        $employeeResult = $employeeStmt->get_result();
        while ($employeeRow = $employeeResult->fetch_assoc()) {
            $employeeOptionName = $employeeRow['employeeName'];
            $selected = ($employeeName === $employeeOptionName) ? ' selected' : '';
            $employeeOptions .= '<option value="' . htmlspecialchars($employeeOptionName) . '"' . $selected . '>' . htmlspecialchars($employeeOptionName) . '</option>';
        }
        $employeeStmt->close();
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

appendQrOfficeDivisionFilter($query, $params, $types, $officeDivision, $pmdDivisionAliases);

if ($employeeName !== '') {
    $query .= " AND employeeName = ?";
    $params[] = $employeeName;
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
        <h2><i class="fas fa-tags"></i> Generate Equipment Stickers</h2>
        <p class="text-muted mb-0">Filter inventory, mark equipment, then generate QR stickers or editable PPE stickers.</p>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-search"></i> Search Filters</div>
        <div class="card-body">
            <form action="mainmenu.php" method="GET" id="stickerFilterForm">
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
                        <select class="form-control" id="employeeName" name="employeeName">
                            <option value="">All</option>
                            <?php echo $employeeOptions; ?>
                        </select>
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
                    <button type="button" class="btn btn-success btn-sm" onclick="showQrStickerModal()"><i class="fas fa-qrcode"></i> Generate QR</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="showPpeStickerModal()"><i class="fas fa-id-card"></i> Generate PPE Stickers</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
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
                            <?php $sequenceNumber = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $sequenceNumber++; ?></td>
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
                                <td colspan="9" class="text-center text-muted py-4">No inventory records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrStickerModal" tabindex="-1" role="dialog" aria-labelledby="qrStickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrStickerModalLabel"><i class="fas fa-qrcode"></i> QR Sticker Layout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="qrPerPage" class="font-weight-bold">How many QR stickers in 1 bond paper?</label>
                <select class="form-control" id="qrPerPage">
                    <option value="4">4 stickers</option>
                    <option value="6">6 stickers</option>
                    <option value="8">8 stickers</option>
                    <option value="10">10 stickers</option>
                    <option value="12">12 stickers</option>
                    <option value="15">15 stickers</option>
                    <option value="18" selected>18 stickers</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="generateMarkedQr()"><i class="fas fa-print"></i> Continue</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ppeStickerModal" tabindex="-1" role="dialog" aria-labelledby="ppeStickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ppeStickerModalLabel"><i class="fas fa-id-card"></i> PPE Sticker Layout</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="ppePerPage" class="font-weight-bold">How many PPE stickers in 1 bond paper?</label>
                <select class="form-control" id="ppePerPage">
                    <option value="4">4 stickers</option>
                    <option value="6">6 stickers</option>
                    <option value="8" selected>8 stickers</option>
                    <option value="10">10 stickers</option>
                    <option value="12">12 stickers</option>
                    <option value="15">15 stickers</option>
                </select>
                <small class="text-muted d-block mt-2">Fields are editable before printing and will not be saved to the database.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="generateMarkedPpeStickers()"><i class="fas fa-print"></i> Continue</button>
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

function showQrStickerModal() {
    const selectedIds = getQrCheckboxes()
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one inventory record.');
        return;
    }

    $('#qrStickerModal').modal('show');
}

function generateMarkedQr() {
    const selectedIds = getQrCheckboxes()
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);
    const perPage = document.getElementById('qrPerPage').value;

    if (selectedIds.length === 0) {
        alert('Please select at least one inventory record.');
        return;
    }

    $('#qrStickerModal').modal('hide');
    window.open(`generateQRBatch.php?ids=${encodeURIComponent(selectedIds.join(','))}&perPage=${encodeURIComponent(perPage)}`, '_blank');
}

function getSelectedQrIds() {
    return getQrCheckboxes()
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);
}

function showPpeStickerModal() {
    const selectedIds = getSelectedQrIds();

    if (selectedIds.length === 0) {
        alert('Please select at least one inventory record.');
        return;
    }

    $('#ppeStickerModal').modal('show');
}

function generateMarkedPpeStickers() {
    const selectedIds = getSelectedQrIds();
    const perPage = document.getElementById('ppePerPage').value;

    if (selectedIds.length === 0) {
        alert('Please select at least one inventory record.');
        return;
    }

    $('#ppeStickerModal').modal('hide');
    window.open(`generatePPEStickersBatch.php?ids=${encodeURIComponent(selectedIds.join(','))}&perPage=${encodeURIComponent(perPage)}`, '_blank');
}

document.addEventListener('DOMContentLoaded', updateSelectedCount);

const stickerFilterStorageKey = 'generateStickerFilters';
const stickerFilterFields = ['inventoryId', 'officeDivision', 'employeeName', 'statusFilter', 'sortBy'];

function getStickerFilterForm() {
    return document.getElementById('stickerFilterForm');
}

function getCurrentStickerFilters() {
    const filters = {};
    stickerFilterFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        filters[fieldName] = field ? field.value : '';
    });

    return filters;
}

function saveStickerFilters() {
    localStorage.setItem(stickerFilterStorageKey, JSON.stringify(getCurrentStickerFilters()));
}

function restoreStickerFiltersFromMemory() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilterParams = stickerFilterFields.some(fieldName => urlParams.has(fieldName));

    if (hasFilterParams) {
        saveStickerFilters();
        return;
    }

    const savedFilters = localStorage.getItem(stickerFilterStorageKey);
    if (!savedFilters) {
        return;
    }

    let filters;
    try {
        filters = JSON.parse(savedFilters);
    } catch (error) {
        localStorage.removeItem(stickerFilterStorageKey);
        return;
    }

    const hasSavedValue = stickerFilterFields.some(fieldName => (filters[fieldName] || '') !== '');
    if (!hasSavedValue) {
        return;
    }

    const restoredParams = new URLSearchParams(window.location.search);
    restoredParams.set('dir', 'generateQR');
    stickerFilterFields.forEach(fieldName => {
        if ((filters[fieldName] || '') !== '') {
            restoredParams.set(fieldName, filters[fieldName]);
        }
    });

    window.location.replace(`mainmenu.php?${restoredParams.toString()}`);
}

document.addEventListener('DOMContentLoaded', function () {
    restoreStickerFiltersFromMemory();

    const filterForm = getStickerFilterForm();
    if (filterForm) {
        filterForm.addEventListener('submit', saveStickerFilters);
    }

    stickerFilterFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', saveStickerFilters);
        }
    });
});
</script>

<?php
if ($stmt) {
    $stmt->close();
}
?>
