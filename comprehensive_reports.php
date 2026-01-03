<?php
// 1. DATABASE CONNECTION & LOGIC LAYER
include 'connect.php'; 

// --- A. Summary Statistics ---
$total_all = $conn->query("SELECT COUNT(*) FROM inv_inventory")->fetch_row()[0];
$inventoried = $conn->query("SELECT COUNT(*) FROM inv_inventory WHERE mark_as_done = '1'")->fetch_row()[0];
$procured_2025 = $conn->query("SELECT COUNT(*) FROM inv_inventory WHERE yearAcquired = '2025'")->fetch_row()[0];
$within_5 = $conn->query("SELECT COUNT(*) FROM inv_inventory WHERE shelfLife LIKE '%Within%'")->fetch_row()[0];
$beyond_5 = $conn->query("SELECT COUNT(*) FROM inv_inventory WHERE shelfLife LIKE '%Beyond%'")->fetch_row()[0];

// --- B. Age Category Report ---
$age_sql = "SELECT id, equipmentType, brand, specifications, yearAcquired, shelfLife, serialNumber, 
                   propertyNumber, accountablePerson, actualUser, officeDivision, remarks 
            FROM inv_inventory 
            WHERE equipmentType IN ('Desktop Computers', 'Laptop Computers', 'Printers')
            ORDER BY shelfLife DESC, yearAcquired DESC, equipmentType";
$age_result = $conn->query($age_sql);

// --- C. Personnel List ---
$personnel_sql = "SELECT actualUser, actualUserSex, actualUserStatusOfEmployment, 
                         GROUP_CONCAT(DISTINCT officeDivision SEPARATOR ', ') AS divisions,
                         COUNT(*) AS num_equip 
                  FROM inv_inventory 
                  WHERE actualUser != '' AND actualUser != '0'
                  GROUP BY actualUser, actualUserSex, actualUserStatusOfEmployment 
                  ORDER BY actualUser";
$personnel_result = $conn->query($personnel_sql);

// --- D. Statistics Counts ---
$div_count = $conn->query("SELECT officeDivision, COUNT(*) AS total FROM inv_inventory GROUP BY officeDivision ORDER BY total DESC");
$year_count = $conn->query("SELECT yearAcquired, COUNT(*) AS total FROM inv_inventory GROUP BY yearAcquired ORDER BY yearAcquired DESC");
$div_type_count = $conn->query("SELECT officeDivision, equipmentType, COUNT(*) AS total FROM inv_inventory GROUP BY officeDivision, equipmentType ORDER BY officeDivision, total DESC");

// --- E. Master Inventory List ---
$full_sql = "SELECT id, equipmentType, brand, yearAcquired, shelfLife, serialNumber, propertyNumber, 
                    accountablePerson, actualUser, officeDivision, remarks 
             FROM inv_inventory 
             ORDER BY officeDivision, actualUser, equipmentType";
$full_result = $conn->query($full_sql);

// --- F. Dropdown Data for Generator Tab ---
$div_list_sql = $conn->query("SELECT DISTINCT officeDivision FROM inv_inventory WHERE officeDivision != '' ORDER BY officeDivision ASC");
$emp_list_sql = $conn->query("SELECT DISTINCT actualUser FROM inv_inventory WHERE actualUser != '' AND actualUser != '0' ORDER BY actualUser ASC");
$report_sql = "SELECT id, equipmentType, brand, yearAcquired, serialNumber, propertyNumber, actualUser, officeDivision, remarks 
               FROM inv_inventory 
               ORDER BY officeDivision, actualUser";
