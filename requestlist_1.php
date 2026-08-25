<?php
// Database configuration
require_once 'connect.php';
require_once 'session_checker.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch data from the database
$sql = "SELECT * FROM srf";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
    <title>Request List</title>
    <style>
    /* Default table styling */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f1f1f1;
    }

    /* Responsive styling for mobile view */
    @media (max-width: 768px) {
        table, thead, tbody, th, td, tr {
            display: block;
        }

        thead tr {
            display: none;
        }

        tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }

        td {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            text-align: left;
            border: none;
            border-bottom: 1px solid #ddd;
            position: relative;
            padding-left: 50%;
        }

        td::before {
            content: attr(data-label);
            position: absolute;
            left: 10px;
            font-weight: bold;
            white-space: nowrap;
        }
    }
</style>


</head>
<body>
    <h2>Request List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ticket Number</th>
                <th>Date</th>
                <th>Name</th>
                <th>ID Name</th>
                <th>Division/Sec/Unit</th>
                <th>Office</th>
                <th>Position</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Request Type</th>
                <th>Other Specify</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
$idSRF = $_SESSION['idSRF'];
$idSRF101 = 101 ;
$sql = "SELECT * FROM srf WHERE tracking = ? OR tracking = ? ";
$stmt = $conn->prepare($sql);

$currentTime = time();
$formattedTime = date('H:i', $currentTime); // Use the desired time format


// Bind the parameters
$stmt->bind_param("ii", $idSRF, $idSRF101);
$stmt->execute();

// Fetch the result
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $srfId = $row['id']; // Assuming 'id' is used here; adjust if needed
        $email = $row['email'];
        $name = $row['name'];
        $ticketNumber = $row['ticketNumber'];
        $requestType = $row['requestType'];
        $otherSpecify = $row['otherSpecify'];
    
        echo "<tr>
        <td data-label='ID'>{$row['id']}</td>
        <td data-label='Ticket Number'>{$row['ticketNumber']}</td>
        <td data-label='Date'>{$row['date']}</td>
        <td data-label='Name'>{$row['name']}</td>
        <td data-label='ID Name'>{$row['idname']}</td>
        <td data-label='Division/Sec/Unit'>{$row['divSecUnit']}</td>
        <td data-label='Office'>{$row['office']}</td>
        <td data-label='Position'>{$row['position']}</td>
        <td data-label='Contact Number'>{$row['contactNumber']}</td>
        <td data-label='Email'>{$row['email']}</td>
        <td data-label='Request Type'>{$row['requestType']}</td>
        <td data-label='Other Specify'>{$row['otherSpecify']}</td>
        <td data-label='Description'>{$row['description']}</td>
        <td data-label='Status'>{$row['status']}</td>
        <td data-label='Action'>";

