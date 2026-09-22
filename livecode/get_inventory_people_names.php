<?php
require_once "connect.php";

header('Content-Type: application/json');

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
