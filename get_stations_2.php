<?php
require_once "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['office'])) {
    $selectedOffice = $_POST['office'];
    $stationsQuery = "SELECT DISTINCT Station FROM useremployee WHERE Office = ? AND Station IS NOT NULL AND Station != ''";
    $stmt = $conn->prepare($stationsQuery);
    $stmt->bind_param('s', $selectedOffice);
    $stmt->execute();
    $stationsResult = $stmt->get_result();

    if ($stationsResult->num_rows > 0) {
        while ($row = $stationsResult->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['Station']) . '">' . htmlspecialchars($row['Station']) . '</option>';
        }
    } else {
        echo '<option value="">No stations found</option>';
    }

    $stmt->close();
}

$conn->close();
?>