<?php


            require_once 'connect_amsos.php' ;


           $id  = $_GET['id'];

            $sql = "SELECT * FROM srf WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    $srfId = $row['id']; // Assuming 'id' is used here; adjust if needed
                    $ticketNumber = $row['ticketNumber'];
                    $date = $row['date'];
                    $name = $row['name'];
                    $idname = $row['idname'];
                    $divSecUnit = $row['divSecUnit'];
                    $office = $row['office'];
                    $position = $row['position'];
                    $contactNumber = $row['contactNumber'];
                    $email = $row['email'];
                    $requestType = $row['requestType'];
                    $otherSpecify = $row['otherSpecify'];
                    $description = $row['description'];
                    $tracking = $row['tracking'];
                    $status = $row['status'];
                    $level = $row['level'];
                    $remarks = $row['remarks'];
                    $station = $row['station'];
                    $otherSpecify = $row['otherSpecify'];
    

                }

            }


$srf_id = $_GET['id'];

$query = "SELECT feedback, date_rated FROM srffeedback WHERE srf_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $srf_id);
$stmt->execute();
$result_1 = $stmt->get_result();


$selectedFeedback = '';

$selectedFeedback = 'No feedback available';
$formattedDate = 'No date available';




    if ($result_1 && $result_1->num_rows > 0) {
        $row = $result_1->fetch_assoc();
        $selectedFeedback = !empty($row['feedback']) ? $row['feedback'] : 'No feedback provided';
        $date_rated = $row['date_rated'];
        $formattedDate = !empty($date_rated) ? (new DateTime($date_rated))->format('F j, Y : g:i a') : 'No date available';
    
    }

$level = 1 ;

$query = "SELECT office, station, position, name, level, signature FROM srfsigner WHERE office = ? AND station = ? AND level = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);

if ($stmt) {

    $stmt->bind_param("ssi", $office, $station, $level); // Assuming $office and $station are strings

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
         $selectedOffice  = $row['office'];
         $selectedStation = $row['station'];
         $selectedName = $row['name'];
         $selectedPosition = $row['position'];
         $signature1 = $row['signature'];
    }

    $stmt->close(); 
}


$level = 2 ;

$query = "SELECT office, station, position, name, level, signature FROM srfsigner WHERE office = ? AND station = ? AND level = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);

if ($stmt) {

    $stmt->bind_param("ssi", $office, $station, $level); // Assuming $office and $station are strings

    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
         $selectedOffice2  = $row['office'];
         $selectedStation2 = $row['station'];
         $selectedName2 = $row['name'];
         $selectedPosition2 = $row['position'];
         $signature2 = $row['signature'];
     
    }

    $stmt->close(); 
}



$user_id = isset($idname) ? (int)$idname : 0;

$query = "SELECT * FROM useremployee WHERE id = ?";
$stmt_user = $conn->prepare($query);
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_45 = $stmt_user->get_result();
} else {
    $result_45 = false;
}

if ($result_45) {
    // Check if a row was found
    if ($result_45->num_rows > 0) {
        // Fetch the data for the user
        $row = $result_45->fetch_assoc();
        $id = $row["id"];
         $Full_Name = $row["Full_Name"];
         $Signature_dir = $row["Signature_dir"];
         $Employment_Status = $row['Employment_Status'];
        // Process the data as needed

        
    } else {
        echo "No user found with ID: $user_id.";
    }
} else {
    echo "Query failed: " . $conn->error;
}


$recieve = "";
$trackid = $_GET['id'];

// Fetch the first two rows from the 'srfhistory' table
$sql = "SELECT date, time FROM srfhistory WHERE trackid = ? LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $trackid);
$stmt->execute();
$history_result = $stmt->get_result();


