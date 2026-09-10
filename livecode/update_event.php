<?php
require_once 'calendarSchedulerdb.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$source = is_array($input) && !empty($input) ? $input : $_POST;
$id = trim($source['id'] ?? '');
$event_date = trim($source['event_date'] ?? '');
$remarks = trim($source['remarks'] ?? '');
$zoom_link = trim($source['zoom_link'] ?? '');
$password = trim($source['password'] ?? '');
$email = trim($source['email'] ?? '');

if ($id === '' || $event_date === '' || $remarks === '' || $password === '' || $email === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
    exit;
}

$stmt = $conn->prepare('UPDATE events SET event_date = ?, remarks = ?, zoom_link = ?, password = ?, email = ? WHERE id = ?');

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare the update query.']);
    exit;
}

$stmt->bind_param('sssssi', $event_date, $remarks, $zoom_link, $password, $email, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update the event.']);
}

$stmt->close();
$conn->close();
