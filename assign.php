<?php



// Include database connection
require_once 'connect.php'; // Replace with your actual connection file
require_once 'repair_history_helpers.php';
require_once 'calendar_event_helpers.php';

calendarEnsureEventSchema($conn);


// Check if the form was submitted and 'assign' parameter exists
if (isset($_REQUEST['assign'])) {

    $request = array_merge($_GET, $_POST);
    $srfId = intval($request['assign']);
    $trackid = $srfId;

    $srfStmt = $conn->prepare("SELECT id, ticketNumber, date, name, divSecUnit, office, requestType, otherSpecify, description, email, equipment_id FROM srf WHERE id = ?");
    $srfStmt->bind_param("i", $srfId);
    $srfStmt->execute();
    $srfResult = $srfStmt->get_result();
    $srfRow = $srfResult ? $srfResult->fetch_assoc() : null;
    $srfStmt->close();

    if (!$srfRow) {
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=SRF%20record%20not%20found.&toast_type=error";</script>';
        exit();
    }

    $email = trim((string)($request['email'] ?? $srfRow['email'] ?? ''));
    $name = trim((string)($request['name'] ?? $srfRow['name'] ?? ''));
    $requestType = trim((string)($request['requestType'] ?? $srfRow['requestType'] ?? ''));
    $otherSpecify = trim((string)($request['otherSpecify'] ?? $srfRow['otherSpecify'] ?? ''));
    $ticketNumber = trim((string)($request['ticketNumber'] ?? $srfRow['ticketNumber'] ?? ''));
    $action_taken = trim((string)($request['action_taken'] ?? ''));
    $equipment_id = trim((string)($request['equipment_id'] ?? ($srfRow['equipment_id'] ?? '')));
    $tracking = intval($request['personelid'] ?? 0);
    $NID = 101;

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


        $email = trim((string)($request['email'] ?? $email));
        $name = trim((string)($request['name'] ?? $name));
        $requestType = trim((string)($request['requestType'] ?? $requestType));
        $otherSpecify = trim((string)($request['otherSpecify'] ?? $otherSpecify));
        $ticketNumber = trim((string)($request['ticketNumber'] ?? $ticketNumber));
        $remarks = trim((string)($request['action_taken'] ?? $action_taken));

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

        if ($requestType === 'Zoom') {
            $zoomMeetingId = trim((string)($request['zoom_meeting_id'] ?? ''));
            $zoomPassword = trim((string)($request['zoom_password'] ?? ''));

            if ($zoomMeetingId === '' || $zoomPassword === '') {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Zoom%20Meeting%20ID%20and%20password%20are%20required%20before%20marking%20done.&toast_type=error";</script>';
                exit();
            }

            $calendarSaved = calendarUpsertEventFromSrf($conn, [
                'source_srf_id' => $srfId,
                'event_date' => trim((string)($request['action_date'] ?? $srfRow['date'] ?? date('Y-m-d'))),
                'remarks' => trim((string)($request['zoom_title'] ?? ($srfRow['description'] ?? $ticketNumber))),
                'zoom_link' => trim((string)($request['zoom_link'] ?? '')),
                'meeting_id' => $zoomMeetingId,
                'password' => $zoomPassword,
                'email' => $email,
                'office' => $srfRow['office'] ?? '',
                'divSecUnit' => $srfRow['divSecUnit'] ?? ''
            ]);

            if (!$calendarSaved) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Unable%20to%20save%20Zoom%20event%20to%20calendar.&toast_type=error";</script>';
                exit();
            }
        }




        
        $sender = $_SESSION['Full_NameSRF'] ?? null; // Validate session variable
        $srfId = isset($request['assign']) ? intval($request['assign']) : null; // Validate and cast to integer
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
        $equipment_id = trim((string)($request['equipment_id'] ?? $equipment_id));
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
            echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Error%20preparing%20statement%20for%20history.&toast_type=error";</script>';
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
                            $equipment_id = isset($request['equipment_id']) ? intval($request['equipment_id']) : null;

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







        
        $name = trim((string)($request['assignedperson_1'] ?? ''));
        $status = "Assigned to RICTU staff " . $name;

        $details = "Received By: " . $_SESSION['Full_NameSRF'];
        $date = date("Y-m-d");
        $time = date("h:i:s A");
        
        // Check if the database connection is valid
        if ($conn) {
            $equipment_id = trim((string)($request['equipment_id'] ?? ''));
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
    $remarks = trim((string)($request['action_taken'] ?? $action_taken));
    $trackid = intval($request['assign'] ?? $trackid);
    $userId =  $_SESSION['idSRF'];


    $stmthij = $conn->prepare("INSERT INTO srf_actiontaken (trackid, userId, name, remarks, date, time) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmthij) {
        $stmthij->bind_param("isssss", $trackid, $userId, $name, $remarks, $date, $time);
        $stmthij->execute();
        $stmthij->close();
    }

    repairHistoryUpdateSrfRepairAction($conn, $srfId, $status, $name, $remarks, $date, $time);


    // Prepare the update statement for srf table
    $stmt = $conn->prepare("UPDATE srf SET tracking = ?, status = ?, level = ?, remarks = ?  WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("isiss", $tracking, $status, $NID, $remarks, $srfId);

        if ($stmt->execute()) {
        
            // Success: redirect to the request list page with a success message
            echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Record%20Successfully%20Assigned.&toast_type=success";</script>';
            exit();
        } else {
            // Failure: redirect to the request list page with an error message
            echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Error%20Approving%20Record.&toast_type=error";</script>';
            exit();
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle error if the prepared statement fails
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Error%20preparing%20update%20statement.&toast_type=error";</script>';
        exit();
    }





}

// Close the database connection
$conn->close();

?>
