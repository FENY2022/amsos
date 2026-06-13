<?php
// connect.php (Assuming this file contains your database connection logic)
// Example:
// $servername = "localhost";
// $username = "your_username";
// $password = "your_password";
// $dbname = "your_database";
//
// $conn = new mysqli($servername, $username, $password, $dbname);
//
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

require_once 'connect.php'; // Database connection

// Fetch dropdown options dynamically
function getDropdownOptions($column) {
    global $conn;
    $options = [];
    // Ensure the column name is properly escaped to prevent SQL injection issues
    $query = "SELECT DISTINCT `" . $conn->real_escape_string($column) . "` FROM `inv_inventory` WHERE `" . $conn->real_escape_string($column) . "` IS NOT NULL AND `" . $conn->real_escape_string($column) . "` != '' ORDER BY `" . $conn->real_escape_string($column) . "` ASC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row[$column];
        }
        $result->free(); // Free the result set
    } else {
        // Log the error or handle it appropriately
        error_log("Error fetching dropdown options for column '$column': " . $conn->error);
    }
    return $options;
}

// Build the filtering query
$filter_query = "SELECT * FROM `inv_inventory` WHERE 1"; // Start with WHERE 1 to easily append conditions

// Prepare an array to store filter parameters for cleaner query building and display
$filter_params = [];

if (!empty($_GET['office'])) {
    $office = $conn->real_escape_string($_GET['office']);
    $filter_query .= " AND `office` = '$office'";
    $filter_params['office'] = $_GET['office'];
}
if (!empty($_GET['officeDivision'])) {
    $officeDivision = $conn->real_escape_string($_GET['officeDivision']);
    $filter_query .= " AND `officeDivision` = '$officeDivision'";
    $filter_params['officeDivision'] = $_GET['officeDivision'];
}
if (!empty($_GET['employeeName'])) {
    $employeeName = $conn->real_escape_string($_GET['employeeName']);
    $filter_query .= " AND `employeeName` = '$employeeName'";
    $filter_params['employeeName'] = $_GET['employeeName'];
}
if (!empty($_GET['yearAcquired'])) {
    $yearAcquired = $conn->real_escape_string($_GET['yearAcquired']);
    $filter_query .= " AND `yearAcquired` = '$yearAcquired'";
    $filter_params['yearAcquired'] = $_GET['yearAcquired'];
}
if (!empty($_GET['equipmentType'])) {
    $equipmentType = $conn->real_escape_string($_GET['equipmentType']);
    $filter_query .= " AND `equipmentType` = '$equipmentType'";
    $filter_params['equipmentType'] = $_GET['equipmentType'];
}
if (!empty($_GET['brand'])) {
    $brand = $conn->real_escape_string($_GET['brand']);
    $filter_query .= " AND `brand` = '$brand'";
    $filter_params['brand'] = $_GET['brand'];
}
if (!empty($_GET['specifications'])) {
    $specifications = $conn->real_escape_string($_GET['specifications']);
    $filter_query .= " AND `specifications` LIKE '%$specifications%'";
    $filter_params['specifications'] = $_GET['specifications'];
}

// Execute query
$results = $conn->query($filter_query);

