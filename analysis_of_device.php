<?php
// Define the current directory and build URL parameters dynamically
$currentDir = "mainmenu.php?dir=analysis_of_device";

// Preserve GET parameters for consistent filtering
$officeDivision = isset($_GET['officeDivision']) ? $_GET['officeDivision'] : "";
$employeeName = isset($_GET['employeeName']) ? $_GET['employeeName'] : "";
$equipmentType = isset($_GET['equipmentType']) ? $_GET['equipmentType'] : "";

// Get the current year
$currentYear = date("Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Replacement Indicator</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .replaceable {
            background-color: #ffdddd;
            color: #d00000;
        }
        .not-replaceable {
            background-color: #ddffdd;
            color: #008000;
        }
    </style>
</head>
<body>
    <h1>Equipment/Device Condition</h1>
    
    <form method="GET" action="mainmenu.php">
        <input type="hidden" name="dir" value="analysis_of_device">
        <label for="officeDivision">Filter by Office Division:</label>
        <select name="officeDivision" id="officeDivision" onchange="this.form.submit()">
            <option value="">-- All --</option>
            <?php
            // Database connection
            $conn = new mysqli("localhost", "root", "", "amsos");

            $officeQuery = "SELECT DISTINCT officeDivision FROM inv_inventory";
            $officeResult = $conn->query($officeQuery);

            while ($row = $officeResult->fetch_assoc()) {
                $selected = ($officeDivision === $row['officeDivision']) ? "selected" : "";
                echo "<option value='" . $row['officeDivision'] . "' $selected>" . strtoupper($row['officeDivision']) . "</option>";

            }
            ?>
        </select>

        <label for="employeeName">Filter by Employee Name:</label>
        <select name="employeeName" id="employeeName" onchange="this.form.submit()">
            <option value="">-- All --</option>
            <?php
            if ($officeDivision != "") {
                $employeeQuery = "SELECT DISTINCT employeeName FROM inv_inventory WHERE officeDivision = '" . $conn->real_escape_string($officeDivision) . "'";
                $employeeResult = $conn->query($employeeQuery);

                while ($row = $employeeResult->fetch_assoc()) {
                    $selected = ($employeeName === $row['employeeName']) ? "selected" : "";
                    echo "<option value='" . $row['employeeName'] . "' $selected>" . $row['employeeName'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="equipmentType">Filter by Equipment Type:</label>
        <select name="equipmentType" id="equipmentType" onchange="this.form.submit()">
            <option value="">-- All --</option>
            <?php
            if ($employeeName != "") {
                $equipmentQuery = "SELECT DISTINCT equipmentType FROM inv_inventory WHERE officeDivision = '" . $conn->real_escape_string($officeDivision) . "' AND employeeName = '" . $conn->real_escape_string($employeeName) . "'";
                $equipmentResult = $conn->query($equipmentQuery);

                while ($row = $equipmentResult->fetch_assoc()) {
                    $selected = ($equipmentType === $row['equipmentType']) ? "selected" : "";
                    echo "<option value='" . $row['equipmentType'] . "' $selected>" . $row['equipmentType'] . "</option>";
                }
            }
            ?>
        </select>
    </form>

    <table>
    <thead>
        <tr>
            <th>Office Division</th>
            <th>Employee Name</th>
            <th>Equipment Type</th>
            <th>Item Name</th>
            <th>Year Acquired</th>
            <th>Equipment Condition</th>
        </tr>
    </thead>
    <tbody>
          <?php
    // Build dynamic query with filters
    $filterQuery = "SELECT officeDivision, employeeName, equipmentType, yearAcquired, brand, specifications FROM inv_inventory WHERE 1=1";

    if ($officeDivision != "") {
        $filterQuery .= " AND officeDivision = '" . $conn->real_escape_string($officeDivision) . "'";
    }
    if ($employeeName != "") {
        $filterQuery .= " AND employeeName = '" . $conn->real_escape_string($employeeName) . "'";
    }
    if ($equipmentType != "") {
        $filterQuery .= " AND equipmentType = '" . $conn->real_escape_string($equipmentType) . "'";
    }

    $result = $conn->query($filterQuery);

    // Count the total number of records
    $totalRecords = $result ? $result->num_rows : 0;

    if ($totalRecords > 0) {
        while ($row = $result->fetch_assoc()) {
            $officeDivision = $row['officeDivision'];
            $employeeName = $row['employeeName'];
            $equipmentType = $row['equipmentType'];
            $yearAcquired = $row['yearAcquired'];
            $brand = $row['brand'];
            $specification = $row['specifications'];

            // Determine if replaceable
            // $isReplaceable = ($currentYear - $yearAcquired) >= 5;
            $isReplaceable = ((int)$currentYear - (int)$yearAcquired) >= 5;



            // Output row with class indicator
            echo "<tr>";
            echo "<td>$officeDivision</td>";
            echo "<td>$employeeName</td>";
            echo "<td>$equipmentType</td>";
            echo "<td><div style='word-wrap: break-word; max-width: 200px;'>{$brand} : {$specification}</div></td>";
            echo "<td>$yearAcquired</td>";

            echo $isReplaceable
                ? "<td class='replaceable'>Outdated</td>"
                : "<td class='not-replaceable'>Updated</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No data found</td></tr>";
    }

    $conn->close();
    ?>

    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: right; font-weight: bold;">Total Records: <?php echo $totalRecords; ?></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
