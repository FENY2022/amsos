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

// Function to get color for shelf life
function getShelfLifeColor($shelfLife) {
    return $shelfLife === "Beyond 5 Years" ? '<span class="badge badge-danger">' . $shelfLife . '</span>' : '<span class="badge badge-success">' . $shelfLife . '</span>';
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

// Function to get color for remarks
function getRemarksColor($remarks) {
    return empty($remarks) ? '<span class="badge badge-success">No issues found</span>' : '<span class="badge badge-danger">' . htmlspecialchars($remarks) . '</span>';
}

// Function to apply color to range category
function getRangeCategoryColor($rangeCategory) {
    switch ($rangeCategory) {
        case 'ENTRY / BASIC LEVEL':
            return '<span class="badge badge-success">' . htmlspecialchars($rangeCategory) . '</span>';
        case 'MID LEVEL':
            return '<span class="badge badge-warning">' . htmlspecialchars($rangeCategory) . '</span>';
        case 'HIGH END':
            return '<span class="badge badge-danger">' . htmlspecialchars($rangeCategory) . '</span>';
        default:
            return htmlspecialchars($rangeCategory);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Replacement List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom CSS to align the container to the left */
        .container {
            margin-left: 0;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Priority List of ICT Equiment for Replacement : Desktop Computers, Laptop Computers, and Printers</h2>

        <form method="GET" action="mainmenu.php" class="mb-4">
            <!-- Hidden input to retain the 'dir' parameter -->
            <input type="hidden" name="dir" value="datarep">

            <div class="form-row">
                <div class="col-md-3">
                    <label for="prioritySelect" class="form-label">Select Prioritization:</label>
                    <select id="prioritySelect" class="form-control">
                        <option value="1">1st Priority</option>
                        <option value="2">2nd Priority</option>
                        <option value="3">3rd Priority</option>
                        <option value="4">4th Priority</option>
                        <option value="5">5th Priority</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="officeDivision" class="form-label">Select Office Division:</label>
                    <select id="officeDivision" name="officeDivision" class="form-control">
                        <option value="All">All</option>
                        <?php foreach ($officeDivisions as $division): ?>
                            <option value="<?php echo htmlspecialchars($division); ?>" <?php echo $selectedOfficeDivision == $division ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($division); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="storageType" class="form-label">Select Storage Type:</label>
                    <select id="storageType" name="storageType" class="form-control">
                        <option value="All">All</option>
                        <option value="HDD" <?php echo $selectedStorageType == 'HDD' ? 'selected' : ''; ?>>HDD</option>
                        <option value="SSD" <?php echo $selectedStorageType == 'SSD' ? 'selected' : ''; ?>>SSD</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="licensingModel" class="form-label">Select Licensing Model:</label>
                    <select id="licensingModel" name="licensingModel" class="form-control">
                        <option value="All">All</option>
                        <option value="PERPETUAL" <?php echo $selectedLicensingModel == 'PERPETUAL' ? 'selected' : ''; ?>>PERPETUAL</option>
                        <option value="EVALUATION COPY" <?php echo $selectedLicensingModel == 'EVALUATION COPY' ? 'selected' : ''; ?>>EVALUATION COPY</option>
                        <option value="GENUINE" <?php echo $selectedLicensingModel == 'GENUINE' ? 'selected' : ''; ?>>GENUINE</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filter</button>
            <!-- <a href="summary.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>" class="btn btn-secondary mt-3">Generate Summary</a> -->
          
            <!-- <a href="summary.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="btn btn-secondary mt-3">Generate Summary</a> -->
            <!-- <a href="summary.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="btn btn-secondary mt-3">Generate Report</a> -->
        
            <!-- <a href="mainmenu.php?dir=summary&officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>" class="btn btn-secondary mt-3">Generate Summary</a> -->
          
        


        </form>



        <div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        Action
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li>
                <a href="summary.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">Generate Summary</a>
            </li>
            <li>
                <a href="replacement_report.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">Generate Report</a>
            </li>
            <li>
                <a href="depreciation_report.php?officeDivision=<?php echo urlencode($selectedOfficeDivision); ?>&storageType=<?php echo urlencode($selectedStorageType); ?>&licensingModel=<?php echo urlencode($selectedLicensingModel); ?>" class="dropdown-item">Depreciation Report</a>
            </li>

        </ul>
    </div><br>



        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Equipment Type</th>
                    <th>Year Acquired</th>
                    <th>Shelf Life</th>
                    <th>Brand</th>
                    <th>Specifications</th>
                    <th>Range Category</th>
                    <th>Office</th>
                    <th>Accountable Person</th>
                    <th>Actual User</th>
                    <th>Remarks</th>
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
                            $remarks = analyzeSpecifications($row['specifications'], $row['equipmentType']);
                            $rangeCategoryColor = getRangeCategoryColor($row['rangeCategory']);
                            $shelfLifeColor = getShelfLifeColor($shelfLife);
                            $remarksColor = getRemarksColor($remarks);
                        ?>
                        <tr>
                            <td><?php echo $counter; ?></td>
                            <td><?php echo htmlspecialchars($row['employeeName']); ?></td>
                            <td><?php echo htmlspecialchars($row['equipmentType']); ?></td>
                            <td><?php echo htmlspecialchars($row['yearAcquired']); ?></td>
                            <td><?php echo $shelfLifeColor; ?></td>
                            <td><?php echo htmlspecialchars($row['brand']); ?></td>
                            <td><?php echo htmlspecialchars($row['specifications']); ?></td>
                            <td><?php echo $rangeCategoryColor; ?></td>
                            <td><?php echo htmlspecialchars($row['office']); ?></td>
                            <td><?php echo htmlspecialchars($row['accountablePerson']); ?></td>
                            <td><?php echo htmlspecialchars($row['actualUser']); ?></td>
                            <td><?php echo $remarksColor; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="text-center">No records found for replacement</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p>Total Records: <?php echo $counter; ?></p>
    </div>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