if ($row['level'] == "101") {
    echo "<div class='dropdown'>
            <button class='btn btn-success dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
                Action
            </button>
            <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
                <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#assign{$srfId}'>Assign</a></li>
                <li><a class='dropdown-item bg-secondary text-white' href='#' data-bs-toggle='modal' data-bs-target='#read{$srfId}'>Read</a></li>
                <li><a class='dropdown-item bg-info text-white' href='#' data-bs-toggle='modal' data-bs-target='#options{$srfId}'>Options</a></li>
            </ul>
        </div>";


   }elseif ($row['level'] == "2") {
    echo "<div class='dropdown'>
    <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
        Action
        </button>
        <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
            <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#approve{$srfId}'>Recieve</a></li>
            <li><a class='dropdown-item bg-warning text-white' href='#' data-bs-toggle='modal' data-bs-target='#read{$srfId}'>Read Full</a></li>
            <li><a class='dropdown-item bg-danger text-white' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'>Disapproved</a></li>
        </ul>
    </div>";


} else {
    echo "<div class='dropdown'>
            <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton{$srfId}' data-bs-toggle='dropdown' aria-expanded='false'>
                Action
            </button>
            <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton{$srfId}'>
                <li><a class='dropdown-item bg-success text-white' href='#' data-bs-toggle='modal' data-bs-target='#approve{$srfId}'>Approve</a></li>
                <li><a class='dropdown-item bg-warning text-white' href='#' data-bs-toggle='modal' data-bs-target='#read{$srfId}'>Read Full</a></li>
                <li><a class='dropdown-item bg-danger text-white' href='#' data-bs-toggle='modal' data-bs-target='#disapproved{$srfId}'>Disapproved</a></li>
            </ul>
        </div>";
}
echo "</td></tr>";





            //     echo "<div class='modal fade' id='disapproved{$srfId}' tabindex='-1' aria-hidden='true'>
            //     <div class='modal-dialog'>
            //         <div class='modal-content'>
            //             <div class='modal-header bg-danger'>
            //                 <h5 class='modal-title text-white'>System Information</h5>
            //                 <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
            //                     <span aria-hidden='true'>×</span>
            //                 </button>
            //             </div>
            //             <div class='modal-body'>ID: ({$srfId}) Are you sure you want to Disapprove this record?</div>
            //             <div class='modal-footer'>
            //                 <button class='btn btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
            //                 <a class='btn btn-danger' href='disapproved.php?disapproved={$srfId}'>Disapprove</a>
            //             </div>
            //         </div>
            //     </div>
            // </div>";


        //     echo "<div class='modal fade' id='disapproved{$srfId}' tabindex='-1' aria-hidden='true'>
        //     <div class='modal-dialog'>
        //         <div class='modal-content'>
        //             <div class='modal-header bg-danger'>
        //                 <h5 class='modal-title text-white'>System Information</h5>
        //                 <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
        //                     <span aria-hidden='true'>×</span>
        //                 </button>
        //             </div>
        //             <div class='modal-body'>ID: ({$srfId}) Are you sure you want to Disapprove this record?</div>
        //             <div class='modal-footer'>
        //                 <button class='btn btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
        //                 <a class='btn btn-danger' href='disapproved.php?disapproved={$srfId}'>Disapprove</a>
        //             </div>
        //         </div>
        //     </div>
        // </div>";


        echo "<div class='modal fade' id='options{$row['id']}' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header bg-info'>
                    <h5 class='modal-title text-white'>View Details</h5>
                    <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>×</span>
                    </button>
                </div>
                <div class='modal-body'>
                    <form method='POST' action='options.php' enctype='multipart/form-data'>
                        <input type='hidden' name='srfId' value='{$srfId}' />
    
                        <!-- Inventory Button -->
                        <a href='mainmenu.php?dir=search_inventory&id=" . $srfId . "' class='btn btn-success'>Inventory</a>

                        
                        <!-- File Upload Section -->
                        <br><br>
                        <div class='form-group'>
                            <label for='fileToUpload'>Upload Documents or Screenshots</label>
                            <input type='file' name='fileToUpload' class='form-control' id='fileToUpload'>
                        </div>
    
                        <!-- Remarks Text Area -->
                        <div class='form-group'>
                            <label for='remarks'>Remarks</label>
                            <textarea class='form-control' id='remarks' name='remarks' rows='3' required></textarea>
                        </div>


                        <button type='button' class='btn btn-primary btn-block open-equipment' data-id='{$row['equipment_id']}'>
                            Open Equipment
                        </button>

    
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                            <button type='submit' name='action' value='submit' class='btn btn-success'>Ok</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>";
    
  

    


        echo "<div class='modal fade' id='disapproved{$row['id']}' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form method='POST' action='disapproved.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-danger'>
                        <h5 class='modal-title text-white'>View Details</h5>
                        <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>×</span>
                        </button>
                    </div>
                    <div class='modal-body'>
    

                    <div class='modal-body'>ID: ({$srfId}) Are you sure you want to Disapprove this record?</div>

                    <div class='form-group'>
                        <input type='text' name='disapproved' value='{$srfId}' class='form-control' required / hidden>
                    </div>

                        <div class='form-group'>
                            <input type='text' name='level' value='{$row['level']}' class='form-control' required / hidden>
                        </div>

                        <div class='form-group'>
                            <input type='text' name='name' value='{$row['name']}' class='form-control' required / hidden>
                        </div>


                    <div class='form-group'>
                        <label for='remarks'>Remarks</label>
                        <textarea class='form-control' id='remarks' name='remarks' rows='3' required></textarea>
                    </div>

                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                        <button type='submit' class='btn btn-danger'>Disapprove</button>
                    </div>
                </div>
            </form>
        </div>
    </div>";
  


        //     echo "<div class='modal fade' id='approve{$srfId}' tabindex='-1' aria-hidden='true'>
        //     <div class='modal-dialog'>
        //         <div class='modal-content'>
        //             <div class='modal-header bg-success'>
        //                 <h5 class='modal-title text-white'>System Information</h5>
        //                 <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
        //                     <span aria-hidden='true'>×</span>
        //                 </button>
        //             </div>
        //             <div class='modal-body'>ID: ({$srfId}) Are you sure you want to approve this record? </div>
        //             <div class='modal-footer'>
        //                 <button class='btn btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
        //                 <a class='btn btn-success' href='approve.php?approve={$srfId}'>Approve</a>
        //             </div>
        //         </div>
        //     </div>
        // </div>";




        echo "<div class='modal fade' id='approve{$row['id']}' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form method='GET' action='approve.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-success'>
                        <h5 class='modal-title text-white'>View Details</h5>
                        <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>×</span>
                        </button>
                    </div>
                    <div class='modal-body'>
    

                        <div class='modal-body'>ID: ({$srfId}) Are you sure you want to approve this record? </div>

                        <div class='form-group'>
                            <input type='text' name='approve' value='{$srfId}' class='form-control' required / hidden>
                        </div>

                        <div class='form-group'>
                            <input type='text' name='level' value='{$row['level']}' class='form-control' required / hidden>
                        </div>

                        <div class='form-group'>
                            <input type='text' name='name' value='{$row['name']}' class='form-control' required / hidden>
                        </div>

                        
                        <div class='form-group'>
                            <input type='text' name='description' value='{$row['description']}' class='form-control' required / hidden>
                        </div>

                        <div class='form-group'>
                            <input type='text' name='requestType' value='{$row['requestType']}' class='form-control' required / hidden>
                        </div>


                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                        <button type='submit' class='btn btn-primary'>Approve</button>
                    </div>
                </div>
            </form>
        </div>
    </div>";



    echo "<div class='modal fade' id='assign{$row['id']}' tabindex='-1' aria-hidden='true'>
    <div class='modal-dialog modal-dialog-scrollable'>
        <form method='GET' action='assign.php'>
            <div class='modal-content'>
                <div class='modal-header bg-secondary'>
                    <h5 class='modal-title text-white'>Assign Action</h5>
                    <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>×</span>
                    </button>
                </div>

                <div class='modal-body'>

                    <div class='form-group'>
                        <label>ID</label>
                        <input type='text' name='assign' value='{$srfId}' class='form-control' required />
                    </div>

                
                    <div class='form-group'>
                    <label for='office'>Personnel</label>
                    <select id='office' name='personelid' onchange='updateStations()'>
                        <option disabled selection value=''>Select Office</option>";
                    
                    // PHP code to populate Office dropdown
                    $sql = "SELECT DISTINCT personelid, name FROM srfactionstaff";
                        $result2 = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($officeRow = $result2->fetch_assoc()) {
                                $selected = ($officeRow['name'] == $row['name']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($officeRow['personelid']) . "' $selected>" . strtoupper(htmlspecialchars($officeRow['name'])) . "</option>";  
                            }

                            echo "<option value='102'>MARK AS DONE</option>";
                        }


                        
                    echo "</select>
                </div>

                  <input type='text' id='assignedperson_1' name='assignedperson_1' >

                <div class='form-group'>
                        <label for='actionDate'>Date</label>
                        <input type='date' class='form-control' id='actionDate' required>
                    </div>
                    <div class='form-group'>
                        <label for='actionTime'>Time</label>
                        <input type='time' class='form-control' id='actionTime'  value='$formattedTime' required>
                    </div>

            

                    <div class='form-group'>
                        <label for='actionTaken'>Action Taken</label>
                        <textarea type='text' class='form-control' id='actionTaken' name='action_taken' rows='3'></textarea>
                    </div>

                    <div class='form-group'>
                        <input type='email' name='email' value='{$email}' class='form-control' required hidden />
                    </div>

                    <div class='form-group'>
                        <input type='text' name='name' value='{$name}' class='form-control' required hidden />
                    </div>

                    <div class='form-group'>
                        <input type='text' name='ticketNumber' value='{$ticketNumber}' class='form-control' required  hidden/>
                    </div>

                    <div class='form-group'>
    
                        <input type='text' name='requestType' value='{$requestType}' class='form-control' required hidden />
                    </div>

                    <div class='form-group'>
                        <input type='text' name='otherSpecify' value='{$otherSpecify}' class='form-control' hidden  />
                    </div>




                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                    <button type='submit' class='btn btn-primary'>Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>";









        echo "<div class='modal fade' id='read{$srfId}' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog'>
            <form method='POST' action='updateBorrower.php'>
                <div class='modal-content'>
                    <div class='modal-header bg-warning'>
                        <h5 class='modal-title text-white'>View Details</h5>
                        <button class='close' type='button' data-dismiss='modal' aria-label='Close'>
                            <span aria-hidden='true'>×</span>
                        </button>
                    </div>
                    <div class='modal-body'>
                        <div class='form-group'>
                            <label>Name</label>
                            <input type='text' name='firstname' value='{$row['name']}' class='form-control' required />
                            <input type='hidden' name='borrower_id' value='{$srfId}'/>
                        </div>

                        <div class='form-group'>
                            <label>Request Type</label>
                            <input type='text' name='firstname' value='{$row['requestType']}' class='form-control' required />
                        </div>

                        
                        <div class='form-group'>
                            <label>Other</label>
                            <input type='text' name='firstname' value='{$row['otherSpecify']}' class='form-control' required />
                        </div>
                        
                        <div class='form-group'>
                            <label>Contact Number</label>
                            <input type='text' name='firstname' value='{$row['contactNumber']}' class='form-control' required />
                        </div>

                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name='firstname' value='{$row['description']}' class='form-control' required />{$row['description']}</textarea>
                        </div>

                        <div class='form-group'>
                            <label>Remarks</label>
                            <textarea name='firstname' value='{$row['remarks']}' class='form-control' required />{$row['remarks']}</textarea>
                        </div>

                      

                        <button type='button' class='btn btn-primary btn-block open-equipment' data-id='{$row['equipment_id']}'>
                            Open Equipment
                        </button>



                    </div>
                    <div class='modal-footer'>
                     
                      
                    </div>
                </div>
            </form>
        </div>
    </div>";




    }
} else {
    echo "<tr><td colspan='13'>No records found</td></tr>";
}

// Close the statement
$stmt->close();

            ?>
        </tbody>
    </table>
</body>
</html>


<script>
    // Wait until the document is fully loaded
    document.addEventListener("DOMContentLoaded", function () {
        // Attach event listener to buttons with class 'open-equipment'
        const buttons = document.querySelectorAll(".open-equipment");
        buttons.forEach(button => {
            button.addEventListener("click", function () {
                // Get the equipment_id from the data attribute
                const equipmentId = this.getAttribute("data-id");

                // Redirect to another page with equipment_id in the URL
                if (equipmentId) {
                    window.location.href = `mainmenu.php?dir=equipment_page&equipment_id=${equipmentId}`;
                }
            });
        });
    });
</script>

<script>
    function updateStations() {
    const dropdown = document.getElementById('office');
    const selectedOption = dropdown.options[dropdown.selectedIndex];
    const name = selectedOption.getAttribute('data-name'); // Get the name from the selected option
    document.getElementById('assignedperson_1').value = name || ''; // Set the value in the textbox
}

    </script>
