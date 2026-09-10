<?php
require_once 'connect.php';
require_once 'session_checker.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$stickerAttached = isset($_POST['sticker_attached']) ? (int) $_POST['sticker_attached'] : 0;
$office = $_SESSION['OfficeSRF'] ?? '';

if ($id <= 0 || ($stickerAttached !== 0 && $stickerAttached !== 1) || $office === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid sticker update data.']);
    exit;
}

$columnCheck = $conn->query("SHOW COLUMNS FROM inv_inventory LIKE 'sticker_attached'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE inv_inventory ADD COLUMN sticker_attached TINYINT(1) NOT NULL DEFAULT 0");
}

$stmt = $conn->prepare('UPDATE inv_inventory SET sticker_attached = ? WHERE id = ? AND Office = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare sticker update.']);
    exit;
}

$stmt->bind_param('iis', $stickerAttached, $id, $office);
$stmt->execute();

if ($stmt->affected_rows < 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to save sticker status.']);
    $stmt->close();
    exit;
}

$stmt->close();
echo json_encode(['success' => true, 'sticker_attached' => $stickerAttached]);
