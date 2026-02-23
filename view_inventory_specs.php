<?php
// Include your existing database connection file
require_once 'connect.php';

// Query to fetch the necessary columns from the inv_inventory table
$query = "SELECT brand, yearAcquired, rangeCategory, computer_specs, specifications, softwareInstalled, amount, remarks FROM inv_inventory ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Specifications Viewer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .table-responsive {
            background-color: #ffffff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h3 class="mb-4 text-center">Inventory Equipment Specifications</h3>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Brand</th>
                        <th>Year Acquired</th>
                        <th>Range Category</th>
                        <th>HDD</th>
                        <th>SSD</th>
                        <th>RAM</th>
                        <th>Processor</th>
                        <th>OS</th>
                        <th>Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Default values
                            $hdd = '-';
                            $ssd = '-';
                            $ram = '-';
                            $processor = '-';
                            $os = htmlspecialchars($row['softwareInstalled']); // Often OS is placed in softwareInstalled
                            
                            // Determine the source of the specs
                            $raw_specs = !empty($row['computer_specs']) ? $row['computer_specs'] : $row['specifications'];
                            
                            // 1. Check if the specs are stored as JSON
                            $json_specs = json_decode($raw_specs, true);
                            
                            if (json_last_error() === JSON_ERROR_NONE && is_array($json_specs)) {
                                $hdd = $json_specs['hdd'] ?? $json_specs['HDD'] ?? '-';
                                $ssd = $json_specs['ssd'] ?? $json_specs['SSD'] ?? '-';
                                $ram = $json_specs['ram'] ?? $json_specs['RAM'] ?? '-';
                                $processor = $json_specs['processor'] ?? $json_specs['Processor'] ?? '-';
                                if (isset($json_specs['os']) || isset($json_specs['OS'])) {
                                    $os = $json_specs['os'] ?? $json_specs['OS'];
                                }
                            } else {
                                // 2. If it's plain text, try basic extraction using Regex (e.g. format "RAM: 8GB | HDD: 1TB")
                                if (preg_match('/HDD:\s*([^,|]+)/i', $raw_specs, $m)) $hdd = trim($m[1]);
                                if (preg_match('/SSD:\s*([^,|]+)/i', $raw_specs, $m)) $ssd = trim($m[1]);
                                if (preg_match('/RAM:\s*([^,|]+)/i', $raw_specs, $m)) $ram = trim($m[1]);
                                if (preg_match('/Processor:\s*([^,|]+)/i', $raw_specs, $m)) $processor = trim($m[1]);
                                if (preg_match('/OS:\s*([^,|]+)/i', $raw_specs, $m)) $os = trim($m[1]);
                                
                                // If nothing matches, dump the raw string into Processor column so the data isn't lost visually
                                if ($hdd == '-' && $ssd == '-' && $ram == '-' && $processor == '-') {
                                    $processor = htmlspecialchars($raw_specs);
                                }
                            }

                            // Output Table Row
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['yearAcquired']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['rangeCategory']) . "</td>";
                            echo "<td>" . htmlspecialchars($hdd) . "</td>";
                            echo "<td>" . htmlspecialchars($ssd) . "</td>";
                            echo "<td>" . htmlspecialchars($ram) . "</td>";
                            echo "<td>" . htmlspecialchars($processor) . "</td>";
                            echo "<td>" . htmlspecialchars($os) . "</td>";
                            echo "<td>₱" . number_format((float)$row['amount'], 2) . "</td>";
                            echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No inventory records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>