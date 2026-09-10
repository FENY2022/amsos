


<?php

 require_once 'connect_otos.php';

 $office = $_SESSION['OfficeSRF']; // Assuming session is already started


// Initialize the query with the office filter
$sql = "SELECT id, Full_Name, Office, Station, Position, User_Role FROM useremployee WHERE Office = ?";

// $sql = "
//     SELECT 
//         useremployee.id, 
//         useremployee.Full_Name, 
//         useremployee.Office, 
//         useremployee.Station, 
//         useremployee.Position, 
//         useremployee.User_Role, 
//         signatory_setup.id AS signatory_id, -- Alias to avoid confusion with useremployee.id
//         signatory_setup.Station 
//     FROM 
//         useremployee 
//     INNER JOIN 
//         signatory_setup 
//     ON 
//         useremployee.Station = signatory_setup.Station 
//     WHERE 
//         useremployee.Office = ?";


// Check if there's a search query
if (!empty($_POST['search'])) {
    $search = '%' . $conn_otos->real_escape_string($_POST['search']) . '%';
    $sql .= " AND Full_Name LIKE ?";
}

$stmt = $conn_otos->prepare($sql);

if (!empty($_POST['search'])) {
    $stmt->bind_param("ss", $office, $search);
} else {
    $stmt->bind_param("s", $office);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . strtoupper($row['Full_Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Office']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Station']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Position']) . "</td>";

      echo  "<td>
        <div class='dropdown'>
        <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$row['id']}' data-bs-toggle='dropdown' aria-expanded='false'>
            Action
        </button>
        <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$row['id']}'>
            <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#add{$row['id']}'>Add</a></li>
        </ul>
    </div>
    </td>";





    

    echo "<div class='modal fade' id='add{$row['id']}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog'>
        <form method='POST' action='signersactionstaffrfhandler.php'>
            <div class='modal-content'>
                <div class='modal-header bg-success'>
                    <h5 class='modal-title text-white'>View Details</h5>
                    <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>×</span>
                    </button>
                </div>
                <div class='modal-body'>

                    <div class='form-group'>
                        <label>ID</label>
                        <input type='text' name='personelid' value='{$row['id']}' class='form-control' required />
                    </div>

                    <div class='form-group'>
                        <label>Name</label>
                        <input type='text' name='name' value='{$row['Full_Name']}' class='form-control' required />
                    </div>

                    <div class='form-group'>
                        <label for='office'>Office</label>
                        <select id='office' name='office' onchange='updateStations()'>
                            <option value=''>Select Office</option>";
                        
                        // PHP code to populate Office dropdown
                        $sql = "SELECT DISTINCT Office FROM signatory_setup";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($officeRow = $result->fetch_assoc()) {
                                $selected = ($officeRow['Office'] == $row['Office']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($officeRow['Office']) . "' $selected>" . htmlspecialchars($officeRow['Office']) . "</option>";
                            }
                        }

                        echo "</select>
                    </div>

    

                    <div class='form-group'>
                        <label>Role</label>
                        <input type='text' name='role' value='{$row['User_Role']}' class='form-control' required />
                    </div>

                    <div class='form-group'>
                        <label>Office</label>
                        <input type='text' name='office' value='{$row['Office']}' class='form-control' required />
                    </div>



                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary'>Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>";



        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No results found</td></tr>";
}


?>

<script>
// JavaScript function to update the stations dropdown based on selected office
function updateStations() {
    var office = document.getElementById('office').value;
    var stationDropdown = document.getElementById('station');

    // Clear existing options
    stationDropdown.innerHTML = '<option value="">Select Station</option>';

    if (office) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_stations.php?office=' + encodeURIComponent(office), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var stations = JSON.parse(xhr.responseText);
                var uniqueStations = {};

                stations.forEach(function(station) {
                    if (!uniqueStations[station]) {
                        uniqueStations[station] = true;
                        var option = document.createElement('option');
                        option.value = station;
                        option.textContent = station;
                        stationDropdown.appendChild(option);
                    }
                });
            }
        };
        xhr.send();
    }
}
</script>


<script>
function updateStationTextBox() {
    var stationDropdown = document.getElementById('station');
    var selectedOption = stationDropdown.options[stationDropdown.selectedIndex];
    var stationName = selectedOption.getAttribute('data-station');

    // Update the text box with the selected station name
    document.getElementById('stationTextBox').value = stationName;
}
</script>
