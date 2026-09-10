<?php

require_once 'connect.php';

// Check if 'equipment_id' exists in the URL
if (isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
    // Sanitize the equipment_id to prevent XSS or SQL injection
    
    $equipmentId = $conn->real_escape_string($_GET['equipment_id']);

    // Query to fetch all fields from inv_inventory table
    $query = "SELECT 
                id, employeeName, equipmentType, yearAcquired, shelfLife, brand, specifications,
                rangeCategory, softwareInstalled, licensingModel, softwareInstalled_2, licensingModel_2,
                serialNumber, propertyNumber, accountablePerson, sex, officeDivision, statusOfEmployment,
                actualUser, actualUserSex, actualUserStatusOfEmployment, natureOfWork, remarks, office
              FROM inv_inventory 
              WHERE id = '$equipmentId'";

    $result = $conn->query($query);

    // Check if the record exists
    if ($result->num_rows > 0) {
        // Fetch the row data
        $row = $result->fetch_assoc();
    } else {
        // If no record found, set error message
        $error = "No record found for Equipment ID: $equipmentId";
    }
} else {
    // If 'equipment_id' is not present, redirect to another page
    // header("Location: equipment_list.php");
    

    // exit(); // Stop further execution of the script
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 70%;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }

        .back-btn {
            display: block;
            width: 100%;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Equipment Details</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?= $error; ?></p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <?php foreach ($row as $field => $value): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($field); ?></strong></td>
                        <td><?= htmlspecialchars($value); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <button class="back-btn" onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
