<?php
header('Content-Type: application/json');

require_once 'connect.php';
require_once 'session_checker.php';

$equipmentId = $_GET['equipment_id'] ?? '';
$id = 0;

if ($equipmentId !== '') {
    $parsedUrl = parse_url($equipmentId);

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        $id = isset($queryParams['id']) ? (int) $queryParams['id'] : 0;
    } else {
        $id = (int) $equipmentId;
    }
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid equipment ID.']);
    exit;
}

$office = $_SESSION['OfficeSRF'] ?? '';
$query = "SELECT id, employeeName, equipmentType, yearAcquired, brand, amount, propertyNumber
          FROM inv_inventory
          WHERE id = ? AND Office = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Unable to prepare equipment lookup.']);
    exit;
}

$stmt->bind_param('is', $id, $office);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'row' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data found for this QR code.']);
}

$stmt->close();
?>
