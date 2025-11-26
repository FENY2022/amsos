<!DOCTYPE html>
<html>
<head>
  <!-- Add your CSS styles here -->
  <style>
    /* Your existing styles */
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #f2f2f2;
    }
  </style>
</head>
<body>
  <!-- Your existing HTML code -->



    
 <label for="Office">Office:</label>
<select name="Office" id="Office">
  <option value="RO PMD">RO PMD</option>
  <option value="RO IT">RO IT</option>
  <option value="RO HR">RO HR</option>
  <option value="RO Finance">RO Finance</option>
</select>


  <br><br> <br>


  
<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);


include "connect.php" ;

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the database
$sql = "SELECT * FROM addinventory";
$result = $conn->query($sql);

$conn->close();
?>
 
  <table>
    <thead>
      <tr>
        <th>No.</th>
        <th>Name of Employee</th>
        <th>Position</th> 
        <th>Designation</th>
        <th>Asset Class</th>
        <th>Year Acquired</th>
        <th>Specification/Range Category</th>
        <th>Production Date</th>
        <th>Total No. of assigned End-Device</th>
        <th>Office</th>
        <th>ID</th>
      </tr>
    </thead>
    <tbody>

    <?php




        if ($result->num_rows > 0) {
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $counter . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["position"] . "</td>";
                echo "<td>" . $row["designation"] . "</td>";
                echo "<td>" . $row["asset_class"] . "</td>";
                echo "<td>" . $row["year_acquired"] . "</td>";
                echo "<td>" . $row["specification"] . "</td>";
                echo "<td>" . formatDate(date("Y-m-d", strtotime($row["production_date"]))) . "</td>";
                echo "<td>" . $row["total_number_assigned"] . "</td>";
                echo "<td>" . $row["office"] . "</td>";
                echo "<td>" . $row["ID"] . "</td> ";
                echo "</tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='10'>No data available</td></tr>";
        }
        ?>

    </tbody>
  </table>
  <!-- Your existing content -->

</body>
</html>
