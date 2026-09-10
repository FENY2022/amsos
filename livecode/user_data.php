<?php
header('Content-Type: application/json');

// Include database connection
require_once 'connect.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// SQL Query
$query = "SELECT `Equipment type` AS equipmentType, 
                 COUNT(DISTINCT accountablePerson) AS Accountable, 
                 COUNT(DISTINCT actualUser) AS Actual
          FROM inv_inventory
          GROUP BY `Equipment type`";

$result = $conn->query($query);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'equipmentType' => $row['equipmentType'],
            'Accountable' => $row['Accountable'],
            'Actual' => $row['Actual']
        ];
    }
} else {
    $data = [];
}

$conn->close();
echo json_encode($data);
?>
