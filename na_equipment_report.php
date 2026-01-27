<?php
// -------------------------------------------------------------------------
// 1. DATABASE CONFIGURATION
// -------------------------------------------------------------------------
$servername = "localhost";
$username   = "root";      // Replace with your database username
$password   = "";          // Replace with your database password
$dbname     = "u645536029_ict_amsos_db"; // The database name from your SQL file

// -------------------------------------------------------------------------
// 2. CREATE CONNECTION & FETCH DATA
// -------------------------------------------------------------------------
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL Query to get employees with 'N/A' equipment type
$sql = "SELECT employeeName, equipmentType FROM inv_inventory WHERE equipmentType = 'N/A'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N/A Equipment Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 900px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #d9534f; /* Red color to highlight the 'N/A' alert nature */
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background-color: #333;
            color: #fff;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .footer {
            padding: 10px;
            text-align: right;
            font-size: 12px;
            color: #777;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Inventory Exception Report</h2>
            <p>List of Employees with Equipment Type marked as "N/A"</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th width="50%">Employee Name</th>
                    <th width="40%">Equipment Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $count = 1;
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $count++ . "</td>";
                        echo "<td><strong>" . htmlspecialchars($row["employeeName"]) . "</strong></td>";
                        echo "<td style='color: #d9534f; font-weight: bold;'>" . htmlspecialchars($row["equipmentType"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='no-data'>No records found where Equipment Type is N/A.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <div class="footer">
            Generated on: <?php echo date("Y-m-d H:i:s"); ?>
        </div>
    </div>

</body>
</html>

<?php
// -------------------------------------------------------------------------
// 3. CLOSE CONNECTION
// -------------------------------------------------------------------------
$conn->close();
?>