<?php
/**
 * AMSOS Analytics Dashboard
 * Combines Backend API logic and Frontend UI in one file.
 */

require 'connect.php'; // Ensure this file exists and connects to your database

// --------------------------------------------------------------------------
// PART 1: BACKEND API HANDLER
// --------------------------------------------------------------------------
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    // Helper: Calculate if item is older than 5 years
    function checkAge($yearAcquired) {
        $currentYear = date("Y");
        $acquired = intval($yearAcquired);
        // If year is invalid (e.g. 0 or empty), treat as "Unknown" or handle specifically
        if ($acquired == 0) return 'Unknown'; 
        $age = $currentYear - $acquired;
        return $age >= 5 ? 'Above 5 Years' : 'Below 5 Years';
    }

    switch ($action) {
        // Requirement 1: Computers/Laptop/Printer Above and Below 5yrs
        case 'age_analysis':
            $query = "SELECT equipmentType, yearAcquired, COUNT(*) as count 
                      FROM inv_inventory 
                      WHERE equipmentType IN ('Desktop Computer', 'Desktop Computers', 'Laptop', 'Printer') 
                      GROUP BY equipmentType, yearAcquired";
            $result = mysqli_query($con, $query);
            
            $data = [
                'above_5' => 0,
                'below_5' => 0,
                'breakdown' => [] // Optional: detailed breakdown by type
            ];

            while($row = mysqli_fetch_assoc($result)) {
                $status = checkAge($row['yearAcquired']);
                if($status == 'Above 5 Years') $data['above_5'] += $row['count'];
                elseif($status == 'Below 5 Years') $data['below_5'] += $row['count'];
            }
            echo json_encode($data);
            break;

        // Requirement 2: List of updated personnel (Last 50 entries)
        case 'updated_personnel':
            $query = "SELECT employeeName, division, yearAcquired, equipmentType 
                      FROM inv_inventory 
                      WHERE employeeName != ''
                      ORDER BY id DESC LIMIT 50";
            $result = mysqli_query($con, $query);
            $data = [];
            while($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        // Requirement 3: Count every ICT Equipment Per division
        case 'division_stats':
            $query = "SELECT division, COUNT(*) as count 
                      FROM inv_inventory 
                      WHERE division != '' AND division IS NOT NULL
                      GROUP BY division
                      ORDER BY count DESC";
            $result = mysqli_query($con, $query);
            $labels = [];
            $values = [];
            while($row = mysqli_fetch_assoc($result)) {
                $labels[] = $row['division'];
                $values[] = $row['count'];
            }
            echo json_encode(['labels' => $labels, 'values' => $values]);
            break;

        // Requirement 4: Total Inventoried vs Procured CY 2025
        case 'procurement_stats':
            // Total
            $q1 = mysqli_query($con, "SELECT COUNT(*) as c FROM inv_inventory");
            $r1 = mysqli_fetch_assoc($q1);
            
            // Procured 2025
            $q2 = mysqli_query($con, "SELECT COUNT(*) as c FROM inv_inventory WHERE yearAcquired = '2025'");
            $r2 = mysqli_fetch_assoc($q2);
            
            echo json_encode([
                'total_inventoried' => $r1['c'],
                'procured_2025' => $r2['c']
            ]);
            break;

        // Requirement 5: Generate per employee of specific division
        case 'get_division_employees':
            $division = isset($_GET['division']) ? mysqli_real_escape_string($con, $_GET['division']) : '';
            $query = "SELECT * FROM inv_inventory WHERE division = '$division' ORDER BY employeeName ASC";
            $result = mysqli_query($con, $query);
            $data = [];
            while($row = mysqli_fetch_assoc($result)) {
                // Add calculated fields
                $row['age_status'] = checkAge($row['yearAcquired']);
                $row['full_specs'] = trim($row['brand'] . ' ' . $row['computer_specs']);
                $data[] = $row;
            }
            echo json_encode($data);
            break;

        // Helper: Get list of Divisions for the dropdown
        case 'get_divisions_list':
            $query = "SELECT DISTINCT division FROM inv_inventory WHERE division != '' ORDER BY division";
            $result = mysqli_query($con, $query);
            $data = [];
            while($row = mysqli_fetch_assoc($result)) {
                $data[] = $row['division'];
            }
            echo json_encode($data);
            break;

        default:
            echo json_encode(['error' => 'Invalid Action']);
    }
    // Stop execution so we don't render HTML when an API call is made
    exit;
}
?>

