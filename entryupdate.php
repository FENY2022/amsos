<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Equipment Inventory</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --body-bg: #f4f7f6;
            --card-bg: #ffffff;
            --font-family: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--body-bg);
            color: var(--dark-color);
        }

        .container {
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .page-header {
            margin-bottom: 2rem;
            color: var(--dark-color);
            text-align: center;
        }
        
        .page-header i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .search-form .form-label {
            font-weight: 500;
        }

        .search-form .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
        }

        .table-responsive {
            margin-top: 1rem;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        
        .table th, .table td {
            vertical-align: middle;
            padding: 1rem;
        }

        .table thead th {
            background-color: var(--light-color);
            border: none;
            color: var(--dark-color);
            font-weight: 600;
        }

        .table tbody tr {
            background-color: var(--card-bg);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5em 0.75em;
        }

        .collapse-btn {
            background: none;
            border: 1px solid #ccc;
            color: var(--secondary-color);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .collapse-btn:hover {
             background-color: var(--primary-color);
             color: white;
             border-color: var(--primary-color);
        }
        
        .collapse-row td {
            background-color: #fcfcfc !important;
            padding: 1.5rem;
            border-top: 2px solid var(--primary-color);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .details-grid strong {
            color: var(--primary-color);
        }

        .pagination .page-link {
            border-radius: 0.25rem;
            margin: 0 3px;
            border: none;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.4);
        }
        
        #record-count-label {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #e9ecef;
            border-radius: 0.5rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
    </style>

</head>
<body>

<?php
    require_once 'connect.php'; // Ensure this path is correct
    // For testing purposes, I'm defining a session variable. Remove this in your live environment.
    if (!isset($_SESSION['OfficeSRF'])) {
        $_SESSION['OfficeSRF'] = 'some_default_office'; // Replace with actual session handling if not set
    }

    $office_division_options = "";
    $previousDivision = "";

    // Fetch distinct office divisions from the database for the current office session
    $sql = "SELECT DISTINCT officeDivision FROM inv_inventory WHERE Office = ? AND officeDivision IS NOT NULL AND officeDivision != '' ORDER BY officeDivision ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['OfficeSRF']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $currentDivision = strtoupper($row['officeDivision']);
            $office_division_options .= "<option value='" . htmlspecialchars($currentDivision) . "'>" . htmlspecialchars($currentDivision) . "</option>";
        }
    } else {
        $office_division_options .= "<option value=''>No divisions found</option>";
    }
?>

<div class="container">
    <h2 class="page-header"><i class="fas fa-edit"></i>Update Inventory Entries</h2>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-search"></i> Search Filters
        </div>
        <div class="card-body">

            <form action="mainmenu.php?dir=edupdate" method="POST" class="search-form" style="margin-bottom: 1rem;">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="officeDivision" class="form-label">Office Division:</label>
                        <select id="officeDivision" name="officeDivision" class="form-select" required style="padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;">
                            <option value="">-- Select Office --</option>
                            <?php echo $office_division_options; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="employeeName" class="form-label">Employee Name:</label>
                        <select id="employeeName" name="employeeName" class="form-select" style="padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;">
                            <option value="">-- Select Employee --</option>
                            </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="statusFilter" class="form-label">Status:</label>
                        <select id="statusFilter" name="statusFilter" class="form-select" style="padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;">
                            <option value="">All</option>
                            <option value="1">Done</option>
                            <option value="0">Not Done</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="sortBy" class="form-label">Sort By:</label>
                        <select id="sortBy" name="sortBy" class="form-select" style="padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem;">
                            <option value="id_desc">Newest First</option>
                            <option value="name_asc">Employee Name (A-Z)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <label for="search" class="form-label">&nbsp;</label>
                        <button type="submit" id="search" name="search" class="btn btn-primary" style="padding: 0.375rem 0.75rem; font-weight: 500;">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col text-end">
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                <i class="fas fa-plus-circle me-2"></i>Add New Record
            </button>
<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#comprehensiveReportsModal">
    <i class="fas fa-chart-pie me-2"></i>Comprehensive Reports
