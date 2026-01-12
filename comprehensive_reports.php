<?php
// Include database connection
require_once 'connect.php'; 

// 1. Initialize filter and view variables
$filterEquipment = $_GET['equipmentType'] ?? 'all';
$filterShelfLife = $_GET['shelfLife'] ?? 'all';
$filterOfficeDivision = $_GET['officeDivision'] ?? 'all';
$viewMode = $_GET['view'] ?? 'conducted'; // Default view is conducted (Done)

// 2. Build SQL WHERE clause based on filters
$whereClauses = [];
if ($filterEquipment !== 'all') {
    $whereClauses[] = "equipmentType = '" . $conn->real_escape_string($filterEquipment) . "'";
}
if ($filterShelfLife !== 'all') {
    $whereClauses[] = "shelfLife = '" . $conn->real_escape_string($filterShelfLife) . "'";
}
if ($filterOfficeDivision !== 'all') {
    $whereClauses[] = "officeDivision = '" . $conn->real_escape_string($filterOfficeDivision) . "'";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// --- EXCEL EXPORT LOGIC (Exports full filtered list, ignoring pagination) ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    $statusValue = ($viewMode == 'pending') ? 0 : 1;
    $filename = ($viewMode == 'pending') ? "Pending_Inventory_" : "Conducted_Inventory_";
    
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=" . $filename . date('Ymd_Hi') . ".xls");
    
    $exportQuery = "SELECT * FROM inv_inventory $whereSql " . (empty($whereSql) ? "WHERE" : "AND") . " mark_as_done = $statusValue ORDER BY employeeName ASC";
    $exportResult = $conn->query($exportQuery);

    echo '<table border="1"><tr><th colspan="8" style="background-color:#eee;">' . strtoupper($viewMode) . ' INVENTORY REPORT</th></tr>';
    echo '<tr><th>#</th><th>Employee</th><th>Type</th><th>Brand</th><th>Serial</th><th>Property #</th><th>Division</th><th>Shelf Life</th></tr>';
    
    $excel_no = 1;
    while($row = $exportResult->fetch_assoc()) {
        echo "<tr>
                <td>{$excel_no}</td>
                <td>{$row['employeeName']}</td>
                <td>{$row['equipmentType']}</td>
                <td>{$row['brand']}</td>
                <td>{$row['serialNumber']}</td>
                <td>{$row['propertyNumber']}</td>
                <td>{$row['officeDivision']}</td>
                <td>{$row['shelfLife']}</td>
              </tr>";
        $excel_no++;
    }
    echo '</table>';
    exit();
}

// 3. Query for stats (For the cards)
$statsQuery = "SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN mark_as_done = 1 THEN 1 ELSE 0 END) as conducted,
                SUM(CASE WHEN mark_as_done = 0 THEN 1 ELSE 0 END) as pending
               FROM inv_inventory $whereSql";

$result = $conn->query($statsQuery);
$stats = $result->fetch_assoc();

$total = $stats['total'] ?: 0;
$conducted = $stats['conducted'] ?: 0;
$pending = $stats['pending'] ?: 0;
$percentage = ($total > 0) ? round(($conducted / $total) * 100, 2) : 0;

// --- PAGINATION LOGIC ---
$recordsPerPage = 20; // Number of items per page
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Determine status filter for the table
$statusFilter = ($viewMode == 'pending') ? 0 : 1;

// Count total records for this specific filtered view to calculate pages
$countSql = "SELECT COUNT(*) as count FROM inv_inventory $whereSql " . (empty($whereSql) ? "WHERE" : "AND") . " mark_as_done = $statusFilter";
$countRes = $conn->query($countSql);
$totalRecordsForView = $countRes->fetch_assoc()['count'];
$totalPages = ceil($totalRecordsForView / $recordsPerPage);

// 4. Query for the DYNAMIC TABLE with LIMIT and OFFSET
$tableQuery = "SELECT * FROM inv_inventory $whereSql " . (empty($whereSql) ? "WHERE" : "AND") . " mark_as_done = $statusFilter ORDER BY employeeName ASC LIMIT $offset, $recordsPerPage";
$displayList = $conn->query($tableQuery);

// 5. Fetch unique values for filters (Excluding NULL/Empty)
$equipmentTypes = $conn->query("SELECT DISTINCT equipmentType FROM inv_inventory WHERE equipmentType IS NOT NULL AND equipmentType != '' ORDER BY equipmentType");
$shelfLifeCategories = $conn->query("SELECT DISTINCT shelfLife FROM inv_inventory WHERE shelfLife IS NOT NULL AND shelfLife != '' ORDER BY shelfLife");
$divisions = $conn->query("SELECT DISTINCT officeDivision FROM inv_inventory WHERE officeDivision IS NOT NULL AND officeDivision != '' ORDER BY officeDivision");