if ($history_result->num_rows >= 2) {
    $row1 = $history_result->fetch_assoc();
    $row2 = $history_result->fetch_assoc();

    $First_date = date("F j, Y", strtotime($row1['date']));
    $First_time = !empty($row1['time']) ? date('g:i A', strtotime($row1['time'])) : '';

    $Second_date = date("F j, Y", strtotime($row2['date']));
    $Second_time = !empty($row2['time']) ? date('g:i A', strtotime($row2['time'])) : '';
    $recieve = "recieve.png";
} elseif ($history_result->num_rows == 1) {
    $row1 = $history_result->fetch_assoc();

    $First_date = date("F j, Y", strtotime($row1['date']));
    $First_time = !empty($row1['time']) ? date('g:i A', strtotime($row1['time'])) : '';
    $recieve = "recieve.png";
}


$trackid = $_GET['id']; // Get the 'id' from the URL

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT step_counter FROM srf WHERE id = ?"); // Ensure 'id' is the correct column name
if (!$stmt) {
    die("Error in preparing statement: " . $conn->error); // Handle errors in preparing the statement
}

$stmt->bind_param("i", $trackid); // Bind the $trackid variable to the placeholder
$stmt->execute(); // Execute the query
$result_step_counter = $stmt->get_result(); // Get the result set

if ($result_step_counter) {
    $row = $result_step_counter->fetch_assoc(); // Fetch the row
    if ($row) {
        $count = $row['step_counter']; // Access the 'step_counter' value


        // Your existing logic for handling $count
        if ($count == 0) {
            $signature1 = "";
            $First_time = "";
            $First_date = "";
            $signature2 = "";
            $Second_time = "";
            $Second_date = "";
            $Signature_dir = "";
            $date_rated = "";
            $recieve = "";
        } elseif ($count == 1) {
            $First_time = "";
            $signature2 = "";
            $Second_time = "";
            $Second_date = "";
            $Signature_dir = "";
            $recieve = "";
        } elseif ($count == 2) {



        } elseif ($count == 3) {
            $date_rated = "";
            $Signature_dir = "";
        } elseif ($count > 3) {
            $checkbox = 1;
        }
    } else {
        echo "No data found for the given ID."; 
    }
} else {
    echo "Error: " . $conn->error; 
}

$stmt->close(); 

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, orientation=landscape">
    <title>SRF <?php echo $ticketNumber ; ?></title>
    <link rel="icon" type="image/x-icon" href="icon/amsos.ico">
    <link rel="stylesheet" type="text/css" href="srf.css" />
    
