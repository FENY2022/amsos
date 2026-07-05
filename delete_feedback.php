<?php
header('Content-Type: application/json');

require_once 'connect_amsos.php';

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->id)) {
    echo json_encode(['success' => false, 'error' => 'Record ID not provided']);
    exit;
}

$id = (int)$data->id;
$query = 'DELETE FROM srffeedback WHERE id = ?';
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare the SQL statement']);
    exit;
}

$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Query execution failed']);
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode([
    'success' => $stmt->affected_rows > 0,
    'error' => $stmt->affected_rows > 0 ? null : 'No record found with the given ID',
]);

$stmt->close();
$conn->close();
?>
