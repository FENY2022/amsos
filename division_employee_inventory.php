<?php
// Include your existing database connection file
include 'connect.php';

// Initialize variables
$selectedDivision = isset($_GET['division']) ? $_GET['division'] : '';
$inventoryData = [];
$divisions = [];

// 1. Fetch Distinct Divisions for the dropdown filter
// We use distinct to get a clean list of all available divisions in the database
$divSql = "SELECT DISTINCT Division FROM hardware_tbl WHERE Division != '' ORDER BY Division ASC";
$divResult = $conn->query($divSql);

if ($divResult->num_rows > 0) {
    while ($row = $divResult->fetch_assoc()) {
        $divisions[] = $row['Division'];
    }
}

// 2. Fetch and Sort Equipment Data if a division is selected
if ($selectedDivision) {
    // QUERY LOGIC: 
    // - Filter by the selected Division
    // - ORDER BY Employee_Name ASC (Primary Sort): Groups items by employee
    // - ORDER BY Type ASC (Secondary Sort): Organizes items (Laptop, Printer) within the employee's list
    $sql = "SELECT * FROM hardware_tbl 
            WHERE Division = ? 
            ORDER BY Employee_Name ASC, Type ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDivision);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $inventoryData[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Division ICT Inventory Per Employee</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .container { margin-top: 30px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header-title { color: #0d6efd; font-weight: 700; }
        
        /* Employee Separator Styles */
        .employee-group-header {
            background-color: #e9ecef;
            border-left: 5px solid #0d6efd;
            font-weight: 700;
            color: #212529;
            padding: 10px 15px;
            margin-top: 20px;
        }
        
        .table thead th { background-color: #343a40; color: white; border: none; }
        .badge-status { font-size: 0.85em; }
        
        @media print {
            .no-print { display: none !important; }
            .container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .employee-group-header { background-color: #eee !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="header-title"><i class="fa-solid fa-users-gear"></i> Division Equipment Report</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary no-print">
            <i class="fa-solid fa-print"></i> Print Report
        </button>
    </div>

    <div class="card mb-4 no-print">
        <div class="card-body bg-light">
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="division" class="col-form-label fw-bold">Select Division:</label>
                </div>
                <div class="col-md-4">
                    <select name="division" id="division" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Choose Division --</option>
                        <?php foreach ($divisions as $div): ?>
                            <option value="<?php echo htmlspecialchars($div); ?>" <?php if($selectedDivision == $div) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($div); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selectedDivision && !empty($inventoryData)): ?>
        <div class="mb-3 text-center">
            <h4>Inventory for Division: <span class="text-primary text-decoration-underline"><?php echo htmlspecialchars($selectedDivision); ?></span></h4>
            <p class="text-muted small">Generated on: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Type</th>
                        <th style="width: 15%;">Brand/Model</th>
                        <th style="width: 30%;">Specifications</th>
                        <th style="width: 15%;">Property No.</th>
                        <th style="width: 10%;">Acquired</th>
                        <th style="width: 10%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $currentEmployee = null;
                    $itemCount = 1;

                    foreach ($inventoryData as $item): 
                        // LOGIC: Check if the employee name has changed since the last row.
                        // If it has, print a new header row for that employee.
                        if ($currentEmployee !== $item['Employee_Name']): 
                            $currentEmployee = $item['Employee_Name'];
                            $itemCount = 1; // Reset counter for new employee
                    ?>
                        <tr class="table-light">
                            <td colspan="7" class="p-0">
                                <div class="employee-group-header">
                                    <i class="fa-solid fa-user-tie"></i>&nbsp; 
                                    <?php echo !empty($currentEmployee) ? htmlspecialchars($currentEmployee) : "Unassigned / No Name"; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td class="text-center"><?php echo $itemCount++; ?></td>
                        <td><?php echo htmlspecialchars($item['Type']); ?></td>
                        <td><?php echo htmlspecialchars($item['Brand']); ?></td>
                        <td class="small"><?php echo htmlspecialchars($item['Specs']); ?></td>
                        <td><?php echo htmlspecialchars($item['Property_No']); ?></td>
                        <td><?php echo htmlspecialchars($item['Acquired_Year']); ?></td>
                        <td class="text-center">
                            <?php 
                                $statusClass = 'bg-secondary';
                                if($item['Status'] === 'Operational') $statusClass = 'bg-success';
                                elseif($item['Status'] === 'Defective') $statusClass = 'bg-danger';
                                elseif($item['Status'] === 'For Repair') $statusClass = 'bg-warning text-dark';
                            ?>
                            <span class="badge <?php echo $statusClass; ?> badge-status">
                                <?php echo htmlspecialchars($item['Status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
    <?php elseif ($selectedDivision): ?>
        <div class="alert alert-warning text-center mt-4">
            <i class="fa-solid fa-triangle-exclamation"></i> No equipment records found for <strong><?php echo htmlspecialchars($selectedDivision); ?></strong>.
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center mt-5">
            <i class="fa-solid fa-arrow-up"></i> Please select a Division above to generate the report.
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>