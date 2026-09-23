<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "connect.php";
require_once "role_access.php";

header('Content-Type: application/json');

if (
    !isset($_SESSION['loggedin']) ||
    $_SESSION['loggedin'] !== ($_SESSION['usernameSRF'] ?? null) ||
    !amsos_can_manage_configuration($_SESSION['User_RoleSRF'] ?? '')
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'names' => [], 'message' => 'Access denied.']);
    exit;
}

$office = trim($_POST['office'] ?? '');
$officeDivision = trim($_POST['officeDivision'] ?? '');
$names = [];

if ($office !== '') {
    $sql = "SELECT DISTINCT full_name FROM inventory_people WHERE office = ? AND full_name IS NOT NULL AND full_name != ''";
    $params = [$office];
    $types = 's';

    if ($officeDivision !== '') {
        $sql .= " AND officeDivision = ?";
        $params[] = $officeDivision;
        $types .= 's';
    }

    $sql .= " ORDER BY full_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $names[] = $row['full_name'];
    }

    $stmt->close();
}

$conn->close();
echo json_encode(['success' => true, 'names' => $names]);
?>