</head>
<body>
    <div id = 'srf'>
        <div id = 'header' style="width:8.3in;">
            <div>
            <table>
                <tr>
                    <td rowspan="3" align='center' class='cell-logo'>
                        <img src='./logopng.png' width="50px" height="50px"/>
                    </td>
                    <td align='center' rowspan="2">
                        <span><strong>PLANNING AND MANAGEMENT DIVISION</strong></span><br/>
                        <span><em>Regional Information and Communication Technology Unit (RICTU)</em></span>
                    </td>
                    <td>
                        Document ID #
                    </td>
                    <td>
                        R13-PMD.FO.01
                    </td>
                </tr>
                <tr>
                    <td>
                        Revision No.
                    </td>
                    <td>
                        2
                    </td>
                </tr>
                <tr>
                    <td align='center'>
                        <strong>SERVICE REQUEST FORM (SRF)</strong>
                    </td>
                    <td>
                        Effectivity
                    </td>
                    <td>
                        8/25/2022
                    </td>
                </tr>
            </table>
            </div>
            <div class='div-margin'>
                <p><strong>Reminder:</strong> : Please complete this form and submit it at the <u>RICT Unit Service Desk</u> located on the ground floor LPDD/ TS Building, Planning and Management Division or email a scanned copy to <a>caraga.ict@denr.gov.ph.</a> Once processed, a Technical Support Representative will contact you to schedule service.</p>
            </div>
            <div class='div-3 div-margin'>
            <p><strong>Ticket No: <u><?php echo $ticketNumber; ?></u></strong></p>
            <p><strong><p>Date(mm/dd/yyyy): <u><?php echo date("m/d/Y", strtotime($date)); ?></u></p></strong></p>
            </div>
            <div>
                <table>
                    <tr class='table-header-bg'>
                        <td colspan="2">
                            <strong>Requester's Information</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Name: <strong><?php echo $name; ?></strong> 
                        </td>

                        <td>
                            Position: <strong><?php echo $position ;?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Division: <strong><?php echo $divSecUnit ;?></strong>
                        </td>
                        <td>
                            Section: 
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Phone: <strong><?php echo $contactNumber ;?></strong>
                        </td>
                        <td >
                            Email Address: <strong><?php echo $email ;?></strong>
                        </td>
                    </tr>

                        <?php

                            $requestTypes = [
                                'Technical Assistance' => 'Technical Assistance',
                                'Asset/Borrow' => 'Asset/Borrow',
                                'Email' => 'E-mail',
                                'In House Software' => 'In House Software',
                                'Otos Web+' => 'Otos Web+',
                                'E-Dats' => 'E-Dats',
                                'Other' => 'Other'
                            ];
                            
                        ?>

                <tr class='table-header-bg'>
                    <td colspan="2">
                        <strong>Request Information</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class='div-flex'>
                            <strong>Type of request:</strong>
                        </div>
                        <div class='div-flex'>
                            <?php foreach ($requestTypes as $value => $label): ?>
                                <div>
                                    <input type='checkbox' 
                                        id='<?php echo strtolower(str_replace(' ', '', $value)); ?>' 
                                        name='requestType[]' 
                                        value='<?php echo $value; ?>' 
                                        <?php echo ($requestType == $value) ? 'checked' : ''; ?> />
                                    <?php echo $label; ?>
                                </div>
                            <?php endforeach; ?>
                            <div>
                                <input type='checkbox' id='others'/>
                                Others (specify): <input type='text' name='otherRequestType' value="<?php echo !empty($otherSpecify) ? $otherSpecify : ""; ?>" />
                            </div>
                        </div>
                    </td>
                </tr>

                    <tr class='table-header-bg'>
                        <td colspan="2">
                            <strong>DESCRIPTION OF REQUEST</strong> <em>(Please clearly write down the details of the request.)</em>
                        </td>
                    </tr>

                    <tr>
    <td colspan="2" style="
        position: relative; /* Needed for absolute positioning of the text */
        height: 150px; 
        text-align: left; 
        vertical-align: top; 
        padding-left: 10px; /* Space from the left edge for text */
        background: url('<?php echo $recieve; ?>') no-repeat right center; /* Positioned to the right */
        background-size: 240px 130px; /* Adjust the size of the image */
        background-color: #f4f4f4; /* Optional: Fallback background color */
        border: 1px solid #ddd; /* Optional: Border for structure */">
        
        <!-- Date and Time String -->
        <div style="
            position: absolute;
            top: 50%; 
            right: 10px; /* Align the text near the right edge */
            transform: translateY(-50%); /* Vertically center the text */
            font-size: 18px; 
            font-weight: bold; 
            color: rgba(70, 0, 150, 0.7); /* Semi-transparent black for readability */
            text-align: right; /* Aligns text to the right */
            padding-right: 50px; /* Adds 10px spacing from the right edge */">
           <?php 
                if (!empty($First_time)) { 
                    echo "<center>" . $Second_date . "<br>" . $Second_time; 
                } 
            ?>
        
        
        </div>
        
        <?php echo $description; ?> <!-- Main content description -->
    </td>
