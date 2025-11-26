<?php
// Include the database connection
require_once 'connect.php';

// Get selected filters from the URL parameters
$selectedOfficeDivision = isset($_GET['officeDivision']) ? $_GET['officeDivision'] : 'All';
$selectedStorageType = isset($_GET['storageType']) ? $_GET['storageType'] : 'All';
$selectedLicensingModel = isset($_GET['licensingModel']) ? $_GET['licensingModel'] : 'All';

// Function to determine if the equipment is beyond 5 years
function determineShelfLife($yearAcquired) {
    $currentYear = date("Y");
    return ($currentYear - (int)$yearAcquired) > 5 ? "Beyond 5 Years" : "Within 5 Years";
}

// SQL query to fetch relevant data filtered by Beyond 5 Years shelf life
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

// Initialize counters
$totalItems = 0;
$beyond5YearsCount = 0;

// Data collection for display
$equipmentList = [];

// Process the query results and filter by Beyond 5 Years
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shelfLife = determineShelfLife($row['yearAcquired']);
        if ($shelfLife === "Beyond 5 Years") {
            $totalItems++;
            $beyond5YearsCount++;

            $equipmentList[] = [
                'employeeName' => $row['employeeName'],
                'yearAcquired' => $row['yearAcquired'],
                'accountablePerson' => $row['accountablePerson'],
                'actualUser' => $row['actualUser'],
                'brand' => $row['brand'],
                'shelfLife' => $shelfLife
            ];
        }
    }
}

// Handle depreciation form submission
$purchasePrice = isset($_POST['purchasePrice']) ? (float)$_POST['purchasePrice'] : 0;
$depreciation = 0;
$salvageValue = 0;

if ($purchasePrice > 0) {
    $salvageValue = $purchasePrice * 0.10; // 10% salvage value
    $usefulLife = 5; // 5 years useful life
    $depreciation = ($purchasePrice - $salvageValue) / $usefulLife;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Equipment Due for Replacement Report</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            color: #007bff;
        }
        .summary-box {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #e9ecef;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f1f1f1;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Overall Equipment Due for Replacement Report</h2>

        <div class="summary-box">
            <p><strong>Total Equipment Items Beyond 5 Years:</strong> <?php echo $beyond5YearsCount; ?></p>
        </div>

        <div class="form-section">
            <h4>Depreciation Calculator</h4>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="purchasePrice">Enter Purchase Price:</label>
                    <input type="number" step="0.01" class="form-control" id="purchasePrice" name="purchasePrice" placeholder="Enter purchase price" required>
                </div>
                <button type="submit" class="btn btn-primary">Calculate Depreciation</button>
            </form>

            <?php if ($purchasePrice > 0): ?>
            <div class="mt-3">
                <p><strong>Purchase Price:</strong> ₱<?php echo number_format($purchasePrice, 2); ?></p>
                <p><strong>Salvage Value (10%):</strong> ₱<?php echo number_format($salvageValue, 2); ?></p>
                <p><strong>Annual Depreciation:</strong> ₱<?php echo number_format($depreciation, 2); ?></p>
            </div>
        <?php endif; ?>

        </div>

        <h4>Equipment List</h4>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Year Acquired</th>
                    <th>Accountable Person</th>
                    <th>Actual User</th>
                    <th>Brand</th>
                    <th>Shelf Life</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipmentList as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($item['employeeName']); ?></td>
                        <td><?php echo htmlspecialchars($item['yearAcquired']); ?></td>
                        <td><?php echo !empty($item['accountablePerson']) ? htmlspecialchars($item['accountablePerson']) : '-'; ?></td>
                        <td><?php echo !empty($item['actualUser']) ? htmlspecialchars($item['actualUser']) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($item['brand']); ?></td>
                        <td><?php echo htmlspecialchars($item['shelfLife']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
