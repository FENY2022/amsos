<?php
require_once 'connect_amsos.php';

// Get the raw POST data
$input = file_get_contents('php://input');

// Decode JSON data
$data = json_decode($input, true);

// Validate required fields
if (!isset($data['id'], $data['feedback'], $data['acknowledged_by'], $data['created_at'], $data['date_rated'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data.']);
    exit;
}

$id = $data['id'];
$feedback = $data['feedback'];
$acknowledged_by = $data['acknowledged_by'];
$created_at = $data['created_at'];
$date_rated = $data['date_rated'];

// Prepare the SQL update statement
$query = "UPDATE srffeedback 
          SET feedback = ?, acknowledged_by = ?, created_at = ?, date_rated = ? 
          WHERE id = ?";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssi", $feedback, $acknowledged_by, $created_at, $date_rated, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
