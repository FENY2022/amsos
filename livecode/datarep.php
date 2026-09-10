<?php
// Include the database connection
require_once 'connect.php';

// Fetch distinct office divisions for the dropdown
$officeDivisionsResult = $conn->query("SELECT DISTINCT officeDivision FROM inv_inventory");
$officeDivisions = [];
while ($row = $officeDivisionsResult->fetch_assoc()) {
    $officeDivisions[] = $row['officeDivision'];
}

// Get selected office division from the dropdown
$selectedOfficeDivision = isset($_GET['officeDivision']) ? $_GET['officeDivision'] : 'All';

// Get selected storage type from the dropdown
$selectedStorageType = isset($_GET['storageType']) ? $_GET['storageType'] : 'All';

// Get selected licensing model from the dropdown
$selectedLicensingModel = isset($_GET['licensingModel']) ? $_GET['licensingModel'] : 'All';

// Query to select records where equipmentType is "Desktop Computers", "Laptop Computers", or "Printers" and filter by officeDivision, storageType, and licensingModel if selected
$sql = "SELECT * FROM inv_inventory WHERE equipmentType IN ('Desktop Computers', 'Laptop Computers', 'Printers')";
if ($selectedOfficeDivision !== 'All') {
    $sql .= " AND officeDivision = '" . $conn->real_escape_string($selectedOfficeDivision) . "'";
}
if ($selectedStorageType !== 'All') {
    $sql .= " AND specifications LIKE '%" . $conn->real_escape_string($selectedStorageType) . "%'";
}
if ($selectedLicensingModel !== 'All') {
    $sql .= " AND licensingModel_2 = '" . $conn->real_escape_string($selectedLicensingModel) . "'";
}
$sql .= " ORDER BY yearAcquired ASC";
$result = $conn->query($sql);

// Function to determine if the equipment is beyond 5 years
function determineShelfLife($yearAcquired) {
    $currentYear = date("Y");
    return ($currentYear - (int)$yearAcquired) > 5 ? "Beyond 5 Years" : "Within 5 Years";
}

// Function to get color for shelf life (Updated for BS5)
function getShelfLifeColor($shelfLife) {
    return $shelfLife === "Beyond 5 Years" ? '<span class="badge bg-danger rounded-pill px-3 py-2">' . $shelfLife . '</span>' : '<span class="badge bg-success rounded-pill px-3 py-2">' . $shelfLife . '</span>';
}

// Function to analyze specifications and suggest replacement
function analyzeSpecifications($specs, $equipmentType) {
    $issues = [];

    if ($equipmentType == 'Printers') {
        if (preg_match('/\b(inkjet)\b/i', $specs)) {
            $issues[] = "Older inkjet technology";
        }
        if (preg_match('/\b(slow printing|low resolution)\b/i', $specs)) {
            $issues[] = "Poor print quality or speed";
        }
        if (preg_match('/\b(manual duplex)\b/i', $specs)) {
            $issues[] = "Manual duplex printing";
        }
    } else {
        if (preg_match('/Core i[357]-[0-6]/i', $specs)) {
            $issues[] = "Older CPU generation";
        }
        if (preg_match('/\b(4GB|4 GB)\b/i', $specs)) {
            $issues[] = "Low RAM";
        }
        if (preg_match('/\b(500GB|500 GB|5400RPM)\b/i', $specs)) {
            $issues[] = "Limited storage or slow HDD";
        }
        if (strpos($specs, 'Windows 7') !== false) {
            $issues[] = "Outdated OS";
        }
    }

    return empty($issues) ? "" : implode(", ", $issues);
}

// Function to get color for remarks (Updated for BS5)
function getRemarksColor($remarks) {
    return empty($remarks) ? '<span class="badge bg-success rounded-pill px-3">No issues found</span>' : '<span class="badge bg-danger rounded-pill px-3 text-wrap text-start" style="line-height: 1.5;">' . htmlspecialchars($remarks) . '</span>';
}

