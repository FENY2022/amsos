<?php
require_once "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['office'])) {
    $selectedOffice = $_POST['office'];

    $divisionsQuery = "SELECT DISTINCT officeDivision FROM inventory_people WHERE office = ? AND officeDivision IS NOT NULL AND officeDivision != '' ORDER BY officeDivision ASC";
    $stmt = $conn->prepare($divisionsQuery);
    $stmt->bind_param('s', $selectedOffice);
    $stmt->execute();
    $divisionsResult = $stmt->get_result();

    if ($divisionsResult->num_rows > 0) {
        while ($row = $divisionsResult->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['officeDivision']) . '">' . htmlspecialchars($row['officeDivision']) . '</option>';
        }
    } else {
        echo '<option value="">No divisions found</option>';
    }

    $stmt->close();
}

$conn->close();
?>