$report_result = $conn->query($report_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMSOS | DENR ICT Inventory</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #435ebe;
            --bg-color: #f2f7ff;
            --text-color: #25396f;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: #333;
            padding-bottom: 50px;
        }
        /* Navbar */
        .navbar {
            background: #fff;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 0.8rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
        }
        .denr-logo {
            height: 45px;
            margin-right: 12px;
        }
        /* Stat Cards */
        .stat-card {
            background: #fff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.05);
        }
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .icon-primary { background: #eef2ff; color: #435ebe; }
        .icon-success { background: #ecfdf5; color: #10b981; }
        .icon-info { background: #eff6ff; color: #3b82f6; }
        .icon-danger { background: #fef2f2; color: #ef4444; }

        /* Navigation Pills */
        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 20px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 6px rgba(67, 94, 190, 0.3);
        }
        
        /* Content Card */
        .content-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.03);
            border: none;
            overflow: hidden;
        }

        /* Tables */
        table.dataTable thead th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef !important;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
            padding: 12px 10px;
        }
        
        /* Badges */
        .badge-soft {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .badge-soft-success { background-color: #d1fae5; color: #065f46; }
        .badge-soft-danger { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<nav class="navbar mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="logo/amsos.png" alt="DENR Logo" class="denr-logo">
            <div>
                <div style="font-size: 0.8em; line-height: 1.2;" class="text-secondary fw-normal">DENR Caraga</div>
                AMSOS <span class="fw-light">Inventory</span>
            </div>
        </a>
        <div class="d-flex align-items-center">
            <span class="text-muted small me-2">System Date:</span>
            <span class="fw-bold text-dark"><?php echo date('F d, Y'); ?></span>
        </div>
    </div>
</nav>

<div class="container">

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Equipment</h6><h2 class="mb-0 fw-bold text-dark"><?php echo number_format($total_all); ?></h2></div>
                    <div class="icon-box icon-primary"><i class="bi bi-pc-display"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1 text-uppercase small fw-bold">Audited/Done</h6><h2 class="mb-0 fw-bold text-dark"><?php echo number_format($inventoried); ?></h2></div>
                    <div class="icon-box icon-success"><i class="bi bi-clipboard-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1 text-uppercase small fw-bold">Procured 2025</h6><h2 class="mb-0 fw-bold text-dark"><?php echo number_format($procured_2025); ?></h2></div>
                    <div class="icon-box icon-info"><i class="bi bi-cart-plus"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted mb-1 text-uppercase small fw-bold">Beyond 5 Years</h6><h2 class="mb-0 fw-bold text-danger"><?php echo number_format($beyond_5); ?></h2></div>
                    <div class="icon-box icon-danger"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold text-dark">Reports & Analytics</h4>
    </div>
    
    <ul class="nav nav-pills mb-4" id="reportTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" id="age-tab" data-bs-toggle="tab" data-bs-target="#age" type="button"><i class="bi bi-hourglass-split me-2"></i>Shelf Life</button></li>
        <li class="nav-item"><button class="nav-link" id="personnel-tab" data-bs-toggle="tab" data-bs-target="#personnel" type="button"><i class="bi bi-people me-2"></i>Personnel List</button></li>
        <li class="nav-item"><button class="nav-link" id="generator-tab" data-bs-toggle="tab" data-bs-target="#generator" type="button"><i class="bi bi-funnel me-2"></i>Div/Emp Report</button></li>
        <li class="nav-item"><button class="nav-link" id="counts-tab" data-bs-toggle="tab" data-bs-target="#counts" type="button"><i class="bi bi-bar-chart me-2"></i>Statistics</button></li>
        <li class="nav-item"><button class="nav-link" id="detailed-tab" data-bs-toggle="tab" data-bs-target="#detailed" type="button"><i class="bi bi-list-ul me-2"></i>Master List</button></li>
    </ul>

    <div class="card content-card">
        <div class="card-body p-0">
            <div class="tab-content" id="reportTabContent">

                <div class="tab-pane fade show active p-4" id="age" role="tabpanel">
                    <div class="table-responsive">
                        <table id="age_table" class="table table-hover w-100">
                            <thead>
                                <tr>
                                    <th width="50">#</th> <th>Type/Brand</th> <th>Specifications</th>
                                    <th>Acquired</th> <th>Shelf Life</th> <th>User/Division</th> <th>Identifiers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while ($row = $age_result->fetch_assoc()): 
                                    $lifeClass = (strpos($row['shelfLife'], 'Beyond') !== false) ? 'badge-soft-danger' : 'badge-soft-success'; ?>
                                <tr>
                                    <td><?php echo $i++; ?></td> 
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['equipmentType']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['brand']); ?></small>
                                    </td>
                                    <td><div class="text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($row['specifications']); ?></div></td>
                                    <td><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                                    <td><span class="badge-soft <?php echo $lifeClass; ?>"><?php echo htmlspecialchars($row['shelfLife']); ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['actualUser']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['officeDivision']); ?></small>
                                    </td>
                                    <td>
                                        <div class="small">SN: <?php echo htmlspecialchars($row['serialNumber']); ?></div>
                                        <div class="small text-muted">PN: <?php echo htmlspecialchars($row['propertyNumber']); ?></div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="personnel" role="tabpanel">
                    <div class="table-responsive">
                        <table id="personnel_table" class="table table-hover w-100">
                            <thead>
                                <tr><th width="50">#</th> <th>Personnel Name</th> <th>Sex</th> <th>Employment</th> <th>Division(s)</th> <th class="text-center">Items</th></tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while ($row = $personnel_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td> 
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['actualUser']); ?></td>
                                    <td><?php echo htmlspecialchars($row['actualUserSex']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['actualUserStatusOfEmployment']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['divisions']); ?></td>
                                    <td class="text-center"><span class="badge rounded-pill bg-secondary"><?php echo $row['num_equip']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="generator" role="tabpanel">
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-funnel-fill me-2"></i>Report Filter</h5>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <select class="form-select" id="divisionFilter">
                                        <option value="">All Divisions</option>
                                        <?php while($d = $div_list_sql->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($d['officeDivision']); ?>"><?php echo htmlspecialchars($d['officeDivision']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select class="form-select" id="employeeFilter">
                                        <option value="">All Employees</option>
                                        <?php while($e = $emp_list_sql->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($e['actualUser']); ?>"><?php echo htmlspecialchars($e['actualUser']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2"><button class="btn btn-outline-secondary w-100" onclick="resetFilters()">Reset</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="generator_table" class="table table-hover w-100">
                            <thead><tr><th width="50">#</th><th>Division</th><th>Employee</th><th>Equipment</th><th>Property No.</th><th>Serial No.</th></tr></thead>
                            <tbody>
                                <?php $i = 1; while ($row = $report_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td> 
                                    <td><?php echo htmlspecialchars($row['officeDivision']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['actualUser']); ?></td>
                                    <td><?php echo htmlspecialchars($row['equipmentType']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['brand']); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['propertyNumber']); ?></td>
                                    <td><?php echo htmlspecialchars($row['serialNumber']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="counts" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card border h-100">
                                <div class="card-header fw-bold bg-light">Equipment Count by Division</div>
                                <div class="card-body">
                                    <table id="div_table" class="table table-sm">
                                        <thead><tr><th width="50">#</th><th>Division</th><th class="text-end">Total</th></tr></thead>
                                        <tbody>
                                            <?php $i = 1; while ($row = $div_count->fetch_assoc()): ?>
                                                <tr><td><?php echo $i++; ?></td><td><?php echo htmlspecialchars($row['officeDivision']); ?></td><td class="text-end fw-bold"><?php echo $row['total']; ?></td></tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card border h-100">
                                <div class="card-header fw-bold bg-light">Acquisition by Year</div>
                                <div class="card-body">
                                    <table id="year_table" class="table table-sm">
                                        <thead><tr><th width="50">#</th><th>Year</th><th class="text-end">Quantity</th></tr></thead>
                                        <tbody>
                                            <?php $i = 1; while ($row = $year_count->fetch_assoc()): ?>
                                                <tr><td><?php echo $i++; ?></td><td><?php echo htmlspecialchars($row['yearAcquired']); ?></td><td class="text-end fw-bold"><?php echo $row['total']; ?></td></tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade p-4" id="detailed" role="tabpanel">
                    <div class="table-responsive">
                        <table id="full_table" class="table table-hover w-100">
                            <thead><tr><th width="50">#</th><th>Actual User</th><th>Division</th><th>Equipment</th><th>Acquired</th><th>Properties</th><th>Remarks</th></tr></thead>
                            <tbody>
                                <?php $i = 1; while ($row = $full_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td> 
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['actualUser']); ?></span><br><small class="text-muted">Acc: <?php echo htmlspecialchars($row['accountablePerson']); ?></small></td>
                                    <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($row['officeDivision']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($row['equipmentType']); ?></strong><br><small><?php echo htmlspecialchars($row['brand']); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                                    <td><small>SN: <?php echo htmlspecialchars($row['serialNumber']); ?></small><br><small>PN: <?php echo htmlspecialchars($row['propertyNumber']); ?></small></td>
                                    <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> </div> </div> </div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        
        // --- PRINT HEADER CONFIGURATION ---
        // This HTML is injected into the print window only
        var printHeader = '<div style="text-align: center; margin-bottom: 20px;">' +
                          '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Department_of_Environment_and_Natural_Resources_%28DENR%29_Logo.png/240px-Department_of_Environment_and_Natural_Resources_%28DENR%29_Logo.png" style="height: 70px; width: auto; margin-bottom: 10px;">' +
                          '<h5 style="margin:0; font-weight: normal;">Republic of the Philippines</h5>' +
                          '<h4 style="margin:0; font-weight: bold;">Department of Environment and Natural Resources</h4>' +
                          '<h5 style="margin:0; font-weight: normal;">Regional Office No. XIII (Caraga)</h5>' +
                          '<h3 style="margin-top: 20px; text-decoration: underline;">ICT EQUIPMENT INVENTORY REPORT</h3>' +
                          '</div>';

        const commonSettings = {
            language: { search: "_INPUT_", searchPlaceholder: "Search records..." },
            // 'B' adds the Buttons (Print) to the layout
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer-fill me-2"></i>Print Table',
                    className: 'btn btn-primary btn-sm',
                    title: '', // Remove default title
                    autoPrint: true,
                    customize: function (win) {
                        $(win.document.body).prepend(printHeader); // Add DENR Header
                        $(win.document.body).find('h1').remove(); // Remove any auto-generated titles
                        $(win.document.body).css('font-size', '10pt');
                        $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
                    },
                    exportOptions: {
                        columns: ':visible' // Only print visible columns
                    }
                }
            ]
        };

        // 1. Age Table
        $('#age_table').DataTable({ ...commonSettings, pageLength: 15, order: [[4, 'desc']] });

        // 2. Personnel Table
        $('#personnel_table').DataTable({ ...commonSettings, pageLength: 15 });

        // 3. GENERATOR / REPORT TABLE
        var generatorTable = $('#generator_table').DataTable({
            ...commonSettings,
            pageLength: 25,
            order: [[1, 'asc'], [2, 'asc']]
        });

        // Filter Logic
        $('#divisionFilter').on('change', function() { generatorTable.column(1).search(this.value).draw(); });
        $('#employeeFilter').on('change', function() { generatorTable.column(2).search(this.value).draw(); });
        window.resetFilters = function() {
            $('#divisionFilter').val('');
            $('#employeeFilter').val('');
            generatorTable.search('').columns().search('').draw();
        };

        // 4. Counts Tables (Simple Print)
        $('#div_table').DataTable({ dom: 'Bft', paging: false, buttons: [{ extend: 'print', title: 'Equipment by Division', customize: function(win){ $(win.document.body).prepend(printHeader); } }] });
        $('#year_table').DataTable({ dom: 'Bft', paging: false, buttons: [{ extend: 'print', title: 'Acquisition by Year', customize: function(win){ $(win.document.body).prepend(printHeader); } }] });

        // 5. Full Table
        $('#full_table').DataTable({ ...commonSettings, pageLength: 25, order: [[2, 'asc'], [1, 'asc']] });
    });
</script>
</body>
</html>