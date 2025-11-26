<?php
require_once 'connect.php';

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are present
if (isset($data['id'], $data['time'], $data['remarks'])) {
    $id = $data['id'];
    $time = $data['time'];
    $remarks = $data['remarks'];
    $date = $data['date'];

    // Prepare the update query
    $query = "UPDATE srf_actiontaken SET time = ?, remarks = ?, date = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssi", $time, $remarks, $date, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Incomplete data received']);
}
?>