</button>

        </div>
    </div>

    <!-- Comprehensive Reports Modal -->
    <div class="modal fade" id="comprehensiveReportsModal" tabindex="-1" aria-labelledby="comprehensiveReportsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-lg-down" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="comprehensiveReportsLabel">
                        <i class="fas fa-file-chart-line me-2"></i>Comprehensive Reports
                    </h5>
                    <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="loading-spinner position-absolute top-50 start-50 translate-middle d-none">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <iframe
                        src="comprehensive_reports.php"
                        style="width: 100%; height: 85vh; border: none;"
                        class="smooth-transition"
                        onload="this.previousElementSibling.classList.add('d-none')"
                        title="Comprehensive Reports"
                    ></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    

    <div class="card">
        <div class="card-body">
            
            <?php
            if (isset($_POST['search']) || isset($_GET['page'])) {
                require_once 'connect.php';

                // PAGINATION SETUP
                $records_per_page = 10;
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $records_per_page;
                
                // GET SEARCH PARAMETERS
                $officeDivision = isset($_REQUEST['officeDivision']) ? $_REQUEST['officeDivision'] : '';
                $employeeName = isset($_REQUEST['employeeName']) ? $_REQUEST['employeeName'] : '';
                $statusFilter = isset($_REQUEST['statusFilter']) ? $_REQUEST['statusFilter'] : '';
                $sortBy = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'id_desc'; // Get Sort By param

                // BUILD THE COUNT QUERY
                $count_query = "SELECT COUNT(*) as total FROM inv_inventory WHERE Office = ?";
                $params = [$_SESSION['OfficeSRF']];
                $types = "s";

                if (!empty($officeDivision)) {
                    $count_query .= " AND officeDivision = ?";
                    $params[] = $officeDivision;
                    $types .= "s";
                }
                if (!empty($employeeName)) {
                    $count_query .= " AND employeeName LIKE ?";
                    $params[] = '%' . $employeeName . '%';
                    $types .= "s";
                }
                if ($statusFilter !== '') { 
                    $count_query .= " AND mark_as_done = ?";
                    $params[] = $statusFilter;
                    $types .= "i";
                }

                // GET TOTAL RECORDS
                $stmt_count = mysqli_prepare($conn, $count_query);
                if ($stmt_count) {
                    mysqli_stmt_bind_param($stmt_count, $types, ...$params);
                    mysqli_stmt_execute($stmt_count);
                    $result_count = mysqli_stmt_get_result($stmt_count);
                    $total_records = mysqli_fetch_assoc($result_count)['total'];
                    $total_pages = ceil($total_records / $records_per_page);
                    mysqli_stmt_close($stmt_count);
                } else {
                    $total_records = 0;
                    $total_pages = 0;
                    error_log("Error preparing count statement: " . mysqli_error($conn));
                }

                echo '<div id="record-count-label"><i class="fas fa-list-ol"></i> Found <strong>'. $total_records . '</strong> Records</div>';

                // BUILD THE DATA QUERY
                $query = "SELECT * FROM inv_inventory WHERE Office = ?";
                $params_data = [$_SESSION['OfficeSRF']];
                $types_data = "s";

                if (!empty($officeDivision)) {
                    $query .= " AND officeDivision = ?";
                    $params_data[] = $officeDivision;
                    $types_data .= "s";
                }
                if (!empty($employeeName)) {
                    $query .= " AND employeeName LIKE ?";
                    $params_data[] = '%' . $employeeName . '%';
                    $types_data .= "s";
                }
                if ($statusFilter !== '') {
                    $query .= " AND mark_as_done = ?";
                    $params_data[] = $statusFilter;
                    $types_data .= "i";
                }
                
                // APPLY SORTING LOGIC
                if ($sortBy == 'name_asc') {
                    $query .= " ORDER BY employeeName ASC, id DESC"; // Sort by Name A-Z, then Newest ID
                } else {
                    $query .= " ORDER BY id DESC"; // Default: Newest ID first
                }

                // LIMIT CLAUSE
                $query .= " LIMIT ?, ?";
                $params_data[] = $offset;
                $params_data[] = $records_per_page;
                $types_data .= "ii";

                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, $types_data, ...$params_data);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                } else {
                    $result = null;
                    error_log("Error preparing data statement: " . mysqli_error($conn));
                }
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Equipment</th>
                            <th>Year</th>
                            <th>Brand</th>
                            <th>Office</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td data-label='ID'><strong><?php echo htmlspecialchars($row['id']); ?></strong></td>
                            <td data-label='Employee Name'><?php echo htmlspecialchars($row['employeeName']); ?></td>
                            <td data-label='Equipment'><?php echo htmlspecialchars($row['equipmentType']); ?></td>
                            <td data-label='Year'><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                            <td data-label='Brand'><?php echo htmlspecialchars($row['brand']); ?></td>
                            <td data-label='Office'><?php echo htmlspecialchars($row['officeDivision']); ?></td>
                            <td data-label='Work Status'>
                                <?php echo ($row['mark_as_done'] == 0 ? '<span class="badge bg-danger">Not Done</span>' : '<span class="badge bg-success">Done</span>'); ?>
                            </td>
                            <td data-label='Action'>
                                <button class='collapse-btn' onclick='toggleRow(this)'><i class="fas fa-plus"></i></button>
                            </td>
                        </tr>
                        <tr class='collapse-row' style='display: none;'>
                            <td colspan='8'>
                                <div class="details-grid">
                                    <div><strong>Specifications:</strong><br><?php echo htmlspecialchars($row['specifications']); ?></div>
                                    <div><strong>Serial Number:</strong><br><?php echo htmlspecialchars($row['serialNumber']); ?></div>
                                    <div><strong>Property Number:</strong><br><?php echo htmlspecialchars($row['propertyNumber']); ?></div>
                                    <div><strong>Actual User:</strong><br><?php echo htmlspecialchars($row['actualUser']); ?></div>
                                    <div><strong>User Status:</strong><br><?php echo htmlspecialchars($row['actualUserStatusOfEmployment']); ?></div>
                                    <div><strong>Nature of Work:</strong><br><?php echo htmlspecialchars($row['natureOfWork']); ?></div>
                                    <div><strong>Accountable Person:</strong><br><?php echo htmlspecialchars($row['accountablePerson']); ?></div>
                                </div>
                                <hr>
                                <div><strong>Remarks:</strong><br><?php echo nl2br(htmlspecialchars($row['remarks'])); ?></div>
                                <hr>
                                <strong>Actions:</strong><br>
                                <div class='mt-2'>
                                    <button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#editInventory<?php echo $row['id']; ?>'><i class="fas fa-edit"></i> Edit</button>
                                    <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#qrModal<?php echo $row['id']; ?>'><i class="fas fa-qrcode"></i> Generate QR</button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="qrModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="qrModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-secondary text-white">
                                        <h5 class="modal-title" id="qrModalLabel<?php echo $row['id']; ?>">
                                            <i class="fas fa-qrcode me-2"></i>QR Code (ID: <?php echo $row['id']; ?>)
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="loading-spinner position-absolute top-50 start-50 translate-middle d-none">
                                            <div class="spinner-border text-secondary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <iframe 
                                            src="qr.php?id=<?php echo $row['id']; ?>" 
                                            style="width: 100%; height: 400px; border: none;"
                                            class="smooth-transition"
                                            onload="this.previousElementSibling.classList.add('d-none')"
                                            title="QR Code Generator"
                                        ></iframe>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i>Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="editInventory<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $row['id']; ?>">
                                            <i class="fas fa-edit me-2"></i>Edit Inventory (ID: <?php echo $row['id']; ?>)
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <div class="loading-spinner position-absolute top-50 start-50 translate-middle d-none">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <iframe 
                                            src="editEnventory.php?id=<?php echo $row['id']; ?>" 
                                            style="width: 100%; height: 80vh; border: none;"
                                            class="smooth-transition"
                                            onload="this.previousElementSibling.classList.add('d-none')"
                                            title="Edit Inventory Form"
                                        ></iframe>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times me-1"></i>Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php
                    if ($total_pages > 1) {
                        $query_params = http_build_query([
                            'dir' => 'edupdate',
                            'officeDivision' => $officeDivision,
                            'employeeName' => $employeeName,
                            'statusFilter' => $statusFilter,
                            'sortBy' => $sortBy // Add sortBy to pagination params
                        ]);

                        // Previous button
                        if ($page > 1) {
                            echo "<li class='page-item'><a class='page-link' href='mainmenu.php?page=".($page - 1)."&{$query_params}'>&laquo;</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><a class='page-link' href='#'>&laquo;</a></li>";
                        }

                        // Page number links
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active = ($i == $page) ? 'active' : '';
                            echo "<li class='page-item {$active}'><a class='page-link' href='mainmenu.php?page={$i}&{$query_params}'>{$i}</a></li>";
                        }

                        // Next button
                        if ($page < $total_pages) {
                            echo "<li class='page-item'><a class='page-link' href='mainmenu.php?page=".($page + 1)."&{$query_params}'>&raquo;</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><a class='page-link' href='#'>&raquo;</a></li>";
                        }
                    }
                    ?>
                </ul>
            </nav>

            <?php
            } else {
                echo "<div class='alert alert-info text-center'><i class='fas fa-info-circle'></i> Please select an office division and click 'Search' to view records.</div>";
            }
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="addRecordModal" tabindex="-1" aria-labelledby="addRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addRecordModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add New Inventory Record
                </h5>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-0">
                <div class="loading-spinner position-absolute top-50 start-50 translate-middle d-none">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <iframe
                    src="mainmenu.php?dir=entrydatahidesidebar" style="width: 100%; height: 85vh; border: none;"
                    class="smooth-transition"
                    onload="this.previousElementSibling.classList.add('d-none')"
                    title="Add New Inventory Form"
                ></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>


