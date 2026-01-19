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
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
            --success: #27ae60;
            --warning: #f39c12;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--dark));
            color: white;
            padding: 20px 0;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: var(--secondary);
        }
        
        .logo h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .controls {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #219653;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h2 {
            margin-bottom: 20px;
            color: var(--primary);
            border-bottom: 2px solid var(--light);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card h2 i {
            color: var(--secondary);
        }
        
        .filter-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .filter-group input, 
        .filter-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .filter-group input:focus, 
        .filter-group select:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: linear-gradient(to right, var(--primary), var(--dark));
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        tbody tr {
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        td {
            padding: 15px;
            color: #555;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            gap: 10px;
        }
        
        .pagination button {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .pagination button:hover:not(:disabled) {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }
        
        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .page-info {
            margin: 0 15px;
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .print-only {
                display: block;
                margin-bottom: 20px;
                text-align: center;
                font-size: 1.2rem;
                font-weight: bold;
            }
            
            body {
                padding: 20px;
            }
            
            .card, .filter-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .controls {
                width: 100%;
                justify-content: center;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>

    <?php
    require_once 'connect.php';

    // Session logic
    $sessionOffice = $_SESSION['OfficeSRF'] ?? '';

    $office_sql = "SELECT DISTINCT office FROM inv_inventory WHERE office = ?";
    $stmt_offices = $conn->prepare($office_sql);
    $stmt_offices->bind_param("s", $sessionOffice);
    $stmt_offices->execute();
    $offices = $stmt_offices->get_result();

    $division_sql = "SELECT DISTINCT officeDivision FROM inv_inventory WHERE office = ?";
    $stmt_divisions = $conn->prepare($division_sql);
    $stmt_divisions->bind_param("s", $sessionOffice);
    $stmt_divisions->execute();
    $divisions = $stmt_divisions->get_result();

    $query = "SELECT employeeName, equipmentType, office, officeDivision FROM inv_inventory WHERE office = ?";
    $stmt_data = $conn->prepare($query);
    $stmt_data->bind_param("s", $sessionOffice);
    $stmt_data->execute();
    $result = $stmt_data->get_result();
    $inventoryData = [];
    while ($row = $result->fetch_assoc()) {
        $inventoryData[] = $row;
    }
    
    echo "<script>var inventoryData = " . json_encode($inventoryData) . ";</script>";

    $stmt_offices->close();
    $stmt_divisions->close();
    $stmt_data->close();
    ?>
</head>
<body>

<div class="container">
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-laptop-house"></i>
                <h1>Inventory Management System</h1>
            </div>
            <div class="controls">
                <button class="btn btn-primary" onclick="printTable()">
                    <i class="fas fa-print"></i> Print Inventory
                </button>
                <button class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> New Item
                </button>
                <button class="btn btn-warning">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </header>
    
    <div class="dashboard no-print">
        <div class="card">
            <h2><i class="fas fa-chart-bar"></i> Equipment Distribution</h2>
            <canvas id="inventoryChart"></canvas>
        </div>
        <div class="card">
            <h2><i class="fas fa-building"></i> Office Distribution</h2>
            <canvas id="officeChart"></canvas>
        </div>
    </div>
    
    <div class="filter-container no-print">
        <h2><i class="fas fa-filter"></i> Filter Inventory</h2>
        <div class="filter-grid">
            <div class="filter-group">
                <label for="employeeNameInput">Employee Name</label>
                <input type="text" id="employeeNameInput" placeholder="Search employee...">
            </div>
            <div class="filter-group">
                <label for="officeDropdown">Office</label>
                <select id="officeDropdown">
                    <option value="">Select Office</option>
                    <?php 
                    $offices->data_seek(0);
                    while ($office = $offices->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($office['office']) ?>"><?= htmlspecialchars($office['office']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="officeDivisionDropdown">Office Division</label>
                <select id="officeDivisionDropdown">
                    <option value="">Select Office Division</option>
                    <?php 
                    $divisions->data_seek(0);
                    while ($division = $divisions->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($division['officeDivision']) ?>"><?= htmlspecialchars($division['officeDivision']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>
    
    <div class="print-only">
        <h2>Inventory Report - Generated on <span id="printDate"></span></h2>
    </div>
    
    <div class="table-container" id="tableContainer">
        <table>
            <thead>
                <tr>
                    <th>Employee's Name</th>
                    <th>Type of ICT Equipment</th>
                    <th>Office</th>
                    <th>Office Division</th>
                </tr>
            </thead>
            <tbody id="inventoryTableBody"></tbody>
        </table>
        
        <div class="pagination no-print">
            <button id="firstPage" disabled><i class="fas fa-angle-double-left"></i></button>
            <button id="prevPage" disabled><i class="fas fa-angle-left"></i></button>
            <span class="page-info">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
            <button id="nextPage" disabled><i class="fas fa-angle-right"></i></button>
            <button id="lastPage" disabled><i class="fas fa-angle-double-right"></i></button>
        </div>
    </div>
    
    <footer>
        <p>Inventory Management System &copy; 2025 | Designed with Feny <i class="fas fa-heart" style="color: var(--accent);"></i></p>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeNameInput = document.getElementById('employeeNameInput');
    const officeDropdown = document.getElementById('officeDropdown');
    const officeDivisionDropdown = document.getElementById('officeDivisionDropdown');
    const chartCtx = document.getElementById('inventoryChart').getContext('2d');
    const officeChartCtx = document.getElementById('officeChart').getContext('2d');
    const inventoryTableBody = document.getElementById('inventoryTableBody');

    let currentPage = 1;
    const rowsPerPage = 5;
    let filteredData = [...inventoryData];

    const currentPageEl = document.getElementById('currentPage');
    const totalPagesEl = document.getElementById('totalPages');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const firstPageBtn = document.getElementById('firstPage');
    const lastPageBtn = document.getElementById('lastPage');

    let inventoryChart;
    let officeChart;

    renderTable();
    setupCharts();
    setupEventListeners();
    updatePagination();

    function setupEventListeners() {
        employeeNameInput.addEventListener('input', applyFilters);
        officeDropdown.addEventListener('change', applyFilters);
        officeDivisionDropdown.addEventListener('change', applyFilters);

        prevPageBtn.addEventListener('click', () => changePage(currentPage - 1));
        nextPageBtn.addEventListener('click', () => changePage(currentPage + 1));
        firstPageBtn.addEventListener('click', () => changePage(1));
        lastPageBtn.addEventListener('click', () => changePage(Math.ceil(filteredData.length / rowsPerPage)));
    }

    function applyFilters() {
        const employeeName = employeeNameInput.value.toLowerCase();
        const selectedOffice = officeDropdown.value.toLowerCase();
        const selectedDivision = officeDivisionDropdown.value.toLowerCase();

        filteredData = inventoryData.filter(item => {
            const matchesName = item.employeeName.toLowerCase().includes(employeeName);
            const matchesOffice = !selectedOffice || item.office.toLowerCase().includes(selectedOffice);
            const matchesDivision = !selectedDivision || item.officeDivision.toLowerCase().includes(selectedDivision);
            return matchesName && matchesOffice && matchesDivision;
        });
        
        currentPage = 1;
        renderTable();
        updatePagination();
        updateCharts();
    }

    function renderTable() {
        inventoryTableBody.innerHTML = '';
        const startIndex = (currentPage - 1) * rowsPerPage;
        const pageData = filteredData.slice(startIndex, startIndex + rowsPerPage);
        
        if (pageData.length === 0) {
            inventoryTableBody.innerHTML = `<tr><td colspan="4" style="text-align: center;">No inventory items found</td></tr>`;
            return;
        }
        
        pageData.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${item.employeeName}</td><td>${item.equipmentType}</td><td>${item.office}</td><td>${item.officeDivision}</td>`;
            inventoryTableBody.appendChild(row);
        });
    }

    function changePage(page) {
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentPage = page;
        renderTable();
        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredData.length / rowsPerPage);
        currentPageEl.textContent = currentPage;
        totalPagesEl.textContent = totalPages === 0 ? 1 : totalPages;
        prevPageBtn.disabled = currentPage === 1 || totalPages === 0;
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
        firstPageBtn.disabled = currentPage === 1 || totalPages === 0;
        lastPageBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    function setupCharts() {
        // Equipment distribution chart with CUSTOM LEGEND LABELS
        inventoryChart = new Chart(chartCtx, {
            type: 'doughnut',
            data: getEquipmentChartData(filteredData),
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            // Custom function to add the numbers next to labels
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${value} ${label}`, // Adds the figure besides the color
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].backgroundColor[i],
                                            lineWidth: 0,
                                            hidden: isNaN(data.datasets[0].data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    title: { display: true, text: 'Equipment Distribution' }
                }
            }
        });

        officeChart = new Chart(officeChartCtx, {
            type: 'bar',
            data: getOfficeChartData(filteredData),
            options: {
                responsive: true,
                plugins: { legend: { display: false }, title: { display: true, text: 'Items per Office' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function updateCharts() {
        inventoryChart.data = getEquipmentChartData(filteredData);
        inventoryChart.update();
        officeChart.data = getOfficeChartData(filteredData);
        officeChart.update();
    }

    function getEquipmentChartData(data) {
        const equipmentCounts = data.reduce((acc, item) => {
            acc[item.equipmentType] = (acc[item.equipmentType] || 0) + 1;
            return acc;
        }, {});
        return {
            labels: Object.keys(equipmentCounts),
            datasets: [{
                data: Object.values(equipmentCounts),
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#d35400', '#34495e'],
                borderWidth: 1
            }]
        };
    }

    function getOfficeChartData(data) {
        const officeCounts = data.reduce((acc, item) => {
            acc[item.office] = (acc[item.office] || 0) + 1;
            return acc;
        }, {});
        return {
            labels: Object.keys(officeCounts),
            datasets: [{ label: 'Number of Items', data: Object.values(officeCounts), backgroundColor: '#3498db', borderWidth: 1 }]
        };
    }

    window.printTable = function () {
        const noPrintElements = document.querySelectorAll('.no-print');
        noPrintElements.forEach(el => el.style.display = 'none');
        document.querySelector('.print-only').style.display = 'block';
        window.print();
        noPrintElements.forEach(el => el.style.display = '');
        document.querySelector('.print-only').style.display = 'none';
    };
});
</script>
</body>
</html>