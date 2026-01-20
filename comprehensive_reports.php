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
    // Check for 'unreconciled' instead of 'pending'
    $statusValue = ($viewMode == 'unreconciled') ? 0 : 1;
    $filename = ($viewMode == 'unreconciled') ? "Unreconciled_Inventory_" : "Conducted_Inventory_";
    
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
// Changed alias 'pending' to 'unreconciled'
$statsQuery = "SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN mark_as_done = 1 THEN 1 ELSE 0 END) as conducted,
                SUM(CASE WHEN mark_as_done = 0 THEN 1 ELSE 0 END) as unreconciled
               FROM inv_inventory $whereSql";

$result = $conn->query($statsQuery);
$stats = $result->fetch_assoc();

$total = $stats['total'] ?: 0;
$conducted = $stats['conducted'] ?: 0;
$unreconciled = $stats['unreconciled'] ?: 0; // Updated variable name
$percentage = ($total > 0) ? round(($conducted / $total) * 100, 2) : 0;

// --- PAGINATION LOGIC ---
$recordsPerPage = 20; // Number of items per page
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Determine status filter for the table (unreconciled = 0, conducted = 1)
$statusFilter = ($viewMode == 'unreconciled') ? 0 : 1;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --gray-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05), 
                        0 5px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1), 
                        0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .header-gradient {
            background: var(--primary-gradient);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 25px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
        }
        
        .stat-card.success {
            background: var(--success-gradient);
            color: white;
        }
        
        .stat-card.warning {
            background: var(--warning-gradient);
            color: #333;
        }
        
        .stat-card.default {
            background: var(--gray-gradient);
        }
        
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.2;
        }
        
        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
            margin: 10px 0;
        }
        
        .progress-ring {
            position: relative;
            width: 160px;
            height: 160px;
            margin: 0 auto;
        }
        
        .progress-percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-weight: 800;
            color: #333;
        }
        
        .filter-pill {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50px;
            padding: 15px 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .form-select-custom {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 15px;
            background: white;
            transition: all 0.3s;
        }
        
        .form-select-custom:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
        }
        
        .table-modern {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .table-modern thead {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        
        .table-modern th {
            border: none;
            font-weight: 600;
            color: #4a5568;
            padding: 18px 15px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-modern td {
            padding: 18px 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .table-modern tbody tr {
            transition: all 0.2s;
        }
        
        .table-modern tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.002);
        }
        
        .badge-modern {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .pagination-modern .page-link {
            border: none;
            color: #667eea;
            margin: 0 5px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .pagination-modern .page-link:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .view-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .view-indicator.success { background: #43e97b; }
        .view-indicator.warning { background: #fa709a; }
        
        .animated-bg {
            animation: gradientShift 15s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @media (max-width: 768px) {
            .stat-value { font-size: 2rem; }
            .progress-ring { width: 120px; height: 120px; }
            .progress-percentage { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="header-gradient animated-bg">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-2"><i class="fas fa-chart-network me-3"></i>Inventory Analytics Dashboard</h1>
                <p class="opacity-90 mb-0">Comprehensive view of inventory status with real-time filtering and reporting</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-light btn-modern" onclick="exportExcel()">
                    <i class="fas fa-file-export me-2"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>

    <div class="filter-pill glass-card">
        <form method="GET" class="row g-3 align-items-center">
            <input type="hidden" name="view" value="<?= $viewMode ?>">
            <input type="hidden" name="page" value="1">
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-muted mb-2">
                    <i class="fas fa-laptop me-2"></i>Equipment Type
                </label>
                <select name="equipmentType" class="form-select form-select-custom">
                    <option value="all">All Equipment</option>
                    <?php while($row = $equipmentTypes->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['equipmentType']) ?>" <?= $filterEquipment == $row['equipmentType'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['equipmentType']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-muted mb-2">
                    <i class="fas fa-clock me-2"></i>Shelf Life
                </label>
                <select name="shelfLife" class="form-select form-select-custom">
                    <option value="all">All Categories</option>
                    <?php while($row = $shelfLifeCategories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['shelfLife']) ?>" <?= $filterShelfLife == $row['shelfLife'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['shelfLife']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-muted mb-2">
                    <i class="fas fa-building me-2"></i>Division
                </label>
                <select name="officeDivision" class="form-select form-select-custom">
                    <option value="all">All Divisions</option>
                    <?php while($row = $divisions->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['officeDivision']) ?>" <?= $filterOfficeDivision == $row['officeDivision'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['officeDivision']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-bold text-muted mb-2">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-modern flex-grow-1">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card default glass-card">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="text-muted small fw-bold mb-2">OVERALL COMPLETION</h6>
                        <div class="progress-ring">
                            <canvas id="progressChart"></canvas>
                            <div class="progress-percentage"><?= $percentage ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card default glass-card">
                <h6 class="text-muted small fw-bold mb-2">TOTAL ITEMS</h6>
                <div class="stat-value text-dark"><?= $total ?></div>
                <p class="mb-0 text-muted small">All inventory items</p>
                <i class="fas fa-boxes stat-icon"></i>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success glass-card <?= $viewMode == 'conducted' ? 'active' : '' ?>" 
                 onclick="switchView('conducted')">
                <h6 class="small fw-bold mb-2">
                    <span class="view-indicator success"></span>CONDUCTED
                </h6>
                <div class="stat-value"><?= $conducted ?></div>
                <p class="mb-0 opacity-90">Completed inventory checks</p>
                <i class="fas fa-check-circle stat-icon"></i>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning glass-card <?= $viewMode == 'unreconciled' ? 'active' : '' ?>" 
                 onclick="switchView('unreconciled')">
                <h6 class="small fw-bold mb-2">
                    <span class="view-indicator warning"></span>UNRECONCILED
                </h6>
                <div class="stat-value"><?= $unreconciled ?></div>
                <p class="mb-0 opacity-90">Awaiting reconciliation</p>
                <i class="fas fa-clock stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="glass-card p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <?php if($viewMode == 'unreconciled'): ?>
                        <i class="fas fa-clock text-warning me-2"></i>Unreconciled Inventory Items
                    <?php else: ?>
                        <i class="fas fa-check-circle text-success me-2"></i>Conducted Inventory Items
                    <?php endif; ?>
                </h4>
                <p class="text-muted mb-0">
                    Page <?= $currentPage ?> of <?= $totalPages ?: 1 ?> • 
                    <span class="fw-bold"><?= $totalRecordsForView ?></span> records found
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark badge-modern">
                    <i class="fas fa-eye me-1"></i> <?= $viewMode == 'unreconciled' ? 'Unreconciled View' : 'Conducted View' ?>
                </span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Property #</th>
                        <th>Serial</th>
                        <th>Division</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($displayList->num_rows > 0): ?>
                        <?php 
                        $no = $offset + 1;
                        while($row = $displayList->fetch_assoc()): 
                        ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['employeeName']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark badge-modern">
                                        <?= htmlspecialchars($row['equipmentType']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['brand']) ?></td>
                                <td>
                                    <code class="bg-light p-2 rounded"><?= htmlspecialchars($row['propertyNumber']) ?></code>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($row['serialNumber']) ?></td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary badge-modern">
                                        <?= htmlspecialchars($row['officeDivision']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['mark_as_done'] == 1): ?>
                                        <span class="badge bg-success badge-modern">
                                            <i class="fas fa-check me-1"></i> DONE
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark badge-modern">
                                            <i class="fas fa-clock me-1"></i> UNRECONCILED
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No items found</h5>
                                    <p class="text-muted small">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="mt-4">
            <nav class="d-flex justify-content-center">
                <ul class="pagination pagination-modern">
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= getPageUrl($currentPage - 1) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
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
                        <a class="page-link" href="<?= getPageUrl($currentPage + 1) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Progress Chart
    const ctx = document.getElementById('progressChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 160, 0);
    gradient.addColorStop(0, '#667eea');
    gradient.addColorStop(1, '#764ba2');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            // UPDATED: Label
            labels: ['Completed', 'Unreconciled'],
            datasets: [{
                // UPDATED: Data Variable
                data: [<?= $conducted ?>, <?= $unreconciled ?>],
                backgroundColor: [gradient, '#e2e8f0'],
                borderWidth: 0,
                borderRadius: 10,
                cutout: '75%',
                spacing: 2
            }]
        },
        options: {
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' items';
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });

    // Switch View Mode
    function switchView(mode) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', mode);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Export Excel
    function exportExcel() {
        const url = new URL(window.location.href);
        url.searchParams.set('export', 'excel');
        window.location.href = url.toString();
    }

    // Clear Filters
    function clearFilters() {
        const url = new URL(window.location.href);
        url.searchParams.delete('equipmentType');
        url.searchParams.delete('shelfLife');
        url.searchParams.delete('officeDivision');
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.glass-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

</body>
</html>