</tr>






                    <tr class='table-header-bg'>
                        <td colspan="2">
                            <strong>Authorization</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                        All requests for service must be approved by the appropriate <span style="color:blue;">manager/supervisor (at least division chief, OIC, immediate supervisor or next in rank staff)</span> of the requester. By signing below the manager/supervisor certifies that the service is required.

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Full Name: <?php echo strtoupper($selectedName); ?></strong>
                        </td>
                        <td>
                            <strong>Position Title: <?php echo strtoupper($selectedPosition); ?></strong>
                        </td>
                    </tr>
                <tr>
                    <td colspan="2">
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px 0 15px;">
                            <!-- Signature Section -->
                            <div style="
                                text-align: center; 
                                position: relative; 
                                padding: 20px; 
                                background-color: transparent; 
                                background-image: url('<?php echo $signature1; ?>'); 
                                background-size: 60%; /* Adjusted size for compression */
                                background-repeat: no-repeat; 
                                background-position: 40% center; /* Adjust horizontal position */
                                border: 0px solid #000;
                                width: 60%; /* Adjust width to compress space */
                            ">
                                ____________________________________________<br/>
                                Signature (Manager/Supervisor)
                            </div>

                            <!-- Date Section -->
                            <div style="text-align: center; width: 35%; /* Adjust width to balance layout */">
                                <b><u><?php echo $First_date . " " . $First_time ; ?></u></b><br/>
                                Date (mm - dd - yyyy)
                            </div>
                        </div>
                    </td>
                    </tr>

                    <tr class='table-header-bg'>
                        <td colspan="2">
                            <strong>Infrastructure Service Authorization</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                        All requests for service must be coordinated with and signed by the Chief of RICTU or his/her authorized representative.
                        </td>
                    </tr>
                    <tr>
                    <td>
                      <strong>Full Name: </strong><span style="font-weight: bold; font-size: medium;"><strong><?php echo " " . $selectedName2;  ?></strong></span>
                    </td>

                    <td>
                        Title / Position: <span style="font-weight: bold; font-size: medium;"><?php echo $selectedPosition2 ; ?></span>
                    </td>

                    </tr>
                                   <tr>
                    <td colspan="2">
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px 0 15px;">
                            <!-- Signature Section -->
                            <div style="
                                text-align: center; 
                                position: relative; 
                                padding: 20px; 
                                background-color: transparent; 
                                background-image: url('<?php echo $signature2; ?>'); 
                                background-size: 60%; /* Adjusted size for compression */
                                background-repeat: no-repeat; 
                                background-position: 40% center; /* Adjust horizontal position */
                                border: 0px solid #000;
                                width: 60%; /* Adjust width to compress space */
                            ">
                                ____________________________________________<br/>
                                Signature (Manager/Supervisor)
                            </div>

                            <!-- Date Section -->
                            <div style="text-align: center; width: 35%; /* Adjust width to balance layout */">
                            <b><u><?php echo $Second_date. " " . $Second_time ; ?></u></b><br/>
                                Date (mm - dd - yyyy)
                            </div>
                        </div>

                        

                    
                        
                    </td>
                    </tr>
                    <tr class='table-header-bg'>
                        <td colspan="2">
                            <strong>For RICTU Staff Only </strong>(Use Back of Form or Separate sheet if necessary)
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td align='center'>
                            Date
                        </td>
                        <td align='center'>
                            Time
                        </td>
                        <td align='center'>
                            Action Taken
                        </td>
                        <td align='center'>
                            Action Staff
                        </td>
                        <td align='center'>
                            Signature
                        </td>
                    </tr>
                    <?php
                            $trackid = $_GET['id'];

                            // SQL query to fetch data based on trackid with an INNER JOIN
                            $query = "SELECT 
                                        srf_actiontaken.date, 
                                        srf_actiontaken.time, 
                                        srf_actiontaken.remarks, 
                                        srf_actiontaken.name, 
                                        srfactionstaff.signature 
                                    FROM srf_actiontaken
                                    INNER JOIN srfactionstaff ON srf_actiontaken.name = srfactionstaff.name
                                    WHERE srf_actiontaken.trackid = ?";
                            $stmt = $conn->prepare($query);

                            if ($stmt === false) {
                                die('Prepare failed: ' . htmlspecialchars($conn->error));
                            }

                            $stmt->bind_param("i", $trackid); // Bind trackid as integer
                            $stmt->execute();

                            // Get the result
                            $result = $stmt->get_result();

                            if ($result === false) {
                                die('Execute failed: ' . htmlspecialchars($stmt->error));
                            }

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . date("F j, Y", strtotime($row['date'])) . "</td>
                                            <td>" . htmlspecialchars(!empty($row['time']) ? date('g:i A', strtotime($row['time'])) : '') . "</td>
                                            <td>" . htmlspecialchars($row['remarks']) . "</td>
                                            <td>" . htmlspecialchars($row['name']) . "</td>
                                             <td><img src='srfsigner/" . htmlspecialchars($row['signature']) . "' alt='Signature' style='width: 100px; height: auto;'></td>
                                        </tr>";
                                }

                            } else {
                                // echo "No records found for Track ID: " . htmlspecialchars($trackid);
                            }

                            // Close the statement
                            $stmt->close();
                            ?>


                    <tr>
                        <td colspan="5">
                            <div class='div-flex'>
                                <strong>Feedback Rating:</strong>
                                <div class='div-flex'>
                                    <input type='checkbox' id='excellent' name='rating' value='Excellent' <?php echo ($selectedFeedback == 'Excellent') ? 'checked' : ''; ?> />
                                    <label for='excellent'>Excellent</label>
                                </div>
                                <div class='div-flex'>
                                    <input type='checkbox' id='verySatisfactory' name='rating' value='Very Satisfactory' <?php echo ($selectedFeedback == 'Very Satisfactory') ? 'checked' : ''; ?> />
                                    <label for='verySatisfactory'>Very Satisfactory</label>
                                </div>
                                <div class='div-flex'>
                                    <input type='checkbox' id='satisfactory' name='rating' value='Satisfactory' <?php echo ($selectedFeedback == 'Satisfactory') ? 'checked' : ''; ?> />
                                    <label for='satisfactory'>Satisfactory</label>
                                </div>
                                <div class='div-flex'>
                                    <input type='checkbox' id='belowSatisfactory' name='rating' value='Below Satisfactory' <?php echo ($selectedFeedback == 'Below Satisfactory') ? 'checked' : ''; ?> />
                                    <label for='belowSatisfactory'>Below Satisfactory</label>
                                </div>
                                <div class='div-flex'>
                                    <input type='checkbox' id='poor' name='rating' value='Poor' <?php echo ($selectedFeedback == 'Poor') ? 'checked' : ''; ?> />
                                    <label for='poor'>Poor</label>
                                </div>
                            </div>
                        </td>
                    </tr>

                </table>
                <div class='div-flex'>
                <input type="checkbox" <?php echo !empty($checkbox) && $checkbox == 1 ? 'checked' : ''; ?> />
                <strong>Completed</strong>
                </div>
                    <div>
                        Acknowledged by:
                    </div>
                    <div style="
                                text-align: center; 
                                position: relative; 
                                padding: 20px; 
                                background-color: transparent; 
                                background-image: url('<?php echo !empty($Signature_dir) ? "https://otos.e-dats.info/forms/setEmployee/" . $Signature_dir : "srfsigner/sign.jpg"; ?>');                                 background-size: 60%; /* Adjusted size for compression */
                                background-repeat: no-repeat; 
                                background-position: 40% center; /* Adjust horizontal position */
                                border: 0px solid #000;
                                width: 60%; /* Adjust width to compress space */
                            "><br><br>
                            ______________________________________<br/>
                            Signature over printed name<br/>
                            Date/Time: 
                                                <?php 
                                        if (!empty($date_rated)) {


                                        $formatted_date = date("F j, Y g:i A", strtotime($date_rated));


                                            echo $formatted_date;
                                            
                                            
                                        } else {
                          
                                        }
                                        ?>
                        </div>
                <div>
                    Ref: <strong><em>NIMD Service Request Form 22 March 2021</em></strong>
                </div>
            </div>
        </div>
    </div>

 
    <?php if (!isset($_SESSION['User_RoleSRF']) || $_SESSION['User_RoleSRF'] != "Super_admin"): ?>
        <?php else: ?>
    <div style="text-align: right; padding: 10px;">
        <button type="button" class="btn btn-primary" style="padding: 8px 20px; font-size: 14px; border-radius: 4px; cursor: pointer; background-color: #007bff; color: white; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onclick="window.open('edit-receive.php?id=<?php echo $trackid; ?>', '_blank')" data-toggle="modal" data-target="#editModal">Edit</button>
    </div>    

    <?php endif; ?>
</body>
</html>