<?php include 'navbar.php'; // Optional: Include your navbar if you have one ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Inventory Analytics Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); margin-bottom: 1.5rem; transition: 0.3s; }
        .card:hover { transform: translateY(-3px); }
        .card-header { background-color: #fff; border-bottom: 1px solid #e3e6f0; font-weight: bold; color: #4e73df; border-radius: 10px 10px 0 0 !important; }
        .text-xs { font-size: .7rem; }
        .border-left-primary { border-left: .25rem solid #4e73df !important; }
        .border-left-success { border-left: .25rem solid #1cc88a !important; }
        .border-left-info { border-left: .25rem solid #36b9cc !important; }
        .border-left-warning { border-left: .25rem solid #f6c23e !important; }
        .text-gray-800 { color: #5a5c69 !important; }
        .icon-circle { height: 3rem; width: 3rem; border-radius: 100%; display: flex; align-items: center; justify-content: center; }
        
        /* Loading Overlay */
        #loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center; }
    </style>
</head>
<body>

<div id="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-pie me-2"></i>ICT Inventory Dashboard</h1>
        <button class="btn btn-sm btn-primary shadow-sm" onclick="window.print()"><i class="fas fa-print fa-sm text-white-50"></i> Print Report</button>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Inventoried</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="stat-total">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary text-white"><i class="fas fa-boxes fa-lg"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Procured CY 2025</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="stat-2025">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success text-white"><i class="fas fa-calendar-check fa-lg"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Equipment < 5 Years</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="stat-below5">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info text-white"><i class="fas fa-laptop-code fa-lg"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Equipment > 5 Years</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="stat-above5">0</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning text-white"><i class="fas fa-history fa-lg"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Equipment Distribution by Division</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="divisionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Lifecycle Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="height: 320px; position: relative;">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Detailed Reports</h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        <select class="form-select form-select-sm" id="divisionFilter" style="max-width: 200px;">
                            <option value="">Select Division</option>
                            </select>
                        <select class="form-select form-select-sm" id="reportType" style="max-width: 200px;">
                            <option value="detailed">Inventory by Division</option>
                            <option value="updated">Recently Updated Personnel</option>
                        </select>
                        <button class="btn btn-sm btn-success" onclick="generateReport()">Generate</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="reportTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr id="tableHeaderRow">
                            <th>Employee Name</th>
                            <th>Division</th>
                            <th>Type</th>
                            <th>Brand / Specs</th>
                            <th>Year</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
    let tableInstance = null;

    $(document).ready(function() {
        // Initialize dashboard
        loadStats();
        loadDivisions();
        
        // Load default report (Updated Personnel) on start
        $('#reportType').val('updated');
        generateReport();
    });

    // 1. Fetch Top Stats & Draw Charts
    function loadStats() {
        // Procurement & Total
        $.get('?action=procurement_stats', function(data) {
            $('#stat-total').text(data.total_inventoried);
            $('#stat-2025').text(data.procured_2025);
        });

        // Age Analysis (Text & Pie Chart)
        $.get('?action=age_analysis', function(data) {
            $('#stat-above5').text(data.above_5);
            $('#stat-below5').text(data.below_5);

            // Pie Chart
            new Chart(document.getElementById("ageChart"), {
                type: 'doughnut',
                data: {
                    labels: ["Below 5 Years", "Above 5 Years"],
                    datasets: [{
                        data: [data.below_5, data.above_5],
                        backgroundColor: ['#36b9cc', '#f6c23e'],
                        hoverBackgroundColor: ['#2c9faf', '#dda20a'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: { backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10 },
                    legend: { display: true, position: 'bottom' },
                    cutout: '70%',
                },
            });
        });

        // Division Stats (Bar Chart)
        $.get('?action=division_stats', function(data) {
            // Bar Chart
            new Chart(document.getElementById("divisionChart"), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Item Count",
                        backgroundColor: "#4e73df",
                        hoverBackgroundColor: "#2e59d9",
                        borderColor: "#4e73df",
                        data: data.values,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                    scales: {
                        x: { grid: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 10 } },
                        y: { ticks: { beginAtZero: true, maxTicksLimit: 5, padding: 10 } }
                    },
                    legend: { display: false }
                },
            });
            $('#loading-overlay').fadeOut();
        });
    }

    // 2. Populate Dropdown
    function loadDivisions() {
        $.get('?action=get_divisions_list', function(list) {
            list.forEach(div => {
                $('#divisionFilter').append(new Option(div, div));
            });
        });
    }

    // 3. Main Report Logic
    function generateReport() {
        const type = $('#reportType').val();
        const division = $('#divisionFilter').val();

        if (type === 'detailed' && !division) {
            alert('Please select a Division for the detailed inventory report.');
            return;
        }

        const url = type === 'updated' 
            ? '?action=updated_personnel' 
            : `?action=get_division_employees&division=${encodeURIComponent(division)}`;

        // Destroy old table
        if (tableInstance) {
            tableInstance.destroy();
            $('#tableBody').empty();
        }

        $('#loading-overlay').show();

        $.get(url, function(data) {
            let html = '';
            
            // Adjust headers based on report type
            if(type === 'updated') {
                $('#tableHeaderRow').html('<th>Employee Name</th><th>Division</th><th>Equipment</th><th>Year Acquired</th><th>Status</th>');
                
                data.forEach(row => {
                    html += `
                        <tr>
                            <td class="fw-bold">${row.employeeName || 'N/A'}</td>
                            <td>${row.division || 'N/A'}</td>
                            <td>${row.equipmentType || 'N/A'}</td>
                            <td>${row.yearAcquired || ''}</td>
                            <td><span class="badge bg-success">Updated Recently</span></td>
                        </tr>
                    `;
                });
            } else {
                // Detailed Division Report
                $('#tableHeaderRow').html('<th>Employee Name</th><th>Type</th><th>Specs/Brand</th><th>Year</th><th>Age Status</th><th>Category</th>');
                
                data.forEach(row => {
                    let badge = row.age_status === 'Above 5 Years' ? 'bg-warning text-dark' : 'bg-info';
                    html += `
                        <tr>
                            <td class="fw-bold">${row.employeeName}</td>
                            <td>${row.equipmentType}</td>
                            <td>${row.full_specs}</td>
                            <td>${row.yearAcquired}</td>
                            <td><span class="badge ${badge}">${row.age_status}</span></td>
                            <td>${row.rangeCategory || ''}</td>
                        </tr>
                    `;
                });
            }

            $('#tableBody').html(html);
            
            // Re-init DataTable
            let title = type === 'updated' ? 'Recently Updated Personnel' : `Inventory Report - ${division}`;
            
            tableInstance = $('#reportTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excel', className: 'btn btn-sm btn-success', title: title },
                    { extend: 'pdf', className: 'btn btn-sm btn-danger', title: title },
                    { extend: 'print', className: 'btn btn-sm btn-secondary', title: title }
                ],
                pageLength: 10,
                order: [[0, 'asc']]
            });
            
            $('#loading-overlay').fadeOut();
        });
    }
</script>

</body>
</html>