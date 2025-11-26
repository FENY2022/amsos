<?php



// Include database connection
require_once 'connect.php'; // Replace with your actual connection file


// Check if the form was submitted via GET and 'assign' parameter exists
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['assign'])) {


    
    $srfId = intval($_GET['assign']); // Sanitize input
    $email = intval($_GET['email']); // Sanitize input
    $name = intval($_GET['name']); // Sanitize input
    $requestType = intval($_GET['requestType']); // Sanitize input
    $otherSpecify = intval($_GET['otherSpecify']); // Sanitize input
    $ticketNumber = intval($_GET['ticketNumber']); // Sanitize input
    $trackid = $srfId ;
    $action_taken = $_GET['action_taken'];
    $equipment_id = $_GET['equipment_id'];


    // Sanitize and get personnel ID
    $tracking = intval($_GET['personelid']); // Ensure 'personelid' is numeric
    $NID = 101; // Set default NID

    $stmt = $conn->prepare("UPDATE srf SET step_counter = step_counter + 1 WHERE id = ?");
    $stmt->bind_param("i", $srfId); 

    if ($stmt->execute()) {
        // echo "Record updated successfully";
    } else {
        // echo "Error updating record: " . $stmt->error;
    }

    // Check if the personnel ID is 102 to complete the SRF
    if ($tracking == 102) {

        $status = "Completed";
        $details = "Approved By: " . $_SESSION['Full_NameSRF'];
        $date = date("Y-m-d");
        $time = date("h:i:s A");
        $name = $_SESSION['Full_NameSRF'];


        $email = $_GET['email'];
        $name = $_GET['name'];
        $requestType = $_GET['requestType'];
        $otherSpecify = $_GET['otherSpecify'];
        $ticketNumber = $_GET['ticketNumber'];
        $remarks = $_GET['action_taken'];

        $subject = 'Service Request Completion Notification';
        $yourname = 'ICTAMSOS ' . $ticketNumber;
        
        $message = 'Dear Mr./Ms. ' . $name . ',
        
        We are pleased to inform you that your service request,  ' . $requestType;
        
        if (!empty($otherSpecify)) {
            $message .= ' (' . $otherSpecify . ')';
        }
        

        // $message = 'Your request, with Ticket Number ' . $ticketNumber . ', has been successfully processed. You may now proceed to the RICTU Help Desk for any items or updates related to your request.';
        // $message .= 'Thank you for your cooperation and trust in our services. Should you need further assistance or have additional requests, please feel free to contact us.';
        // $message .= '<p>Best regards,<br>RICTU OTOS/AMSOS Team</p>'; 


        $message = 'Your request, with Ticket Number ' . $ticketNumber . ', has been successfully repaired/completed/processed. You may now proceed to the RICTU Help Desk for any items or updates related to your request. 
        Thank you for your cooperation and trust in our services. Should you need further assistance or have additional requests, please feel free to contact us.<br><br> 
        Best regards,<br><br>RICTU OTOS/AMSOS Team'; 
        
        $message_2 = 'Your request, with Ticket Number ' . $ticketNumber . ', has been successfully repaired/completed/processed. You may now proceed to the RICTU Help Desk for any items or updates related to your request. 
        Thank you for your cooperation and trust in our services. Should you need further assistance or have additional requests, please feel free to contact us.<br><br> 
        Best regards,<br><br>RICTU OTOS/AMSOS Team'; 



        
        
        $sender = $_SESSION['Full_NameSRF'] ?? null; // Validate session variable
        $srfId = isset($_GET['assign']) ? intval($_GET['assign']) : null; // Validate and cast to integer
        $remarks = htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8'); // Sanitize remarks (if needed)
        
        if (!empty($sender) && $srfId && !empty($remarks)) {
            // Prepare the insert statement
            $stmt = $conn->prepare("INSERT INTO srf_notification (sender, message, srfId) VALUES (?, ?, ?)");
            if ($stmt) {
                $message = 1; // Assuming you want to insert the value 1 for the message
                $stmt->bind_param("ssi", $sender, $remarks, $srfId);
                if ($stmt->execute()) {
                    echo "Record inserted successfully.";
                } else {
                    error_log("Insert query failed: " . $stmt->error);
                    echo "An error occurred while inserting the record.";
                }
                $stmt->close();
            } else {
                error_log("Failed to prepare insert statement: " . $conn->error);
                echo "An error occurred while preparing the query.";
            }
        
            // Prepare the update statement
            $stmt = $conn->prepare("UPDATE srf SET Notification_read = 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $srfId);
                if ($stmt->execute()) {
                    echo "Record updated successfully.";
                } else {
                    error_log("Update query failed: " . $stmt->error);
                    echo "An error occurred while updating the record.";
                }
                $stmt->close();
            } else {
                error_log("Failed to prepare update statement: " . $conn->error);
                echo "An error occurred while preparing the update query.";
            }
        } else {
            echo "Invalid input. Please check your data.";
        }
        
        
        

        
                $url = 'https://o-ldpms.denr.gov.ph/sendemail/send.php?send=1&email=' . urlencode($email) . '&Subject=' . urlencode($subject) . '&message=' . urlencode($message_2) . '&yourname=' . urlencode($yourname);
                
                // Initialize cURL
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                // Execute the request
                $response = curl_exec($ch);
                
                // Check for errors
                if (curl_errno($ch)) {
                    echo 'cURL error: ' . curl_error($ch);
                } else {
                    echo 'Response from server: ' . $response;
                }
                
                // Close cURL session
                curl_close($ch);
                



    $name_s = $_SESSION['Full_NameSRF'];
    $equipment_id = $_GET['equipment_id'];
        // Prepare the insert statement for srfhistory table
        $office = $_SESSION['OfficeSRF'];
        $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmth) {
            $stmth->bind_param("isssssis", $trackid, $name_s, $details, $date, $time, $status, $equipment_id, $office);
            $stmth->execute();
            $stmth->close();


        $stmthi = $conn->prepare("INSERT INTO srfstaff_remarks (track_id, date, time, action_taken, actionstaff) VALUES (?, ?, ?, ?, ?)");
        if ($stmthi) {
            $stmthi->bind_param("issss", $trackid, $date, $time, $action_taken, $name);
            $stmthi->execute();
            $stmthi->close();
        }
            

    


        } else {
            // Handle error if the prepared statement fails
            echo '<script>alert("Error preparing statement for history."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
            exit();
        }


        
    } else {


                // INSERT (TAT) 
                        // Query to check if the record exists
                        $sql = "SELECT * FROM srf_notification WHERE srfId = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $srfId); // Bind the parameter as an integer
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Check if the query result is empty
                        if ($result->num_rows == 0) {
                            // Define current date and time
                            $currentDate = date('Y-m-d');
                            $currentTime = date('H:i:s');

                            $sender = "REMINDER:";
                            $message = "The turnaround time (TAT) for your service request will pause until the required equipment (e.g., CPU) is delivered to the 
                                        RICTU Help Desk. TAT will resume upon receipt of the unit, regardless of prior confirmation to bring it.";

                            // Insert the reminder into the srf_notification table
                            $query = $conn->prepare("INSERT INTO srf_notification (srfId, sender, message, date, time) VALUES (?, ?, ?, ?, ?)");
                            $query->bind_param("issss", $srfId, $sender, $message, $currentDate, $currentTime);

                            // Prepare the data for srfhistory table
                            $name = "REMINDER";
                            $status = $message;
                            $details = "Reminder";
                            $date = date("Y-m-d");
                            $time = date("h:i:s A");

                            // Check if equipment_id exists in GET parameters
                            $equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : null;

                            // Prepare the SQL statement for srfhistory
                            $office = $_SESSION['OfficeSRF'];
                            $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmth->bind_param("isssssis", $srfId, $name, $details, $date, $time, $status, $equipment_id, $office);

                            // Execute both insert queries
                            if ($query->execute() && $stmth->execute()) {
                                echo json_encode(['success' => 'Reminder inserted successfully into both tables']);
                            } else {
                                echo json_encode([
                                    'error' => 'Error inserting reminder: ' . $query->error . ' or ' . $stmth->error
                                ]);
                            }

                        
                        } else {
                            echo json_encode(['info' => 'Notification already exists']);
                        }







        
        $name = $_GET["assignedperson_1"];
        $status = "Assigned to RICTU staff " . $name;

        $details = "Received By: " . $_SESSION['Full_NameSRF'];
        $date = date("Y-m-d");
        $time = date("h:i:s A");
        
        // Check if the database connection is valid
        if ($conn) {
            $equipment_id = $_GET['equipment_id'];
            // Prepare the SQL statement
            $office = $_SESSION['OfficeSRF'];
            $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
            if ($stmth) {
                // Bind the parameters
                $stmth->bind_param("isssssis", $srfId, $name, $details, $date, $time, $status, $equipment_id, $office);
        
                // Execute the statement
                if ($stmth->execute()) {
                    echo "Record successfully inserted into srfhistory.";
                } else {
                    echo "Error executing statement: " . $stmth->error;
                }
        
                // Close the statement
                $stmth->close();
            }
        
        }
                    
        $status = "Assigned RICTU staff"; 
    }

    $date = date("Y-m-d");
    $time = date("h:i:s A");
    $name = $_SESSION['Full_NameSRF'];
    $remarks = $_GET['action_taken'];
    $trackid = $_GET['assign'];
    $userId =  $_SESSION['idSRF'];


    $stmthij = $conn->prepare("INSERT INTO srf_actiontaken (trackid, userId, name, remarks, date, time) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmthij) {
        $stmthij->bind_param("isssss", $trackid, $userId, $name, $remarks, $date, $time);
        $stmthij->execute();
        $stmthij->close();
    }


    // Prepare the update statement for srf table
    $stmt = $conn->prepare("UPDATE srf SET tracking = ?, status = ?, level = ?, remarks = ?  WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("isiss", $tracking, $status, $NID, $remarks, $srfId);

        if ($stmt->execute()) {
        
            // Success: redirect to the request list page with a success message
            echo '<script>alert("Record Successfully Assigned."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
            exit();
        } else {
            // Failure: redirect to the request list page with an error message
            echo '<script>alert("Error Approving Record."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
            exit();
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle error if the prepared statement fails
        echo '<script>alert("Error preparing update statement."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
        exit();
    }





}

// Close the database connection
$conn->close();

?>
