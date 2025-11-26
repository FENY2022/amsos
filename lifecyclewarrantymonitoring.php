<?php
require_once 'connect.php';

// Query to fetch asset details
$office = $_SESSION['OfficeSRF'];
$sql = "SELECT employeeName, equipmentType, yearAcquired, shelfLife 
        FROM inv_inventory 
        WHERE office = '$office'";
$result = $conn->query($sql);

$assets = [];

// Fetch rows into an array for easier processing
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchase_year = intval($row["yearAcquired"]);
        $shelf_life = intval($row["shelfLife"]);
        $replacement_year = $purchase_year + $shelf_life;

        $assets[] = [
            "name" => $row["equipmentType"],
            "purchaseYear" => $purchase_year,
            "replacementYear" => $replacement_year,
            "employeeName" => $row["employeeName"],
            "status" => ($replacement_year <= date("Y")) ? "End of Life" : "Replacement Alert"
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphical Asset Lifecycle Timeline</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .timeline-container {
            width: 90%;
            margin: 0 auto;
            position: relative;
        }
        .year-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
            color: #555;
        }
        .asset-row {
            display: flex;
            align-items: center;
            margin-bottom: 60px; /* Increased spacing */
        }
        .asset-label {
            width: 50%;
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
        }
        .asset-timeline {
            width: 75%;
            position: relative;
            border-bottom: 2px solid #ccc;
        }
        .timeline-bar {
            position: absolute;
            height: 15px; /* Adjusted bar height */
            border-radius: 3px;
        }
        .status-replacement-alert {
            background-color: orange;
        }
        .status-end-of-life {
            background-color: red;
        }
        .milestone {
            position: absolute;
            height: 8px; /* Adjusted marker size */
            width: 8px;
            background-color: green;
            border-radius: 50%;
            top: 3px; /* Centered vertically */
        }
        .milestone.end {
            background-color: red;
        }
        .milestone-label {
            position: absolute;
            font-size: 12px; /* Reduced font size */
            transform: translateX(-50%);
            top: -30px; /* Adjusted position to avoid overlap */
        }
        .milestone-label2 {
            position: absolute;
            font-size: 12px; /* Reduced font size */
            transform: translateX(-50%);
            top: -60px; /* Adjusted position to avoid overlap */
        }
    </style>
</head>
<body>

<h2>Graphical Asset Lifecycle Timeline</h2>

<div class="timeline-container">
    <div class="year-labels">
        <?php
        // Display year labels dynamically
        for ($year = 2018; $year <= 2026; $year++) {
            echo "<span>$year</span>";
        }
        ?>
    </div>

    <?php
    // Total years span for calculating position
    $start_year = 2018;
    $end_year = 2026;
    $total_years = $end_year - $start_year;

    foreach ($assets as $asset) {
        // Calculate positions based on year span
        $start_percent = (($asset['purchaseYear'] - $start_year) / $total_years) * 100;
        $end_percent = (($asset['replacementYear'] - $start_year) / $total_years) * 100;
        $bar_class = ($asset['status'] === "Replacement Alert") ? "status-replacement-alert" : "status-end-of-life";

        echo "
        <div class='asset-row'>
            <div class='asset-label'>
                {$asset['name']} ({$asset['employeeName']})
            </div>
            <div class='asset-timeline'>
                <div class='timeline-bar $bar_class' style='left: $start_percent%; width: " . ($end_percent - $start_percent) . "%;'></div>
                
                <!-- Purchase milestone -->
                <div class='milestone' style='left: $start_percent%;'></div>
                <div class='milestone-label' style='left: $start_percent%;'>Purchase: {$asset['purchaseYear']}</div>

                <!-- Replacement/End of Life milestone -->
                <div class='milestone end' style='left: $end_percent%;'></div>
           
            </div>
        </div>
        ";
    }
    ?>
</div>

<!-- <div class='milestone-label2' style='left: $end_percent%;'>End: {$asset['replacementYear']}</div> -->

</body>
</html>
