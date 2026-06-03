<?php
// Database connection parameters
$servername = "localhost";
$username   = "root"; // Update with your DB username
$password   = "";     // Update with your DB password
$dbname     = "amsos"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL Query using INNER JOIN
// We count DISTINCT trackid so that multiple history logs for the same repair ticket aren't counted as multiple repairs
$sql = "SELECT 
            i.propertyNumber, 
            i.typeOfEquipment, 
            i.brandName, 
            i.description,
            i.actualUser, 
            COUNT(DISTINCT s.trackid) as repair_count 
        FROM inv_inventory i
        INNER JOIN srfhistory s ON i.id = s.equipment_id
        GROUP BY i.id
        ORDER BY repair_count DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Repair Frequency</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .table-wrapper { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="table-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary m-0">Equipment Repair Frequency</h3>
            <button class="btn btn-secondary btn-sm" onclick="window.print()">Print Report</button>
        </div>
        
        <p class="text-muted">This table displays the total number of times each equipment has been repaired based on Service Request Form (SRF) tracking records.</p>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Property Number</th>
                        <th scope="col">Actual User</th>
                        <th scope="col">Type of Equipment</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-center">Times Repaired</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $counter = 1;
                        while($row = $result->fetch_assoc()) {
                            // Truncate long descriptions to keep the table clean
                            $desc = htmlspecialchars($row['description']);
                            $short_desc = strlen($desc) > 60 ? substr($desc, 0, 60) . "..." : $desc;
                            
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td class='fw-bold'>" . htmlspecialchars($row['propertyNumber']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['actualUser']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['typeOfEquipment']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['brandName']) . "</td>";
                            echo "<td><span title='$desc'>$short_desc</span></td>";
                            
                            // Highlighting the repair count dynamically
                            $badgeColor = $row['repair_count'] > 3 ? 'bg-danger' : 'bg-warning text-dark';
                            echo "<td class='text-center'><span class='badge $badgeColor rounded-pill fs-6'>" . $row['repair_count'] . "</span></td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted py-4'>No repair history found for any equipment.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
if($conn) {
    $conn->close();
}
?>4