<script>
// JavaScript for persisting form fields and dynamic dropdowns
(function($) { // Pass jQuery as $ to ensure it's available in this scope
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        var i;
        for (i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Function to load employee names based on selected office division
    function loadEmployeeNames(selectedDivision) {
        const employeeNameSelect = document.getElementById("employeeName");
        // Clear existing options and show a loading message
        employeeNameSelect.innerHTML = '<option value="">-- Loading Employees --</option>'; 
        employeeNameSelect.disabled = true; // Disable until loaded

        if (selectedDivision) {
            $.ajax({
                url: 'get_employees.php', // Path to the new PHP file
                type: 'GET',
                data: { officeDivision: selectedDivision },
                dataType: 'json',
                success: function(data) {
                    employeeNameSelect.innerHTML = '<option value="">-- Select Employee --</option>'; // Default option
                    if (data.length > 0) {
                        data.forEach(function(employee) {
                            const option = document.createElement('option');
                            option.value = employee;
                            option.textContent = employee;
                            employeeNameSelect.appendChild(option);
                        });
                        // Restore previously selected employee if it exists and is in the new list
                        const savedEmployeeName = getCookie("employeeName") || localStorage.getItem("employeeName");
                        if (savedEmployeeName && data.includes(savedEmployeeName)) {
                            employeeNameSelect.value = savedEmployeeName;
                        } else {
                            // If the previously selected employee is not in the new list, clear the saved value
                            setCookie("employeeName", "", -1); // Expire cookie
                            localStorage.removeItem("employeeName");
                        }
                    } else {
                        employeeNameSelect.innerHTML = '<option value="">No employees found for this division</option>';
                    }
                    employeeNameSelect.disabled = false; // Enable dropdown
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error loading employees:", status, error, xhr.responseText);
                    employeeNameSelect.innerHTML = '<option value="">Error loading employees</option>';
                    employeeNameSelect.disabled = false; // Re-enable dropdown even on error
                }
            });
        } else {
            employeeNameSelect.innerHTML = '<option value="">-- Select Office first --</option>';
            employeeNameSelect.disabled = true; // Keep disabled until an office is selected
            setCookie("employeeName", "", -1); // Clear employee cookie if no division is selected
            localStorage.removeItem("employeeName");
        }
    }

    window.addEventListener('load', function() {
        const officeDivisionSelect = document.getElementById("officeDivision");
        const employeeNameSelect = document.getElementById("employeeName");
        const statusFilterSelect = document.getElementById("statusFilter");
        const sortBySelect = document.getElementById("sortBy"); // Get sort element

        // Restore saved office division
        const savedOfficeDivision = getCookie("officeDivision") || localStorage.getItem("officeDivision");
        if (savedOfficeDivision) {
            officeDivisionSelect.value = savedOfficeDivision;
            loadEmployeeNames(savedOfficeDivision); // Load employees for the saved division
        } else {
            employeeNameSelect.innerHTML = '<option value="">-- Select Office first --</option>';
            employeeNameSelect.disabled = true; // Disable initially if no office is selected
        }

        // Restore saved status filter
        const savedStatusFilter = getCookie("statusFilter") || localStorage.getItem("statusFilter");
        if (savedStatusFilter !== null) { 
            statusFilterSelect.value = savedStatusFilter;
        }

        // Restore saved sort order
        const savedSortBy = getCookie("sortBy") || localStorage.getItem("sortBy");
        if (savedSortBy) {
            sortBySelect.value = savedSortBy;
        }
    });

    // Event listener for Office Division change
    document.getElementById("officeDivision").addEventListener("change", function() {
        const selectedDivision = this.value;
        setCookie("officeDivision", selectedDivision, 30);
        localStorage.setItem("officeDivision", selectedDivision);
        loadEmployeeNames(selectedDivision); // Load employees when division changes
    });

    // Event listener for Employee Name change
    document.getElementById("employeeName").addEventListener("change", function() {
        setCookie("employeeName", this.value, 30);
        localStorage.setItem("employeeName", this.value);
    });

    // Event listener for Status Filter change
    document.getElementById("statusFilter").addEventListener("change", function() {
        setCookie("statusFilter", this.value, 30);
        localStorage.setItem("statusFilter", this.value);
    });
    
    // Event listener for Sort By change
    document.getElementById("sortBy").addEventListener("change", function() {
        setCookie("sortBy", this.value, 30);
        localStorage.setItem("sortBy", this.value);
    });

})(jQuery); // Pass jQuery to the IIFE

// JavaScript for toggling details row
function toggleRow(button) {
    const icon = button.querySelector('i');
    const collapseRow = button.closest("tr").nextElementSibling;
    
    if (collapseRow.style.display === "none") {
        collapseRow.style.display = "table-row";
        icon.classList.remove('fa-plus');
        icon.classList.add('fa-minus');
    } else {
        collapseRow.style.display = "none";
        icon.classList.remove('fa-minus');
        icon.classList.add('fa-plus');
    }
}
</script>

</body>
</html>