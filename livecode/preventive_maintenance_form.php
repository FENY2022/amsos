<?php 

$id = $_GET['id'];

// Database connection
require_once 'connect.php';

// Fetch property information
$query = "SELECT DISTINCT division, used_by, article, property_no, accounting_officer, mr_number, description 
          FROM inv_preventive_maintenance_schedule 
          WHERE inv_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Fetch maintenance tasks
$tasksQuery = "SELECT task, month, status 
               FROM inv_preventive_maintenance_schedule 
               WHERE inv_id = ?";
$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$tasksResult = $stmt->get_result();

// Organize tasks from database into an array
$taskStatuses = [];
while ($task = $tasksResult->fetch_assoc()) {
    $taskStatuses[$task['task']][$task['month']] = $task['status'];
}

// Default tasks to always display
$defaultTasks = [
    "Defragment",
    "Scan Disk",
    "Error Checking",
    "Uninstall Free Trial Antivirus",
    "Nozzle Checking",
    "Printer Head Cleaning",
];



$id = $_GET['id'] ?? null;
$data = [];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM inv_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
}



$maintenance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$remarks = '';

if ($maintenance_id > 0) {
    $sql = "SELECT remarks FROM inv_preventive_maintenance_schedule WHERE maintenance_id = '$maintenance_id' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $remarks = $row['remarks'];
    }
}


// Get current year from the form input or default to the current year
$selectedYear = isset($_POST['year']) ? intval($_POST['year']) : date("Y");

// List of months
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Generate dynamic remarks based on maintenance tasks and statuses
$remarks = '';

if (!empty($taskStatuses)) {
    $completedTasks = [];
    $pendingTasks = [];

    foreach ($taskStatuses as $taskName => $monthsStatuses) {
        $completedMonths = [];
        $pendingMonths = [];

        foreach ($monthsStatuses as $month => $status) {
            if ($status) {
                $completedMonths[] = $months[$month];
            } else {
                $pendingMonths[] = $months[$month];
            }
        }

        if (!empty($completedMonths)) {
            $completedTasks[] = "$taskName (completed in " . implode(', ', $completedMonths) . ")";
        }

        if (!empty($pendingMonths)) {
            $pendingTasks[] = "$taskName (pending in " . implode(', ', $pendingMonths) . ")";
        }
    }

    if (!empty($completedTasks)) {
        $remarks .= "The following tasks were completed in $selectedYear: " . implode('; ', $completedTasks) . ". ";
    }

    if (!empty($pendingTasks)) {
        $remarks .= "The following tasks are still pending in $selectedYear: " . implode('; ', $pendingTasks) . ". ";
    }

    if (empty($completedTasks) && empty($pendingTasks)) {
        $remarks = "No maintenance tasks have been recorded for $selectedYear.";
    } else {
        $remarks .= "Ensure pending tasks are completed promptly.";
    }
} else {
    $remarks = "No maintenance data available for this equipment in $selectedYear.";
}

// Generate equipment health assessment based on Description and yearAcquired
$description = $data['specifications'] ?? '';
$yearAcquired = $data['yearAcquired'] ?? date('Y');
$currentYear = date('Y');
$equipmentAge = $currentYear - $yearAcquired;

// Health assessment based on Description keywords
if (stripos($description, 'good') !== false || stripos($description, 'excellent') !== false) {
    $healthStatus = "The equipment appears to be in good condition.";
} elseif (stripos($description, 'needs repair') !== false || stripos($description, 'damaged') !== false) {
    $healthStatus = "The equipment requires attention or repair.";
} else {
    $healthStatus = "The equipment condition is unclear based on the description.";
}

// Optimization status based on equipment age
if ($equipmentAge <= 3) {
    $healthStatus .= " The equipment is relatively new and should be operating optimally.";
} elseif ($equipmentAge <= 6) {
    $healthStatus .= " The equipment is moderately aged and may require occasional maintenance.";
} else {
    $healthStatus .= " The equipment is aging and may need frequent maintenance or replacement.";
}

// Additional recommendations based on Description keywords
$recommendations = [];

// Detect Processor
if (preg_match('/Intel\s+Core\s+i\d-\d+/i', $description, $processorMatch)) {
    $processor = $processorMatch[0];
    if (stripos($processor, 'i3') !== false || stripos($processor, 'Celeron') !== false || stripos($processor, 'Pentium') !== false) {
        $recommendations[] = "Consider upgrading the processor to Intel i5 or higher for better performance.";
    }
}

