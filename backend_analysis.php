<?php
require_once 'connect.php';

$filterColumn = isset($_POST['filterColumn']) ? $_POST['filterColumn'] : 'equipmentType';
$filterValue = isset($_POST['filterValue']) ? $_POST['filterValue'] : '';

$response = [
    'options' => [],
    'count' => 0
];

// Sanitize column name to prevent SQL Injection
$allowedColumns = ['equipmentType', 'employeeName', 'officeDivision', 'rangeCategory'];
if (!in_array($filterColumn, $allowedColumns)) {
    echo json_encode($response);
    exit;
}

// Fetch distinct options for the selected column
$optionsQuery = "SELECT DISTINCT $filterColumn FROM inv_inventory";
$optionsResult = $conn->query($optionsQuery);

if ($optionsResult && $optionsResult->num_rows > 0) {
    while ($row = $optionsResult->fetch_assoc()) {
        $response['options'][] = $row[$filterColumn];
    }
}

// Fetch count based on selected value
$countQuery = "SELECT COUNT(*) as count FROM inv_inventory";
if (!empty($filterValue)) {
    $countQuery .= " WHERE $filterColumn = ?";
}

$stmt = $conn->prepare($countQuery);

if (!empty($filterValue)) {
    $stmt->bind_param("s", $filterValue);
}

$stmt->execute();
$countResult = $stmt->get_result();
if ($countResult) {
    $row = $countResult->fetch_assoc();
    $response['count'] = $row['count'];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
