<?php


require_once 'connect.php';
// Fetch data for the table
$query = "SELECT employeeName AS name, 
                 equipmentType AS device, 
                 CASE 
                     WHEN shelfLife = 'Beyond 5 year' THEN 'outdated' 
                     WHEN shelfLife = 'Within 5 Year' THEN 'virtually_outdated' 
                     ELSE 'updated' 
                 END AS status 
          FROM inv_inventory";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$conn->close();
echo json_encode($data);

?>