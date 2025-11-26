<?php



include 'connect.php';

$query = "SELECT trackid, name, details, date, time, status FROM srfhistory";
$result2 = $conn->query($query);

if ($result2->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr>
            <th>Track ID</th>
            <th>Name</th>
            <th>Details</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
          </tr>";
    
    while($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['trackid'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['details'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['time'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No records found.";
}

// Close the database connection
$conn->close();






?>