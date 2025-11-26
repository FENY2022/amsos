<?php
// Include the database connection
require_once 'connect.php';

// Get selected office division from the URL parameter
$selectedOfficeDivision = isset($_GET['officeDivision']) ? $_GET['officeDivision'] : 'All';
$selectedStorageType = isset($_GET['storageType']) ? $_GET['storageType'] : 'All';
$selectedLicensingModel = isset($_GET['licensingModel']) ? $_GET['licensingModel'] : 'All';


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

// Function to analyze specifications and suggest replacement
function analyzeSpecifications($specs, $equipmentType) {
    $issues = [];

    if ($equipmentType == 'Printers') {
        if (preg_match('/\b(inkjet)\b/i', $specs)) {
            $issues[] = "Older inkjet technology which is less efficient compared to modern laser printers.";
        }
        if (preg_match('/\b(slow printing|low resolution)\b/i', $specs)) {
            $issues[] = "Poor print quality or speed, which can affect productivity.";
        }
        if (preg_match('/\b(manual duplex)\b/i', $specs)) {
            $issues[] = "Manual duplex printing, which is less convenient and time-consuming.";
        }
    } else {
        if (preg_match('/Core i[357]-[0-6]/i', $specs)) {
            $issues[] = "Older CPU generation, which may not support modern applications efficiently.";
        }
        if (preg_match('/\b(4GB|4 GB)\b/i', $specs)) {
            $issues[] = "Low RAM, which can cause performance issues with modern software.";
        }
        if (preg_match('/\b(500GB|500 GB|5400RPM)\b/i', $specs)) {
            $issues[] = "Limited storage or slow HDD, which can affect the overall performance and speed.";
        }
        if (strpos($specs, 'Windows 7') !== false) {
            $issues[] = "Outdated OS, which is no longer supported and poses security risks.";
        }
    }

    return empty($issues) ? "" : implode(", ", $issues);
}


// Function to generate dynamic replacement reasons
// Function to generate AI-like dynamic replacement reasons
function generateReplacementReason($remarks, $equipmentType) {
    // Define different sentence patterns
    $patterns = [
        "The {equipmentType} shows signs of {issues}. Upgrading this equipment will {benefit}.",
        "Given the {issues} in the current {equipmentType}, it's essential to replace it to {benefit}.",
        "Due to {issues}, this {equipmentType} is no longer meeting performance standards. Replacing it will {benefit}.",
        "The current {equipmentType} suffers from {issues}, which {impact}. A replacement is necessary to {benefit}.",
        "With the {issues} affecting this {equipmentType}, an upgrade is recommended to {benefit}."
    ];

    // Possible benefits of replacement
    $benefits = [
        "improve efficiency and productivity",
        "ensure smoother operations",
        "reduce downtime and frustration",
        "enhance security and performance",
        "support modern applications and workflows"
    ];

    // Possible impacts of issues
    $impacts = [
        "affects daily tasks",
        "hinders performance",
        "slows down work processes",
        "compromises productivity",
        "limits operational capacity"
    ];

    // Replace placeholders with actual data
    $selectedPattern = $patterns[array_rand($patterns)];
    $selectedBenefit = $benefits[array_rand($benefits)];
    $selectedImpact = $impacts[array_rand($impacts)];

    // Replace placeholders with dynamic content
    $reason = str_replace(
        ['{equipmentType}', '{issues}', '{benefit}', '{impact}'],
        [strtolower($equipmentType), $remarks, $selectedBenefit, $selectedImpact],
        $selectedPattern
    );

    return ucfirst($reason);
}


// Function to generate summary and reasons for replacement
function generateSummary($result) {
    $summary = [];
    while ($row = $result->fetch_assoc()) {
        $shelfLife = determineShelfLife($row['yearAcquired']);
        if ($shelfLife === "Beyond 5 Years") {
            $remarks = analyzeSpecifications($row['specifications'], $row['equipmentType']);
            $summary[] = [
                'employeeName' => $row['employeeName'],
                'equipmentType' => $row['equipmentType'],
                'yearAcquired' => $row['yearAcquired'],
                'shelfLife' => $shelfLife,
                'brand' => $row['brand'],
                'specifications' => $row['specifications'],
                'rangeCategory' => $row['rangeCategory'],
                'office' => $row['office'],
                'accountablePerson' => $row['accountablePerson'],
                'actualUser' => $row['actualUser'],
                'remarks' => $remarks
            ];
        }
    }
    return $summary;
}

$summary = generateSummary($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary of Equipment for Replacement</title>
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
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        li strong {
            color: #333;
        }
        li span {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Summary of Equipment for Replacement</h2>

        <?php if (!empty($summary)): ?>
            <ul>
                <?php foreach ($summary as $item): ?>
                    <li>
                        <strong>Employee Name:</strong> <span><?php echo htmlspecialchars($item['employeeName']); ?></span><br>
                        <strong>Equipment Type:</strong> <span><?php echo htmlspecialchars($item['equipmentType']); ?></span><br>
                        <strong>Year Acquired:</strong> <span><?php echo htmlspecialchars($item['yearAcquired']); ?></span><br>
                        <strong>Shelf Life:</strong> <span><?php echo htmlspecialchars($item['shelfLife']); ?></span><br>
                        <strong>Brand:</strong> <span><?php echo htmlspecialchars($item['brand']); ?></span><br>
                        <strong>Specifications:</strong> <span><?php echo htmlspecialchars($item['specifications']); ?></span><br>
                        <strong>Range Category:</strong> <span><?php echo htmlspecialchars($item['rangeCategory']); ?></span><br>
                        <strong>Office:</strong> <span><?php echo htmlspecialchars($item['office']); ?></span><br>
                        <strong>Accountable Person:</strong> <span><?php echo htmlspecialchars($item['accountablePerson']); ?></span><br>
                        <strong>Actual User:</strong> <span><?php echo htmlspecialchars($item['actualUser']); ?></span><br>
                        <strong>Remarks:</strong> <span><?php echo htmlspecialchars($item['remarks']); ?></span><br>
                        <strong>Reason for Replacement:</strong> 
                        <span><?php echo htmlspecialchars(generateReplacementReason($item['remarks'], $item['equipmentType'])); ?></span><br>


                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No equipment found for replacement.</p>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
