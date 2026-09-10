<?php
require_once "connect.php";

$query = "SELECT * FROM inv_inventory WHERE 1=1";

if (!empty($_GET['employeeName'])) {
    $employeeName = $conn->real_escape_string($_GET['employeeName']);
    $query .= " AND employeeName LIKE '%$employeeName%'";
}
if (!empty($_GET['officeDivision'])) {
    $officeDivision = $conn->real_escape_string($_GET['officeDivision']);
    $query .= " AND officeDivision = '$officeDivision'";
}
if (!empty($_GET['equipmentType'])) {
    $equipmentType = $conn->real_escape_string($_GET['equipmentType']);
    $query .= " AND equipmentType = '$equipmentType'";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employeeName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['equipmentType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['officeDivision']) . "</td>";
        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
        echo "<td>" . htmlspecialchars($row['yearAcquired']) . "</td>";
        echo "<td><a href=\"mainmenu.php?dir=preventive_maintenance_form&id=" . $row['id'] . "\" class=\"btn btn-success btn-sm\">Select</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No records found.</td></tr>";
}
// $conn->close();
?>
