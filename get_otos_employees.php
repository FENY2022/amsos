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

function normalizeOtosInventoryName($name) {
    return strtoupper(trim((string)$name));
}

$office = trim((string)($_SESSION['OfficeSRF'] ?? ''));
$station = trim((string)($_GET['station'] ?? ''));

if ($office === '' || $station === '') {
    sendOtosJson(['success' => false, 'message' => 'Office and station are required.', 'employees' => []]);
}

$stmt = $conn_otos->prepare("SELECT id, Full_Name, Office, Station, Div_Sec_Unit, Employment_Status FROM useremployee WHERE Office = ? AND Station = ? AND status_toggle = 'active' AND is_active = 1 ORDER BY Full_Name");
$stmt->bind_param('ss', $office, $station);
$stmt->execute();
$result = $stmt->get_result();

$otosEmployees = [];
$otosIds = [];
$normalizedNames = [];
while ($row = $result->fetch_assoc()) {
    $normalizedName = normalizeOtosInventoryName($row['Full_Name']);
    $otosEmployees[] = $row + ['normalized_name' => $normalizedName];
    $otosIds[] = (int)$row['id'];
    if ($normalizedName !== '') {
        $normalizedNames[] = $normalizedName;
    }
}
$stmt->close();

$includedByOtosId = [];
$includedByName = [];

if (!empty($otosIds)) {
    $placeholders = implode(',', array_fill(0, count($otosIds), '?'));
    $types = str_repeat('i', count($otosIds));
    $stmt = $conn->prepare("SELECT otos_user_id FROM inventory_people WHERE otos_user_id IN ($placeholders)");
    $stmt->bind_param($types, ...$otosIds);
    $stmt->execute();
    $localResult = $stmt->get_result();
    while ($row = $localResult->fetch_assoc()) {
        $includedByOtosId[(int)$row['otos_user_id']] = true;
    }
    $stmt->close();
}

if (!empty($normalizedNames)) {
    $normalizedNames = array_values(array_unique($normalizedNames));
    $placeholders = implode(',', array_fill(0, count($normalizedNames), '?'));
    $types = str_repeat('s', count($normalizedNames));
    $stmt = $conn->prepare("SELECT normalized_name FROM inventory_people WHERE normalized_name IN ($placeholders)");
    $stmt->bind_param($types, ...$normalizedNames);
    $stmt->execute();
    $localResult = $stmt->get_result();
    while ($row = $localResult->fetch_assoc()) {
        $includedByName[$row['normalized_name']] = true;
    }
    $stmt->close();
}

$employees = [];
foreach ($otosEmployees as $employee) {
    $otosId = (int)$employee['id'];
    $normalizedName = $employee['normalized_name'];
    $alreadyIncluded = isset($includedByOtosId[$otosId]) || isset($includedByName[$normalizedName]);

    $employees[] = [
        'id' => $otosId,
        'full_name' => $employee['Full_Name'],
        'station' => $employee['Station'],
        'division_unit' => $employee['Div_Sec_Unit'],
        'employment_status' => $employee['Employment_Status'],
        'already_included' => $alreadyIncluded,
    ];
}

$conn->close();
$conn_otos->close();

sendOtosJson(['success' => true, 'employees' => $employees]);
?>
