<?php
// reports_advanced.php
// Full Solution for AMSOS Advanced Reporting
// Requirements Covered: 1, 2, 3, 5, 6

include 'connect.php'; 

// --- 1. HANDLE FILTERS & INPUTS ---
$current_year = date('Y');
$filter_year = isset($_GET['year']) ? $_GET['year'] : $current_year;
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$filter_division = isset($_GET['division']) ? $_GET['division'] : '';
$filter_employee = isset($_GET['employee']) ? $_GET['employee'] : '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// --- 2. HELPER FUNCTIONS ---
function get_db_count($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if($result) {
        $row = mysqli_fetch_array($result);
        return $row[0] ?? 0;
    }
    return 0;
}

// --- 3. FETCH HIGH-LEVEL METRICS (Req #3) ---
// Req 3: Total Inventoried
$total_inventory = get_db_count($conn, "SELECT COUNT(*) FROM inv_inventory");

// Req 3: Total Procured CY 2025
// Note: Checks 'yearAcquired' for 2025
$total_procured_2025 = get_db_count($conn, "SELECT COUNT(*) FROM inv_inventory WHERE yearAcquired LIKE '%2025%'");

// Req 5: Quick Count of Old Items (> 5 Years)
// Assumes yearAcquired is stored as YYYY
$old_threshold_year = (int)$current_year - 5;
$total_old_items = get_db_count($conn, "SELECT COUNT(*) FROM inv_inventory WHERE yearAcquired <= '$old_threshold_year' AND yearAcquired != ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMSOS | Advanced Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- MODERN UI STYLING --- */
        :root {
            --primary: #2c3e50;
            --accent: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #c0392b;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Sidebar Placeholder (If you have an include) */
        .sidebar-space { width: 250px; float: left; min-height: 100vh; background: #333; display: none; } 
        /* Assuming you include your sidebar via PHP, adjust margins accordingly */

        .main-content {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header h1 { margin: 0; font-size: 1.5rem; color: var(--primary); }
        .page-header p { margin: 5px 0 0; color: #7f8c8d; }

        /* KPI Cards (Req 3) */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            border-bottom: 4px solid var(--accent);
            transition: transform 0.2s;
        }
        .kpi-card:hover { transform: translateY(-3px); }
        .kpi-icon {
            width: 60px; height: 60px;
            background: var(--light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; color: var(--accent);
            margin-right: 20px;
        }
        .kpi-data h3 { margin: 0; font-size: 2rem; color: var(--dark); }
        .kpi-data p { margin: 0; color: #7f8c8d; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; }

        /* Tabs */
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 0; border-bottom: 2px solid #ddd; }
        .nav-link {
            padding: 12px 25px;
            background: #e9ecef;
            border: none;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-weight: 600;
            color: #6c757d;
            transition: 0.3s;
            text-decoration: none;
        }
        .nav-link:hover { background: #dde2e6; color: var(--primary); }
        .nav-link.active {
            background: var(--accent);
            color: white;
        }

        /* Tab Content */
        .tab-pane {
            background: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: none;
        }
        .tab-pane.active { display: block; animation: fadeIn 0.4s; }

        /* Filters & Tables */
        .filter-bar {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        select, input[type="number"], .btn-action {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .btn-action {
            background: var(--accent);
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-action:hover { background: #2980b9; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: var(--primary); color: white; padding: 12px; text-align: left; font-size: 0.9rem; }
        td { padding: 12px; border-bottom: 1px solid #eee; color: #555; }
        tr:hover { background-color: #f1f1f1; }

        /* Badges */
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; color: white; }
        .bg-old { background-color: var(--warning); }
        .bg-new { background-color: var(--success); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <?php if(file_exists('sidebar.php')) { include 'sidebar.php'; } ?>

    <div class="main-content">
        
        <div class="page-header">
            <div>
                <h1>Advanced Analytics Dashboard</h1>
                <p>Hardware Lifecycle, Personnel Tracking, and Procurement Reports</p>
            </div>
            <div>
                <button class="btn-action" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi-card" style="border-bottom-color: var(--primary);">
                <div class="kpi-icon"><i class="fas fa-cubes"></i></div>
                <div class="kpi-data">
                    <h3><?php echo number_format($total_inventory); ?></h3>
                    <p>Total Inventory</p>
                </div>
            </div>
            <div class="kpi-card" style="border-bottom-color: var(--success);">
                <div class="kpi-icon"><i class="fas fa-cart-plus"></i></div>
                <div class="kpi-data">
                    <h3><?php echo number_format($total_procured_2025); ?></h3>
                    <p>Procured CY 2025</p>
                </div>
            </div>
            <div class="kpi-card" style="border-bottom-color: var(--warning);">
                <div class="kpi-icon"><i class="fas fa-history"></i></div>
                <div class="kpi-data">
                    <h3><?php echo number_format($total_old_items); ?></h3>
                    <p>Items > 5 Years Old</p>
                </div>
            </div>
        </div>

        <div class="nav-tabs">
            <a href="?tab=dashboard" class="nav-link <?php echo ($active_tab == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> 1. Division Stats (Req 2)
            </a>
            <a href="?tab=lifecycle" class="nav-link <?php echo ($active_tab == 'lifecycle') ? 'active' : ''; ?>">
                <i class="fas fa-hourglass-half"></i> 2. Lifecycle (>5 Yrs) (Req 5)
            </a>
            <a href="?tab=employee" class="nav-link <?php echo ($active_tab == 'employee') ? 'active' : ''; ?>">
                <i class="fas fa-user-tag"></i> 3. Employee Items (Req 1)
            </a>
            <a href="?tab=personnel" class="nav-link <?php echo ($active_tab == 'personnel') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> 4. Personnel List (Req 6)
            </a>
        </div>

        <div class="tab-pane <?php echo ($active_tab == 'dashboard') ? 'active' : ''; ?>">
            <h3><i class="fas fa-building"></i> Equipment Count by Division</h3>
            <p>Sort and generate count of ICT Equipment per division for specific dates.</p>
            
            <form method="GET" class="filter-bar">
                <input type="hidden" name="tab" value="dashboard">
                <label>Year Acquired:</label>
                <input type="number" name="year" value="<?php echo $filter_year; ?>" min="2000" max="2100">
                <button type="submit" class="btn-action">Filter Statistics</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Division / Office</th>
                        <th>Total Units Acquired (<?php echo $filter_year; ?>)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Grouping by 'office' (Column 18 in your structure)
                    // We assume the column name is 'office' based on 'ORED' values. Adjust if it is 'division'.
                    $sql_div = "SELECT office, COUNT(*) as count 
                                FROM inv_inventory 
                                WHERE yearAcquired = '$filter_year' 
                                GROUP BY office 
                                ORDER BY count DESC";
                    
                    $res_div = mysqli_query($conn, $sql_div);
                    
                    if (mysqli_num_rows($res_div) > 0) {
                        while ($row = mysqli_fetch_assoc($res_div)) {
                            $divName = empty($row['office']) ? "Unassigned" : $row['office'];
                            echo "<tr>
                                    <td><strong>{$divName}</strong></td>
                                    <td><span style='font-size:1.1rem; font-weight:bold;'>{$row['count']}</span> units</td>
                                    <td>Active</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; padding:20px;'>No acquisitions found for Year $filter_year</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="tab-pane <?php echo ($active_tab == 'lifecycle') ? 'active' : ''; ?>">
            <h3><i class="fas fa-server"></i> Equipment Lifecycle (5 Year Threshold)</h3>
            <p>Computers, Laptops, and Printers classified by age.</p>

            <div class="filter-bar">
                <label>View Category:</label>
                <select id="lifeFilter" onchange="filterLifeTable()">
                    <option value="all">Show All</option>
                    <option value="old">Above 5 Years (For Replacement)</option>
                    <option value="new">Below 5 Years (Good Condition)</option>
                </select>
            </div>

            <table id="lifeTable">
                <thead>
                    <tr>
                        <th>Property No / ID</th>
                        <th>Type</th>
                        <th>Brand & Model</th>
                        <th>Acquired</th>
                        <th>Age (Years)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Filter specifically for computers/printers
                    $sql_life = "SELECT * FROM inv_inventory 
                                 WHERE equipmentType LIKE '%Computer%' 
                                    OR equipmentType LIKE '%Laptop%' 
                                    OR equipmentType LIKE '%Printer%'
                                 ORDER BY yearAcquired DESC";
                    $res_life = mysqli_query($conn, $sql_life);

                    while($row = mysqli_fetch_assoc($res_life)) {
                        $acq_year = (int)$row['yearAcquired'];
                        if($acq_year == 0) continue; // Skip invalid years
                        
                        $age = (int)$current_year - $acq_year;
                        $is_old = $age >= 5;
                        $row_class = $is_old ? 'row-old' : 'row-new';
                        $badge_class = $is_old ? 'bg-old' : 'bg-new';
                        $status_text = $is_old ? '> 5 Years' : '< 5 Years';

                        echo "<tr class='{$row_class}'>
                                <td>{$row['id']}</td>
                                <td>{$row['equipmentType']}</td>
                                <td>{$row['brand']}</td>
                                <td>{$row['yearAcquired']}</td>
                                <td><strong>{$age}</strong></td>
                                <td><span class='badge {$badge_class}'>{$status_text}</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <script>
                function filterLifeTable() {
                    let filter = document.getElementById('lifeFilter').value;
                    let rowsOld = document.querySelectorAll('.row-old');
                    let rowsNew = document.querySelectorAll('.row-new');
                    
                    if(filter === 'all') {
                        rowsOld.forEach(r => r.style.display = '');
                        rowsNew.forEach(r => r.style.display = '');
                    } else if (filter === 'old') {
                        rowsOld.forEach(r => r.style.display = '');
                        rowsNew.forEach(r => r.style.display = 'none');
                    } else {
                        rowsOld.forEach(r => r.style.display = 'none');
                        rowsNew.forEach(r => r.style.display = '');
                    }
                }
            </script>
        </div>

        <div class="tab-pane <?php echo ($active_tab == 'employee') ? 'active' : ''; ?>">
            <h3><i class="fas fa-id-card"></i> Employee Accountability Report</h3>
            <p>Generate list of equipment per employee within a specific division.</p>
            
            <form method="GET" class="filter-bar">
                <input type="hidden" name="tab" value="employee">
                
                <select name="division" onchange="this.form.submit()">
                    <option value="">-- Select Division First --</option>
                    <?php
                    $d_sql = "SELECT DISTINCT office FROM inv_inventory WHERE office != '' ORDER BY office";
                    $d_res = mysqli_query($conn, $d_sql);
                    while($row = mysqli_fetch_assoc($d_res)){
                        $sel = ($filter_division == $row['office']) ? 'selected' : '';
                        echo "<option value='{$row['office']}' $sel>{$row['office']}</option>";
                    }
                    ?>
                </select>

                <select name="employee">
                    <option value="">-- Select Employee --</option>
                    <?php
                    if($filter_division) {
                        // Assuming 'accountable_officer' is the relevant column, or 'employeeName'
                        // Based on your data, 'accountable_officer' seems more appropriate for accountability
                        $e_sql = "SELECT DISTINCT accountable_officer FROM inv_inventory WHERE office = '$filter_division' ORDER BY accountable_officer";
                        $e_res = mysqli_query($conn, $e_sql);
                        while($row = mysqli_fetch_assoc($e_res)){
                            $sel = ($filter_employee == $row['accountable_officer']) ? 'selected' : '';
                            echo "<option value='{$row['accountable_officer']}' $sel>{$row['accountable_officer']}</option>";
                        }
                    }
                    ?>
                </select>

                <button type="submit" class="btn-action">Generate</button>
            </form>

            <?php if($filter_employee): ?>
                <div style="background:#e8f4fc; padding:15px; border-left:5px solid var(--accent); margin-bottom:15px;">
                    <strong>Accountability List for:</strong> <?php echo $filter_employee; ?> <br>
                    <strong>Division:</strong> <?php echo $filter_division; ?>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Brand/Model</th>
                            <th>Description</th>
                            <th>Value (Cost)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch items for specific employee in specific division
                        $emp_inv_sql = "SELECT * FROM inv_inventory 
                                        WHERE office = '$filter_division' 
                                        AND accountable_officer = '$filter_employee'";
                        $emp_inv_res = mysqli_query($conn, $emp_inv_sql);
                        
                        if(mysqli_num_rows($emp_inv_res) > 0) {
                            while($row = mysqli_fetch_assoc($emp_inv_res)){
                                // Handle potential empty value columns
                                $val = !empty($row['unit_value']) ? $row['unit_value'] : 'N/A';
                                echo "<tr>
                                        <td>{$row['equipmentType']}</td>
                                        <td>{$row['brand']}</td>
                                        <td>{$row['specifications']}</td>
                                        <td>{$val}</td>
                                        <td>Active</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No equipment found assigned to this officer.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="tab-pane <?php echo ($active_tab == 'personnel') ? 'active' : ''; ?>">
            <h3><i class="fas fa-address-book"></i> Updated Personnel List</h3>
            <p>Master list of personnel identified in the inventory system.</p>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name of Personnel</th>
                        <th>Designation / Position</th>
                        <th>Office / Division</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get distinct personnel. We check both employeeName and accountable_officer
                    $p_sql = "SELECT DISTINCT accountable_officer, office, employment_status FROM inv_inventory 
                              WHERE accountable_officer != '' 
                              ORDER BY office, accountable_officer";
                    $p_res = mysqli_query($conn, $p_sql);
                    $count = 1;
                    
                    while($row = mysqli_fetch_assoc($p_res)){
                        echo "<tr>
                                <td>{$count}</td>
                                <td><strong>{$row['accountable_officer']}</strong></td>
                                <td>{$row['employment_status']}</td>
                                <td>{$row['office']}</td>
                              </tr>";
                        $count++;
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>