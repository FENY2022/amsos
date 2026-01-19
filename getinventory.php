<?php
/**
 * Inventory Management System - Full Version
 * Features: Dynamic Chart.js (with legend figures), Filtering, and Pagination.
 */
require_once 'connect.php';

// 1. DATA PREPARATION (PHP)
$sessionOffice = $_SESSION['OfficeSRF'] ?? '';

// Fetch unique offices for the filter dropdown
$stmt_offices = $conn->prepare("SELECT DISTINCT office FROM inv_inventory WHERE office = ?");
$stmt_offices->bind_param("s", $sessionOffice);
$stmt_offices->execute();
$offices = $stmt_offices->get_result();

// Fetch unique divisions for the filter dropdown
$stmt_divisions = $conn->prepare("SELECT DISTINCT officeDivision FROM inv_inventory WHERE office = ?");
$stmt_divisions->bind_param("s", $sessionOffice);
$stmt_divisions->execute();
$divisions = $stmt_divisions->get_result();

// Fetch all inventory items for client-side filtering/charts
$stmt_data = $conn->prepare("SELECT employeeName, equipmentType, office, officeDivision FROM inv_inventory WHERE office = ?");
$stmt_data->bind_param("s", $sessionOffice);
$stmt_data->execute();
$result = $stmt_data->get_result();

$inventoryData = [];
while ($row = $result->fetch_assoc()) {
    $inventoryData[] = $row;
}

