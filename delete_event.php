<?php
require_once 'calendarSchedulerdb.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = trim($input['id'] ?? '');

if ($id === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Missing event ID.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM events WHERE id = ?');

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare delete query.']);
    exit;
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to delete the event.']);
}

$stmt->close();
$conn->close();