// Function to apply color to range category (Updated for BS5)
function getRangeCategoryColor($rangeCategory) {
    switch ($rangeCategory) {
        case 'ENTRY / BASIC LEVEL':
            return '<span class="badge bg-success rounded-pill px-3">' . htmlspecialchars($rangeCategory) . '</span>';
        case 'MID LEVEL':
            return '<span class="badge bg-warning text-dark rounded-pill px-3">' . htmlspecialchars($rangeCategory) . '</span>';
        case 'HIGH END':
            return '<span class="badge bg-danger rounded-pill px-3">' . htmlspecialchars($rangeCategory) . '</span>';
        default:
            return '<span class="badge bg-secondary rounded-pill px-3">' . htmlspecialchars($rangeCategory) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Replacement List</title>
    
    <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #333;
        }
        .page-title {
            font-weight: 700;
            color: #2c3e50;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        /* Card Styling */
        .custom-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }
        /* Table Styling */
        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }
        .table tbody tr:hover {
            background-color: #f8fdff;
            transition: 0.2s;
        }
        .table td, .table th {
            vertical-align: middle;
            padding: 12px 10px;
        }
        
        .truncate-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
        
        /* Child row styles */
        .details-row td {
            padding: 0 !important;
            border-bottom: none;
        }
        .details-inner {
            background-color: #fafbfc;
            border-bottom: 2px solid #dee2e6;
            padding: 20px 25px;
            box-shadow: inset 0 3px 6px -3px rgba(0,0,0,0.1);
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        /* Grid layout for expanded details */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .detail-item strong {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .detail-item span {
            font-weight: 500;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4 px-lg-5">
        
        <h3 class="page-title mb-4">
            <i class="fas fa-desktop text-primary me-2"></i> ICT Equipment Replacement Priority
        </h3>

        <div class="custom-card p-4">
            <form method="GET" action="mainmenu.php">
                <input type="hidden" name="dir" value="datarep">
                
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="prioritySelect" class="form-label"><i class="fas fa-sort-amount-up-alt me-1 text-secondary"></i> Prioritization:</label>
                        <select id="prioritySelect" class="form-select">
                            <option value="1">1st Priority</option>
                            <option value="2">2nd Priority</option>
                            <option value="3">3rd Priority</option>
                            <option value="4">4th Priority</option>
                            <option value="5">5th Priority</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="officeDivision" class="form-label"><i class="fas fa-building me-1 text-secondary"></i> Office Division:</label>
                        <select id="officeDivision" name="officeDivision" class="form-select">
                            <option value="All">All Divisions</option>
                            <?php foreach ($officeDivisions as $division): ?>
                                <option value="<?php echo htmlspecialchars($division); ?>" <?php echo $selectedOfficeDivision == $division ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($division); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="storageType" class="form-label"><i class="fas fa-hdd me-1 text-secondary"></i> Storage Type:</label>
                        <select id="storageType" name="storageType" class="form-select">
                            <option value="All">All Storage</option>
                            <option value="HDD" <?php echo $selectedStorageType == 'HDD' ? 'selected' : ''; ?>>HDD</option>
                            <option value="SSD" <?php echo $selectedStorageType == 'SSD' ? 'selected' : ''; ?>>SSD</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="licensingModel" class="form-label"><i class="fas fa-key me-1 text-secondary"></i> Licensing Model:</label>
                        <select id="licensingModel" name="licensingModel" class="form-select">
                            <option value="All">All Models</option>
                            <option value="PERPETUAL" <?php echo $selectedLicensingModel == 'PERPETUAL' ? 'selected' : ''; ?>>PERPETUAL</option>
                            <option value="EVALUATION COPY" <?php echo $selectedLicensingModel == 'EVALUATION COPY' ? 'selected' : ''; ?>>EVALUATION COPY</option>
                            <option value="GENUINE" <?php echo $selectedLicensingModel == 'GENUINE' ? 'selected' : ''; ?>>GENUINE</option>
                        </select>
                    </div>
                </div>

                <div class="action-bar mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle px-4" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a href="summary.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">
                                    <i class="fas fa-file-alt text-secondary me-2"></i> Generate Summary
                                </a>
                            </li>
                            <li>
                                <a href="replacement_report.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">
                                    <i class="fas fa-file-export text-secondary me-2"></i> Generate Report
                                </a>
                            </li>
                            <li>
                                <a href="depreciation_report.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">
                                    <i class="fas fa-chart-line text-secondary me-2"></i> Depreciation Report
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>

        <div class="custom-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top">
                <h5 class="mb-0 text-secondary"><i class="fas fa-list me-2"></i> Equipment List</h5>
                <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                    Total Records: <?php echo $result->num_rows; // Will update correctly later ?>
                </span>
            </div>
            
            <div class="table-responsive" style="max-height: 65vh;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;"></th>
                            <th class="text-center">#</th>
                            <th>Employee Name</th>
                            <th>Equipment Type</th>
                            <th class="text-center">Year</th>
                            <th class="text-center">Shelf Life</th>
                            <th>Brand</th>
                            <th class="text-center">Range Category</th>
                            
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 0;
                        if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                    $shelfLife = determineShelfLife($row['yearAcquired']);
                                    if ($shelfLife !== "Beyond 5 Years") continue;
                                    $counter++;
                                    
                                    // Process issues/remarks
                                    $remarks = analyzeSpecifications($row['specifications'], $row['equipmentType']);
                                    $rangeCategoryColor = getRangeCategoryColor($row['rangeCategory']);
                                    $shelfLifeColor = getShelfLifeColor($shelfLife);
                                    $remarksColor = getRemarksColor($remarks);
                                ?>
                                
                                <tr>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $counter; ?>" aria-expanded="false" onclick="toggleIcon(this)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                    <td class="text-center fw-bold text-secondary"><?php echo $counter; ?></td>
                                    <td class="fw-medium text-nowrap"><?php echo htmlspecialchars($row['employeeName']); ?></td>
                                    <td class="text-nowrap"><?php echo htmlspecialchars($row['equipmentType']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                                    <td class="text-center"><?php echo $shelfLifeColor; ?></td>
                                    <td class="text-nowrap"><?php echo htmlspecialchars($row['brand']); ?></td>
                                    <td class="text-center"><?php echo $rangeCategoryColor; ?></td>
                                </tr>
                                
                                <tr id="details-<?php echo $counter; ?>" class="collapse details-row">
                                    <td colspan="8"> <div class="details-inner">
                                            
                                            <div class="details-grid mb-3">
                                                <div class="detail-item">
                                                    <strong><i class="fas fa-building me-1"></i> Office / Division</strong>
                                                    <span><?php echo htmlspecialchars($row['office']); ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <strong><i class="fas fa-user-tie me-1"></i> Accountable Person</strong>
                                                    <span><?php echo htmlspecialchars($row['accountablePerson']); ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <strong><i class="fas fa-user me-1"></i> Actual User</strong>
                                                    <span><?php echo htmlspecialchars($row['actualUser']); ?></span>
                                                </div>
                                            </div>

                                            <hr class="text-muted">

                                            <div class="row mt-3">
                                                <div class="col-md-7 mb-3 mb-md-0">
                                                    <h6 class="text-primary fw-bold"><i class="fas fa-microchip me-1"></i> Full Specifications</h6>
                                                    <p class="mb-0 text-secondary" style="white-space: pre-wrap; font-size: 0.95rem;"><?php echo htmlspecialchars($row['specifications']); ?></p>
                                                </div>
                                                <div class="col-md-5">
                                                    <h6 class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> Remarks / Issues Found</h6>
                                                    <div class="mt-2">
                                                        <?php echo $remarksColor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            
                            <?php if($counter == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 text-light"></i><br>
                                        No equipment found beyond 5 years of shelf life.
                                    </td>
                                </tr>
                            <?php endif; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-3x mb-3 text-light"></i><br>
                                    No records found matching your filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <script>
                // Update badge counter top
                document.addEventListener("DOMContentLoaded", function() {
                    const badge = document.querySelector('.bg-primary.rounded-pill');
                    if(badge) {
                        badge.innerHTML = "Total Records: <?php echo $counter; ?>";
                    }
                });

                // Function para mu-change ang Icon (Plus to Minus)
                function toggleIcon(btn) {
                    const icon = btn.querySelector('i');
                    if(icon.classList.contains('fa-plus')) {
                        icon.classList.remove('fa-plus');
                        icon.classList.add('fa-minus');
                        btn.classList.replace('btn-outline-secondary', 'btn-primary'); // mo-blue color inig open
                    } else {
                        icon.classList.remove('fa-minus');
                        icon.classList.add('fa-plus');
                        btn.classList.replace('btn-primary', 'btn-outline-secondary'); // balik gray inig close
                    }
                }
            </script>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>