// Check for query errors
if (!$results) {
    error_log("Error executing filter query: " . $conn->error);
    // Optionally, display a user-friendly error message
    $error_message = "An error occurred while fetching data. Please try again later.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Inventory Filter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
            --danger: #e63946;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .filter-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 12px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
            transition: all 0.3s;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--accent));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4);
        }
        
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .result-count {
            font-weight: 600;
            color: var(--primary);
            background-color: rgba(67, 97, 238, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .table-container {
            max-height: 500px;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table thead th {
            background-color: var(--primary);
            color: white;
            vertical-align: middle;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 0.9rem;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.08);
        }
        
        .badge {
            padding: 0.6em 1em;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .bg-success {
            background: linear-gradient(to right, #4cc9f0, #4895ef) !important;
        }
        
        .bg-danger {
            background: linear-gradient(to right, #e63946, #f72585) !important;
        }
        
        .bg-warning {
            background: linear-gradient(to right, #ff9e00, #ff6d00) !important;
        }
        
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-tag {
            background: linear-gradient(to right, var(--accent), var(--primary));
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-tag .close {
            color: white;
            opacity: 0.8;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .filter-tag .close:hover {
            opacity: 1;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .no-results i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .footer {
            margin-top: 3rem;
            padding: 1.5rem 0;
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-filter me-2"></i> Equipment Inventory Filter</h2>
                <div class="d-flex align-items-center">
                    
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-sliders-h me-2"></i> Filter Options
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filterForm">
                            <input type="hidden" name="dir" value="analysisandgraph_datafilter">
                            
                            <div class="row g-3">
                                <!-- Office Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Office</label>
                                    <select name="office" class="form-select">
                                        <option value="">Select Office</option>
                                        <?php foreach (getDropdownOptions('office') as $office): ?>
                                            <option value="<?= htmlspecialchars($office) ?>" 
                                                <?= (isset($_GET['office']) && $_GET['office'] === $office) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($office) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Office Division Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Office Division</label>
                                    <select name="officeDivision" class="form-select">
                                        <option value="">Select Division</option>
                                        <?php foreach (getDropdownOptions('officeDivision') as $division): ?>
                                            <option value="<?= htmlspecialchars($division) ?>" <?= (isset($_GET['officeDivision']) && $_GET['officeDivision'] === $division) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($division) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Employee Name Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Employee Name</label>
                                    <select name="employeeName" class="form-select">
                                        <option value="">Select Employee</option>
                                        <?php foreach (getDropdownOptions('employeeName') as $employee): ?>
                                            <option value="<?= htmlspecialchars($employee) ?>" <?= (isset($_GET['employeeName']) && $_GET['employeeName'] === $employee) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($employee) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Year Acquired Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Year Acquired</label>
                                    <select name="yearAcquired" class="form-select">
                                        <option value="">Select Year</option>
                                        <?php foreach (getDropdownOptions('yearAcquired') as $year): ?>
                                            <option value="<?= htmlspecialchars($year) ?>" <?= (isset($_GET['yearAcquired']) && $_GET['yearAcquired'] === $year) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($year) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Equipment Type Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Equipment Type</label>
                                    <select name="equipmentType" class="form-select">
                                        <option value="">Select Type</option>
                                        <?php foreach (getDropdownOptions('equipmentType') as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>" <?= (isset($_GET['equipmentType']) && $_GET['equipmentType'] === $type) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Brand Dropdown -->
                                <div class="col-md-4">
                                    <label class="form-label">Brand</label>
                                    <select name="brand" class="form-select">
                                        <option value="">Select Brand</option>
                                        <?php foreach (getDropdownOptions('brand') as $brand): ?>
                                            <option value="<?= htmlspecialchars($brand) ?>" <?= (isset($_GET['brand']) && $_GET['brand'] === $brand) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($brand) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Specifications Textbox -->
                                <div class="col-md-8">
                                    <label class="form-label">Specifications (e.g., HDD, SSD, etc.)</label>
                                    <input type="text" name="specifications" class="form-control" 
                                        value="<?= isset($_GET['specifications']) ? htmlspecialchars($_GET['specifications']) : '' ?>" 
                                        placeholder="Enter specifications keywords">
                                </div>
                                        
                                <!-- Action Buttons -->
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="d-grid gap-2 w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter me-2"></i> Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="fas fa-sync-alt me-2"></i> Reset Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Filter Tags -->
                <?php if (!empty($filter_params)): ?>
                    <div class="filter-tags">
                        <span class="fw-bold">Active Filters:</span>
                        <?php foreach ($filter_params as $key => $value): ?>
                            <div class="filter-tag">
                                <?= htmlspecialchars(ucfirst($key)) ?>: <?= htmlspecialchars($value) ?>
                                <span class="close" onclick="removeFilter('<?= $key ?>')">&times;</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Results Section -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-table me-2"></i> Filtered Results
                    </div>
                    <div class="card-body">
                        <div class="results-header">
                            <h3>Equipment Inventory</h3>
                            <div class="result-count">
                                <i class="fas fa-chart-bar me-2"></i>
                                <?php if (isset($results) && $results->num_rows > 0): ?>
                                    <?= $results->num_rows ?> records found
                                <?php else: ?>
                                    No records found
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (isset($results) && $results->num_rows > 0): ?>
                            <div class="table-container">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Employee</th>
                                            <th>Equipment Type</th>
                                            <th>Year</th>
                                            <th>Brand</th>
                                            <th>Specs</th>
                                            <th>Office</th>
                                            <th>Health Status</th>
                                            <th>User</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $results->fetch_assoc()): 
                                            $currentYear = date('Y');
                                            $yearsInUse = $currentYear - (int)$row['yearAcquired'];
                                            $healthStatus = '';
                                            
                                            if ($yearsInUse > 5) {
                                                $healthStatus = 'Poor';
                                            } else {
                                                $healthStatus = 'Good Condition';
                                            }
                                            
                                            // Adjust based on specifications
                                            if (stripos($row['specifications'], 'HDD') !== false && $yearsInUse > 3) {
                                                $healthStatus = 'Attention Required';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['employeeName']) ?></td>
                                                <td><?= htmlspecialchars($row['equipmentType']) ?></td>
                                                <td><?= htmlspecialchars($row['yearAcquired']) ?></td>
                                                <td><?= htmlspecialchars($row['brand']) ?></td>
                                                <td><?= htmlspecialchars($row['specifications']) ?></td>
                                                <td><?= htmlspecialchars($row['office']) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $healthStatus === 'Good Condition' ? 'bg-success' : 
                                                            ($healthStatus === 'Poor' ? 'bg-danger' : 'bg-warning') ?>">
                                                        <?= $healthStatus ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($row['actualUser']) ?></td>
                                                <td>P<?= number_format(htmlspecialchars($row['amount']), 2) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-results">
                                <i class="fas fa-inbox"></i>
                                <h4>No equipment records found</h4>
                                <p class="text-muted">Try adjusting your filters to see more results</p>
                            </div>
                        <?php endif; ?>

                        <?php 
                        // Close the database connection and free results if they exist
                        if (isset($results) && $results instanceof mysqli_result) {
                            $results->free();
                        }
                        if (isset($conn) && $conn instanceof mysqli) {
                            $conn->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Equipment Inventory System &copy; <?= date('Y') ?> | Powered by PHP & MySQL</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reset form function
        function resetForm() {
            // Clear all select elements
            document.querySelectorAll('#filterForm select').forEach(select => {
                select.value = '';
            });
            // Clear the text input
            document.querySelector('#filterForm input[name="specifications"]').value = '';
            
            // Submit the form to clear filters from URL
            const urlParams = new URLSearchParams();
            urlParams.set('dir', 'analysisandgraph_datafilter'); // Preserve the 'dir' parameter
            window.location.search = urlParams.toString();
        }
        
        // Remove filter function
        function removeFilter(filterName) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete(filterName);
            window.location.search = urlParams.toString();
        }
        
        // Enhance form controls with icons (This part is purely cosmetic and can be removed if not desired)
        document.querySelectorAll('.form-select').forEach(select => {
            // Check if the select already has the wrapper to prevent duplication on multiple calls
            if (!select.closest('.input-group')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                
                const icon = document.createElement('span');
                icon.className = 'input-group-text bg-light border-0';
                icon.innerHTML = '<i class="fas fa-chevron-down"></i>'; // Default dropdown icon
                
                // For specific select boxes, you might want different icons
                if (select.name === 'specifications') {
                    icon.innerHTML = '<i class="fas fa-search"></i>';
                } else if (select.name === 'office') {
                    icon.innerHTML = '<i class="fas fa-building"></i>';
                } else if (select.name === 'employeeName') {
                    icon.innerHTML = '<i class="fas fa-user"></i>';
                } else if (select.name === 'yearAcquired') {
                    icon.innerHTML = '<i class="fas fa-calendar-alt"></i>';
                } else if (select.name === 'equipmentType') {
                    icon.innerHTML = '<i class="fas fa-laptop"></i>';
                } else if (select.name === 'brand') {
                    icon.innerHTML = '<i class="fas fa-tag"></i>';
                }

                // Insert the wrapper before the select, then append select and icon to wrapper
                select.parentNode.insertBefore(wrapper, select);
                wrapper.appendChild(select);
                wrapper.appendChild(icon);
            }
        });

        // Add icon for specifications input
        document.querySelectorAll('input[name="specifications"]').forEach(input => {
            // Check if the input already has the wrapper
            if (!input.closest('.input-group')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                
                const icon = document.createElement('span');
                icon.className = 'input-group-text bg-light border-0';
                icon.innerHTML = '<i class="fas fa-search"></i>';
                
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                wrapper.appendChild(icon);
            }
        });
    </script>
</body>
</html>