// Helper function to build pagination URLs while keeping filters
function getPageUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return "?" . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Comprehensive Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.04); transition: transform 0.2s; }
        .clickable-card { cursor: pointer; }
        .clickable-card:hover { transform: scale(1.02); }
        .active-view { border: 2px solid #0d6efd !important; background-color: #f0f7ff !important; }
        .stat-val { font-size: 2.5rem; font-weight: 800; }
        .percentage-container { position: relative; width: 180px; margin: 0 auto; }
        .percentage-text { 
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-size: 1.5rem; font-weight: bold; color: #333;
        }
        .table-section { background: white; border-radius: 15px; padding: 25px; margin-top: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .pagination { margin-bottom: 0; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-chart-pie text-primary me-2"></i>Inventory Analytics</h4>
        <div class="btn-group">
            <button class="btn btn-outline-success" onclick="exportExcel()">
                <i class="fas fa-file-excel me-1"></i> Export Full List to Excel
            </button>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="view" value="<?= $viewMode ?>">
            <input type="hidden" name="page" value="1"> 
            
            <div class="col-md-3">
                <label class="form-label small fw-bold">Equipment Type</label>
                <select name="equipmentType" class="form-select form-select-sm">
                    <option value="all">All Equipment</option>
                    <?php while($row = $equipmentTypes->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['equipmentType']) ?>" <?= $filterEquipment == $row['equipmentType'] ? 'selected' : '' ?>><?= htmlspecialchars($row['equipmentType']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Shelf Life</label>
                <select name="shelfLife" class="form-select form-select-sm">
                    <option value="all">All Categories</option>
                    <?php while($row = $shelfLifeCategories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['shelfLife']) ?>" <?= $filterShelfLife == $row['shelfLife'] ? 'selected' : '' ?>><?= htmlspecialchars($row['shelfLife']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Division</label>
                <select name="officeDivision" class="form-select form-select-sm">
                    <option value="all">All Divisions</option>
                    <?php while($row = $divisions->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['officeDivision']) ?>" <?= $filterOfficeDivision == $row['officeDivision'] ? 'selected' : '' ?>><?= htmlspecialchars($row['officeDivision']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-lg-3">
            <div class="card p-4 text-center h-100">
                <h6 class="text-muted small fw-bold mb-3">OVERALL COMPLETION</h6>
                <div class="percentage-container">
                    <canvas id="progressChart"></canvas>
                    <div class="percentage-text"><?= $percentage ?>%</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card clickable-card p-4 border-start border-success border-5 h-100 <?= $viewMode == 'conducted' ? 'active-view' : '' ?>" onclick="switchView('conducted')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-success fw-bold mb-1">DONE (CONDUCTED)</h6>
                        <div class="stat-val text-success"><?= $conducted ?></div>
                        <span class="text-muted small">Showing <?= $viewMode == 'conducted' ? 'Table' : 'Summary' ?></span>
                    </div>
                    <i class="fas fa-check-circle fs-1 text-success opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card clickable-card p-4 border-start border-warning border-5 h-100 <?= $viewMode == 'pending' ? 'active-view' : '' ?>" onclick="switchView('pending')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-warning fw-bold mb-1">NOT DONE (PENDING)</h6>
                        <div class="stat-val text-warning"><?= $pending ?></div>
                        <span class="text-muted small">Showing <?= $viewMode == 'pending' ? 'Table' : 'Summary' ?></span>
                    </div>
                    <i class="fas fa-exclamation-triangle fs-1 text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="table-section shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                <?= ($viewMode == 'pending') ? '<i class="fas fa-list text-warning me-2"></i>Pending List' : '<i class="fas fa-list text-success me-2"></i>Conducted List' ?>
                <small class="text-muted fw-normal ms-2">(Page <?= $currentPage ?> of <?= $totalPages ?: 1 ?>)</small>
            </h5>
            <span class="badge bg-secondary px-3 py-2 rounded-pill"><?= $totalRecordsForView ?> Records found</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th>Employee Name</th>
                        <th>Equipment Type</th>
                        <th>Brand</th>
                        <th>Property Number</th>
                        <th>Serial Number</th>
                        <th>Division</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($displayList->num_rows > 0): ?>
                        <?php 
                        $no = $offset + 1; // Correct numbering for current page
                        while($row = $displayList->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['employeeName']) ?></td>
                                <td><?= htmlspecialchars($row['equipmentType']) ?></td>
                                <td><?= htmlspecialchars($row['brand']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['propertyNumber']) ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars($row['serialNumber']) ?></td>
                                <td><?= htmlspecialchars($row['officeDivision']) ?></td>
                                <td>
                                    <?php if($row['mark_as_done'] == 1): ?>
                                        <span class="badge bg-success px-3">DONE</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3">PENDING</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No items found for this filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4 d-flex justify-content-center">
            <ul class="pagination pagination-sm">
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= getPageUrl($currentPage - 1) ?>"><i class="fas fa-chevron-left"></i></a>
                </li>

                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);

                for ($i = $start; $i <= $end; $i++):
                ?>
                    <li class="page-item <?= ($currentPage == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= getPageUrl($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= getPageUrl($currentPage + 1) ?>"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
    // 1. Progress Chart
    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Done', 'Pending'],
            datasets: [{
                data: [<?= $conducted ?>, <?= $pending ?>],
                backgroundColor: ['#198754', '#ffc107'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    // 2. Switch View Mode
    function switchView(mode) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', mode);
        url.searchParams.set('page', '1'); // Always reset to page 1 on view switch
        window.location.href = url.toString();
    }

    // 3. Export Excel
    function exportExcel() {
        const url = new URL(window.location.href);
        url.searchParams.set('export', 'excel');
        window.location.href = url.toString();
    }
</script>

</body>
</html>