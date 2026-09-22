<?php
require_once "connect.php";

$office = trim($_POST['office'] ?? '');
$officeDivision = trim($_POST['officeDivision'] ?? ($_POST['station'] ?? ''));
$fullname = trim($_POST['fullname'] ?? '');

$sql = "SELECT id, otos_user_id, full_name, office, officeDivision, employment_status, source, created_at
        FROM inventory_people
        WHERE office = ?";
$params = [$office];
$types = 's';

if ($officeDivision !== '') {
    $sql .= " AND officeDivision = ?";
    $params[] = $officeDivision;
    $types .= 's';
}

if ($fullname !== '') {
    $sql .= " AND full_name LIKE ?";
    $params[] = "%$fullname%";
    $types .= 's';
}

$sql .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

echo '<div style="max-height: 500px; overflow: auto; border: 1px solid #ccc; padding: 10px; border-radius: 8px;">';
echo '<table style="width: 100%; border-collapse: collapse;">';
echo '<tr style="background-color: #4CAF50; color: white;">';
echo '<th style="padding: 8px; text-align: left;">Full Name</th>';
echo '<th style="padding: 8px; text-align: left;">Office</th>';
echo '<th style="padding: 8px; text-align: left;">Office Division</th>';
echo '<th style="padding: 8px; text-align: left;">Employment Status</th>';
echo '<th style="padding: 8px; text-align: left;">Source</th>';
echo '<th style="padding: 8px; text-align: left;">OTOS User ID</th>';
echo '<th style="padding: 8px; text-align: left;">Saved Date</th>';
echo '</tr>';

$rowCount = 0;

while ($row = $result->fetch_assoc()) {
    $rowCount++;
    echo '<tr style="border-bottom: 1px solid #ddd;">';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['full_name']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['office']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['officeDivision']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['employment_status']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['source']) . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['otos_user_id'] ?? '') . '</td>';
    echo '<td style="padding: 8px;">' . htmlspecialchars($row['created_at']) . '</td>';
    echo '</tr>';
}

if ($rowCount === 0) {
    echo '<tr><td colspan="7" style="padding: 16px; text-align: center;">No records found.</td></tr>';
}

echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
echo '<td colspan="6" style="padding: 8px; text-align: right;">Total Rows:</td>';
echo '<td style="padding: 8px; text-align: left;">' . $rowCount . '</td>';
echo '</tr>';

echo '</table>';
echo '</div>';

$stmt->close();
$conn->close();
?>
