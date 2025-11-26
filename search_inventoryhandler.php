<?php
// Include the database connection
require_once 'connect.php';

// Search query variables
$employeeName = isset($_POST['employeeName']) ? $_POST['employeeName'] : '';
$equipmentType = isset($_POST['equipmentType']) ? $_POST['equipmentType'] : '';
$yearAcquired = isset($_POST['yearAcquired']) ? $_POST['yearAcquired'] : '';
$brand = isset($_POST['brand']) ? $_POST['brand'] : '';
$specifications = isset($_POST['specifications']) ? $_POST['specifications'] : '';
$natureOfWork = isset($_POST['natureOfWork']) ? $_POST['natureOfWork'] : '';

// Building the query
$query = "SELECT * FROM inv_inventory WHERE 1=1";
$params = [];

if (!empty($employeeName)) {
    $query .= " AND employeeName LIKE ?";
    $params[] = "%$employeeName%";
}

if (!empty($equipmentType)) {
    $query .= " AND equipmentType LIKE ?";
    $params[] = "%$equipmentType%";
}

if (!empty($yearAcquired)) {
    $query .= " AND yearAcquired = ?";
    $params[] = $yearAcquired;
}

if (!empty($brand)) {
    $query .= " AND brand LIKE ?";
    $params[] = "%$brand%";
}

if (!empty($specifications)) {
    $query .= " AND specifications LIKE ?";
    $params[] = "%$specifications%";
}

if (!empty($natureOfWork)) {
    $query .= " AND natureOfWork LIKE ?";
    $params[] = "%$natureOfWork%";
}

// Prepare and execute the statement
$stmt = $conn->prepare($query);

// Bind parameters dynamically based on the number of conditions
if (!empty($params)) {
    $types = str_repeat('s', count($params)); // 's' for string data type
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Display the results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $srfId = $row['id'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employeeName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['equipmentType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['yearAcquired']) . "</td>";
        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
        echo "<td>" . htmlspecialchars($row['specifications']) . "</td>";
        echo "<td>" . htmlspecialchars($row['natureOfWork']) . "</td>";
        echo "<td>";
        echo "<div class='dropdown'>
        <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
                Action
            </button>
            <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
                <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#select{$srfId}'>Select</a></li>
            </ul>
        </div>";
        echo "</td>";
        echo "</tr>";





        
        echo "<div class='modal fade' id='select{$srfId}' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form method='POST' action='save_repairdetails.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-warning'>
                        <h5 class='modal-title text-white'>View Details</h5>
                        <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>×</span>
                        </button>
                    </div>
                    <div class='modal-body'>
                    
                        <div class='form-group'>
                                <input type='text' name='id' value='{$row['id']}' class='form-control' required />

                            <label>Name</label>
                                <input type='text' name='employeeName' value='{$row['employeeName']}' class='form-control' required />
                             <label>Equipment Type</label>
                                 <input type='text' name='item_name' value='{$row['equipmentType']}' class='form-control' required />
                            <label>Property Number</label>
                                 <input type='text' name='property_number' value='{$row['propertyNumber']}' class='form-control' required />
                            <label>Date Repaired</label>
                            <input type='date' name='date_repaired'  class='form-control' required />

            
                                    <label for='status'>Select Status:</label>

                                    <select name='status' id='status'>
                                    <option value='Serviceable'>Serviceable</option>
                                    <option value='Unserviceable'>Unserviceable</option>
                                    <option value='Repaired'>Repaired</option>
                                    </select> 

<br><br>
				<button type='type' name='borrower_id' value='{$srfId}'>Ok</button>
                       
 </div>


    
                    </div>
                    <div class='modal-footer'>
                     
                    
                      
                    </div>
                </div>
            </form>
        </div>
    </div>";


    }



    


    
} else {
    echo "<tr><td colspan='6'>No results found</td></tr>";
}

// Close the connection
$stmt->close();
$conn->close();
?>
