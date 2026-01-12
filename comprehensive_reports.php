<?php
// Include database connection
require_once 'connect.php'; 

// Initialize filter variables
$filterEquipment = $_GET['equipmentType'] ?? 'all';
$filterShelfLife = $_GET['shelfLife'] ?? 'all';
$filterDivision = $_GET['division'] ?? 'all';

// Build SQL WHERE clause based on filters
$whereClauses = [];
if ($filterEquipment !== 'all') {
    $whereClauses[] = "equipmentType = '" . $conn->real_escape_string($filterEquipment) . "'";
}
if ($filterShelfLife !== 'all') {
    $whereClauses[] = "shelfLife = '" . $conn->real_escape_string($filterShelfLife) . "'";
}
if ($filterDivision !== 'all') {
    $whereClauses[] = "division = '" . $conn->real_escape_string($filterDivision) . "'";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Query for stats
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

// Fetch unique values for filters
$equipmentTypes = $conn->query("SELECT DISTINCT equipmentType FROM inv_inventory ORDER BY equipmentType");
$shelfLifeCategories = $conn->query("SELECT DISTINCT shelfLife FROM inv_inventory ORDER BY shelfLife");
$divisions = $conn->query("SELECT DISTINCT division FROM inv_inventory ORDER BY division");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Summary - ICT-AMSOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .percentage-ring { position: relative; width: 200px; margin: 0 auto; }
        .percentage-label { 
            position: absolute; top: 50%; left: 50%; 
            transform: translate(-50%, -50%); 
            font-size: 2rem; font-weight: bold; color: #0d6efd; 
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold"><i class="bi bi-graph-up-arrow me-2"></i>Inventory Completion Analysis</h2>
            <p class="text-muted">Real-time percentage of conducted inventories based on active filters.</p>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Equipment Type</label>
                <select name="equipmentType" class="form-select">
                    <option value="all">All Equipment</option>
                    <?php while($row = $equipmentTypes->fetch_assoc()): ?>
                        <option value="<?= $row['equipmentType'] ?>" <?= $filterEquipment == $row['equipmentType'] ? 'selected' : '' ?>>
                            <?= $row['equipmentType'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Shelf Life</label>
                <select name="shelfLife" class="form-select">
                    <option value="all">All Categories</option>
                    <?php while($row = $shelfLifeCategories->fetch_assoc()): ?>
                        <option value="<?= $row['shelfLife'] ?>" <?= $filterShelfLife == $row['shelfLife'] ? 'selected' : '' ?>>
                            <?= $row['shelfLife'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Division</label>
                <select name="division" class="form-select">
                    <option value="all">All Divisions</option>
                    <?php while($row = $divisions->fetch_assoc()): ?>
                        <option value="<?= $row['division'] ?>" <?= $filterDivision == $row['division'] ? 'selected' : '' ?>>
                            <?= $row['division'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 p-4 text-center">
                <h5 class="mb-4">Inventory Progress</h5>
                <div class="percentage-ring">
                    <canvas id="progressChart"></canvas>
                    <div class="percentage-label"><?= $percentage ?>%</div>
                </div>
                <p class="mt-4 text-muted small">Showing percentage for <br><strong><?= strtoupper($filterDivision) ?></strong></p>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card stat-card bg-primary text-white p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="small opacity-75">Total Items</div>
                                <h3 class="fw-bold mb-0"><?= $total ?></h3>
                            </div>
                            <i class="bi bi-box-seam fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card stat-card bg-success text-white p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="small opacity-75">Conducted</div>
                                <h3 class="fw-bold mb-0"><?= $conducted ?></h3>
                            </div>
                            <i class="bi bi-check-circle fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-4">
                    <div class="card stat-card bg-warning text-dark p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="small opacity-75">Not Conducted</div>
                                <h3 class="fw-bold mb-0"><?= $pending ?></h3>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1 opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 p-4">
                <h6 class="fw-bold">Report Summary</h6>
                <p class="mb-0 text-muted">
                    Based on your selection, a total of <strong><?= $conducted ?></strong> items have been marked as inventory-ready. 
                    This results in a <strong><?= $percentage ?>%</strong> completion rate. 
                    Target: Ensure all items under <strong><?= $filterEquipment ?></strong> are processed.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Chart.js Doughnut Chart
    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Conducted', 'Pending'],
            datasets: [{
                data: [<?= $conducted ?>, <?= $pending ?>],
                backgroundColor: ['#198754', '#dee2e6'],
                hoverBackgroundColor: ['#157347', '#ced4da'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>