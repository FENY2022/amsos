<?php
// -------------------------------------------------------------------------
// 1. DATABASE CONFIGURATION
// -------------------------------------------------------------------------
$servername = "localhost";
$username   = "root";                // Update if your username is different
$password   = "";                    // Update if you have a password
$dbname     = "u645536029_ict_amsos_db"; // Database name from your SQL file

// -------------------------------------------------------------------------
// 2. FETCH DATA
// -------------------------------------------------------------------------
$data = [];
$count = 0;
$errorMsg = "";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // SQL to find employees where equipmentType is "N/A"
    $sql = "SELECT id, employeeName, equipmentType, propertyNumber, remarks 
            FROM inv_inventory 
            WHERE equipmentType = 'N/A' 
            ORDER BY id DESC"; // Showing newest first usually looks better
    
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $count = count($data);
    } else {
        throw new Exception("Error executing query: " . $conn->error);
    }

    $conn->close();

} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N/A Equipment Report | AMSOS</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --bg-color: #f3f4f6;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --danger: #ef4444;
            --success: #10b981;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Card Styling */
        .card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 25px;
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card-header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .badge-count {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        /* Table Styling */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background-color: #f9fafb;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #fee2e2;
            color: #991b1b;
        }

        .remarks-text {
            color: var(--text-muted);
            font-style: italic;
            font-size: 0.85rem;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: var(--text-muted);
        }

        /* Toast Notification Styling */
        #toast {
            visibility: hidden;
            min-width: 300px;
            background-color: #333;
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            right: 30px;
            bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s, bottom 0.3s;
            border-left: 5px solid var(--primary);
        }

        #toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }

        .toast-icon {
            margin-right: 12px;
            font-size: 20px;
        }

        .toast-title {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        .toast-msg {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <div class="container">
        <?php if (!empty($errorMsg)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong>System Error:</strong> <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div>
                    <h1>Inventory Exceptions</h1>
                    <p>Employees with Equipment Type marked as "N/A"</p>
                </div>
                <div class="badge-count">
                    <?php echo $count; ?> Records Found
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee Name</th>
                            <th>Equipment Type</th>
                            <th>Property Number</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($count > 0): ?>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td style="color: var(--text-muted);">#<?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['employeeName']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge"><?php echo htmlspecialchars($row['equipmentType']); ?></span>
                                    </td>
                                    <td style="font-family: monospace; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($row['propertyNumber'] ?: '---'); ?>
                                    </td>
                                    <td>
                                        <span class="remarks-text"><?php echo htmlspecialchars($row['remarks'] ?: 'No remarks'); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    No records found with Equipment Type "N/A".
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            ICT AMSOS System &copy; <?php echo date("Y"); ?>
        </div>
    </div>

    <div id="toast">
        <div class="toast-icon">🔔</div>
        <div>
            <span class="toast-title">System Alert</span>
            <span class="toast-msg">Found <strong><?php echo $count; ?></strong> items with "N/A" type.</span>
        </div>
    </div>

    <script>
        // JavaScript to trigger Toast
        document.addEventListener('DOMContentLoaded', function() {
            var recordCount = <?php echo $count; ?>;
            // Only show toast if records exist or even if 0 just to notify check complete
            showToast();
        });

        function showToast() {
            var x = document.getElementById("toast");
            x.className = "show";
            // Hide after 4 seconds
            setTimeout(function(){ x.className = x.className.replace("show", ""); }, 4000);
        }
    </script>

</body>
</html>