// Detect RAM
if (preg_match('/\b(\d+GB)\s+DDR\d/i', $description, $ramMatch)) {
    $ram = $ramMatch[1];
    if (intval($ram) <= 4) {
        $recommendations[] = "Consider upgrading the RAM to 8GB or more for better multitasking capabilities.";
    }
}

// Detect ROM (Storage)
if (preg_match('/\b(\d+TB|\d+GB)\s+(HDD|SSD)/i', $description, $storageMatch)) {
    $storage = $storageMatch[0];
    if (stripos($storage, 'HDD') !== false) {
        $recommendations[] = "Consider upgrading to an SSD for better performance.";
    }
}

// Detect GPU
if (preg_match('/NVIDIA\s+GeForce\s+\w+\s+\d+GB/i', $description, $gpuMatch)) {
    $gpu = $gpuMatch[0];
}

// Append health assessment and recommendations to remarks
$remarks .= " " . $healthStatus;

if (!empty($recommendations)) {
    $remarks .= " Recommendations: " . implode(' ', $recommendations);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Preventive Maintenance Checklist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .form-header p {
            font-size: 14px;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #333;
            border-bottom: 2px solid #00a19d;
            padding-bottom: 5px;
        }

        .form-group {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .form-group label {
            flex: 0 0 30%;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #00a19d;
            color: #fff;
            font-weight: bold;
        }

        .schedule {
            background: #e9f8f6;
        }

        .submit-btn {
            text-align: center;
        }

        .submit-btn button {
            padding: 10px 20px;
            background: #00a19d;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn button:hover {
            background: #007a74;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>ICT Preventive Maintenance Checklist</h1>
            <p>Ensure proper maintenance of ICT equipment.</p>
        </div>

        <div class="section">
    <h3>Property Information</h3>

    <form method="POST" action="save_checklist.php">



    <div class="form-group">
        <label for="division">Division/Section:</label>
        <input type="text" id="division" name="division" value="<?php echo htmlspecialchars($data['officeDivision'] ?? ''); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="used-by">Used By:</label>
        <input type="text" id="used-by" name="used-by" value="<?php echo htmlspecialchars($data['actualUser'] ?? ''); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="article">Article:</label>
        <input type="text" id="article" name="article" value="<?php echo htmlspecialchars($data['equipmentType'] ?? ''); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="property-no">Property No:</label>
        <input type="text" id="property-no" name="property-no" value="<?php echo htmlspecialchars($data['propertyNumber'] ?? ''); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="accounting-officer">Accounting Officer:</label>
        <input type="text" id="accounting-officer" name="accounting-officer" value="<?php echo htmlspecialchars($data['accountablePerson'] ?? ''); ?>" readonly>
    </div>
    <div class="form-group">
        <label for="mr-number">MR Number:</label>
        <input type="text" id="mr-number" name="mr-number" value="<?php echo htmlspecialchars($data['remarks'] ?? ''); ?>" readonly>
    </div>
    
    <div class="form-group">
        <label for="division">Brand:</label>
        <input type="brand" id="brand" name="brand" value="<?php echo htmlspecialchars($data['brand'] ?? ''); ?>" readonly>
    </div>

    <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="3" readonly><?php echo htmlspecialchars($data['specifications'] ?? ''); ?></textarea>
    </div>
</div>


        <h4>Maintenance Schedule</h4>


            <div class="form-group">
                <select name="year" id="year">
                    <?php
                        $currentYear = date("Y");
                        for ($year = 2024; $year <= 2050; $year++) {
                            $selected = ($year == $currentYear) ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                    ?>
                </select>
            </div>

            <input type="text" id="inv_id" name="inv_id" value="<?php echo $id; ?>" hidden>
            <table>
                <thead>
                    <tr>
                        <th>Maintenance Task</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($defaultTasks as $taskName) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($taskName) . "</td>";
                        for ($i = 1; $i <= 12; $i++) {
                            $checked = isset($taskStatuses[$taskName][$i]) && $taskStatuses[$taskName][$i] ? 'checked' : '';
                            echo "<td>
                                <input type='checkbox' name='tasks[$taskName][$i]' value='1' $checked>
                                <input type='hidden' name='task_hidden[$taskName][$i]' value='1'>
                            </td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>

            </table>
            <br>

            <div class="form-group">
                <label for="remarks">Remarks:</label>
                <textarea id="remarks" name="remarks" style="width: 300px; height: 150px; resize: vertical;"><?php echo htmlspecialchars($remarks); ?></textarea>

            </div>



            <div class="submit-btn">
                <button type="submit">Save Checklist</button>
            </div>



        </form>




    </div>
</body>
</html>