// Cleanup database connections
$stmt_offices->close();
$stmt_divisions->close();
$stmt_data->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --bg-color: #f5f7fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background-color: var(--bg-color); 
            color: #333; 
            font-family: 'Segoe UI', Tahoma, sans-serif;
            line-height: 1.6;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* --- UI COMPONENTS --- */
        header {
            background: linear-gradient(135deg, var(--primary), #1a252f);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { display: flex; align-items: center; gap: 15px; }
        .logo i { font-size: 2.2rem; color: var(--secondary); }

        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- FILTERS --- */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 0.85rem; font-weight: bold; color: #555; }
        .input-group input, .input-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        /* --- TABLE --- */
        .table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-size: 0.85rem; color: #777; text-transform: uppercase; }
        td { padding: 15px; border-top: 1px solid #eee; }
        tr:hover { background: #fcfcfc; }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            gap: 15px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn:hover:not(:disabled) { background: #f0f0f0; }
        .btn:disabled { opacity: 0.4; cursor: not-allowed; }

        @media print {
            .no-print { display: none !important; }
            .container { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div class="logo">
            <i class="fas fa-microchip"></i>
            <h1>Inventory Dashboard</h1>
        </div>
        <div class="no-print">
            <button class="btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </header>

    <div class="dashboard no-print">
        <div class="card">
            <h2><i class="fas fa-chart-pie"></i> Equipment Distribution</h2>
            <div style="height: 300px;"><canvas id="equipmentChart"></canvas></div>
        </div>
        <div class="card">
            <h2><i class="fas fa-chart-bar"></i> Office Distribution</h2>
            <div style="height: 300px;"><canvas id="officeChart"></canvas></div>
        </div>
    </div>

    <div class="filter-section no-print">
        <h2><i class="fas fa-search"></i> Search & Filters</h2>
        <div class="filter-grid">
            <div class="input-group">
                <label>Employee Name</label>
                <input type="text" id="searchName" placeholder="Search...">
            </div>
            <div class="input-group">
                <label>Office</label>
                <select id="searchOffice">
                    <option value="">All Offices</option>
                    <?php while($o = $offices->fetch_assoc()): ?>
                        <?php if (!empty($o['office'])): ?>
                            <option value="<?= htmlspecialchars($o['office']) ?>"><?= htmlspecialchars($o['office']) ?></option>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Division</label>
                <select id="searchDivision">
                    <option value="">All Divisions</option>
                    <?php while($d = $divisions->fetch_assoc()): ?>
                        <?php if (!empty($d['officeDivision'])): ?>
                            <option value="<?= htmlspecialchars($d['officeDivision']) ?>"><?= htmlspecialchars($d['officeDivision']) ?></option>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Equipment Type</th>
                    <th>Office</th>
                    <th>Division</th>
                </tr>
            </thead>
            <tbody id="inventoryBody"></tbody>
        </table>

        <div class="pagination no-print">
            <button id="prevBtn" class="btn"><i class="fas fa-chevron-left"></i></button>
            <span id="pageInfo">Page 1 of 1</span>
            <button id="nextBtn" class="btn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
// --- DATA SOURCE ---
const rawInventory = <?php echo json_encode($inventoryData); ?>;
let filteredData = [...rawInventory];
let currentPage = 1;
const rowsPerPage = 10;

let equipmentChart, officeChart;

// --- INITIALIZE ---
document.addEventListener('DOMContentLoaded', () => {
    setupCharts();
    setupFilters();
    updateUI();
});

function setupFilters() {
    ['searchName', 'searchOffice', 'searchDivision'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => {
            const name = document.getElementById('searchName').value.toLowerCase();
            const office = document.getElementById('searchOffice').value;
            const division = document.getElementById('searchDivision').value;

            filteredData = rawInventory.filter(item => {
                return item.employeeName.toLowerCase().includes(name) &&
                       (office === "" || item.office === office) &&
                       (division === "" || item.officeDivision === division);
            });

            currentPage = 1;
            updateUI();
        });
    });

    document.getElementById('prevBtn').onclick = () => { if(currentPage > 1) { currentPage--; updateUI(); } };
    document.getElementById('nextBtn').onclick = () => { if(currentPage * rowsPerPage < filteredData.length) { currentPage++; updateUI(); } };
}

function updateUI() {
    renderTable();
    updateCharts();
}

function renderTable() {
    const tbody = document.getElementById('inventoryBody');
    tbody.innerHTML = '';
    
    const start = (currentPage - 1) * rowsPerPage;
    const currentItems = filteredData.slice(start, start + rowsPerPage);

    if (currentItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">No records found.</td></tr>';
    } else {
        currentItems.forEach(item => {
            tbody.innerHTML += `<tr>
                <td>${item.employeeName}</td>
                <td>${item.equipmentType}</td>
                <td>${item.office}</td>
                <td>${item.officeDivision}</td>
            </tr>`;
        });
    }

    const totalPages = Math.ceil(filteredData.length / rowsPerPage) || 1;
    document.getElementById('pageInfo').innerText = `Page ${currentPage} of ${totalPages}`;
    document.getElementById('prevBtn').disabled = (currentPage === 1);
    document.getElementById('nextBtn').disabled = (currentPage === totalPages);
}

// --- CHART.JS ---
function setupCharts() {
    // Doughnut Chart Setup
    const eqCtx = document.getElementById('equipmentChart').getContext('2d');
    equipmentChart = new Chart(eqCtx, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: [] }] },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        // CUSTOM: Display the count (figure) besides the color
                        generateLabels: (chart) => {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => ({
                                    text: `${data.datasets[0].data[i]} - ${label}`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                    lineWidth: 0,
                                    index: i
                                }));
                            }
                            return [];
                        }
                    }
                }
            }
        }
    });

    // Bar Chart Setup
    const offCtx = document.getElementById('officeChart').getContext('2d');
    officeChart = new Chart(offCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Total Items', data: [], backgroundColor: '#34495e' }] },
        options: {
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });
}

function updateCharts() {
    // 1. Equipment Distribution (Doughnut)
    const eqCounts = {};
    filteredData.forEach(item => eqCounts[item.equipmentType] = (eqCounts[item.equipmentType] || 0) + 1);
    
    // Expanded Palette for unique colors
    const palette = [
        '#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6', '#1abc9c', '#d35400', '#34495e',
        '#e91e63', '#00bcd4', '#ffc107', '#8bc34a', '#ff5722', '#607d8b', '#9c27b0', '#ffeb3b',
        '#009688', '#f06292', '#4db6ac', '#ffd54f', '#fb8c00', '#795548', '#afb42b', '#546e7a'
    ];

    equipmentChart.data.labels = Object.keys(eqCounts);
    equipmentChart.data.datasets[0].data = Object.values(eqCounts);
    equipmentChart.data.datasets[0].backgroundColor = palette.slice(0, Object.keys(eqCounts).length);
    equipmentChart.update();

    // 2. Office Distribution (Bar)
    const offCounts = {};
    filteredData.forEach(item => offCounts[item.office] = (offCounts[item.office] || 0) + 1);
    officeChart.data.labels = Object.keys(offCounts);
    officeChart.data.datasets[0].data = Object.values(offCounts);
    officeChart.update();
}
</script>
</body>
</html>