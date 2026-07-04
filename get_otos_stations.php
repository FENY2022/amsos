<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';
require_once 'connect_otos.php';

header('Content-Type: application/json');

function sendOtosJson($payload) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode($payload);
    exit;
}

$office = trim((string)($_SESSION['OfficeSRF'] ?? ''));

if ($office === '') {
    sendOtosJson(['success' => false, 'message' => 'No office found in session.', 'stations' => []]);
}

$stmt = $conn_otos->prepare("SELECT DISTINCT Station FROM useremployee WHERE Office = ? AND Station IS NOT NULL AND TRIM(Station) != '' AND status_toggle = 'active' AND is_active = 1 ORDER BY Station");
$stmt->bind_param('s', $office);
$stmt->execute();
$result = $stmt->get_result();

$stations = [];
while ($row = $result->fetch_assoc()) {
    $stations[] = $row['Station'];
}

$stmt->close();
$conn->close();
$conn_otos->close();

sendOtosJson(['success' => true, 'stations' => $stations]);
?>
