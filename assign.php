<?php



// Include database connection
require_once 'connect.php'; // Replace with your actual connection file
require_once 'repair_history_helpers.php';
require_once 'calendar_event_helpers.php';
require_once 'srf_request_notification_helpers.php';

calendarEnsureEventSchema($conn);
calendarEnsureSrfZoomSchema($conn);

function ensureReturnApprovalSchema(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS inv_returned_equipment (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        original_inventory_id INT(11) NOT NULL,
        employeeName TEXT NOT NULL,
        employee_person_id INT(11) NULL,
        equipmentType TEXT NOT NULL,
        computer_specs TEXT NULL,
        yearAcquired TEXT NOT NULL,
        shelfLife TEXT NOT NULL,
        brand TEXT NOT NULL,
        specifications LONGTEXT NOT NULL,
        rangeCategory TEXT NOT NULL,
        softwareInstalled TEXT NOT NULL,
        licensingModel TEXT NOT NULL,
        softwareInstalled_2 TEXT NOT NULL,
        licensingModel_2 TEXT NOT NULL,
        serialNumber LONGTEXT NOT NULL,
        propertyNumber TEXT NOT NULL,
        accountablePerson TEXT NOT NULL,
        accountable_person_id INT(11) NULL,
        sex TEXT NOT NULL,
        officeDivision TEXT NOT NULL,
        statusOfEmployment TEXT NOT NULL,
        actualUser TEXT NOT NULL,
        actual_user_id INT(11) NULL,
        actualUserSex TEXT NOT NULL,
        actualUserStatusOfEmployment TEXT NOT NULL,
        natureOfWork TEXT NOT NULL,
        remarks LONGTEXT NOT NULL,
        office TEXT NOT NULL,
        office_id INT(11) NULL,
        amount INT(11) NOT NULL DEFAULT 0,
        depreciation_value INT(11) NOT NULL DEFAULT 0,
        mark_as_done TEXT NOT NULL,
        inventory_created_at TIMESTAMP NULL,
        inventory_updated_at TIMESTAMP NULL,
        return_status ENUM('Returned','Restored') NOT NULL DEFAULT 'Returned',
        return_reason TEXT NULL,
        returned_by VARCHAR(255) NULL,
        returned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        restored_by VARCHAR(255) NULL,
        restored_at TIMESTAMP NULL,
        restore_inventory_id INT(11) NULL,
        INDEX idx_original_inventory_id (original_inventory_id),
        INDEX idx_return_status (return_status),
        INDEX idx_returned_at (returned_at),
        INDEX idx_restore_inventory_id (restore_inventory_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS inv_return_approvers (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(255) NULL,
        office VARCHAR(255) NULL,
        station VARCHAR(255) NULL,
        is_default TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_return_approver_user_id (user_id),
        INDEX idx_return_approver_active (is_active),
        INDEX idx_return_approver_default (is_default)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->query("CREATE TABLE IF NOT EXISTS inv_return_requests (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        inventory_id INT(11) NOT NULL,
        requested_by_id INT(11) NULL,
        requested_by_name VARCHAR(255) NULL,
        assigned_to_id INT(11) NOT NULL,
        assigned_to_name VARCHAR(255) NOT NULL,
        return_reason TEXT NULL,
        status ENUM('Pending','Approved','Disapproved','Cancelled') NOT NULL DEFAULT 'Pending',
        reviewed_by_id INT(11) NULL,
        reviewed_by_name VARCHAR(255) NULL,
        reviewed_at TIMESTAMP NULL,
        review_remarks TEXT NULL,
        returned_equipment_id INT(11) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_return_request_inventory (inventory_id),
        INDEX idx_return_request_assigned (assigned_to_id),
        INDEX idx_return_request_status (status),
        INDEX idx_return_request_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $conn->query("INSERT INTO inv_return_approvers (user_id, full_name, username, office, station, is_default, is_active)
        VALUES (1373, 'Rodelo L. Tanudtanud', 'rodelo87tanud', 'REGIONAL OFFICE', 'RO ASD', 1, 1)
        ON DUPLICATE KEY UPDATE
            full_name = VALUES(full_name),
            username = VALUES(username),
            office = VALUES(office),
            station = VALUES(station),
            is_default = 1,
            is_active = 1");
}

ensureReturnApprovalSchema($conn);


// Check if the form was submitted and 'assign' parameter exists
if (isset($_REQUEST['assign'])) {

    $request = array_merge($_GET, $_POST);
    $srfId = intval($request['assign']);
    $trackid = $srfId;

    $srfStmt = $conn->prepare("SELECT id, ticketNumber, date, name, divSecUnit, office, requestType, otherSpecify, description, zoom_title, zoom_schedule_datetime, email, equipment_id FROM srf WHERE id = ?");
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
    $isMarkAsDone = isset($request['mark_as_done']) && (string) $request['mark_as_done'] === '1';
    $tracking = $isMarkAsDone ? 102 : intval($request['personelid'] ?? 0);
    $completionResult = trim((string)($request['completion_result'] ?? ''));
    $returnReason = trim((string)($request['return_reason'] ?? ''));
    $returnApprovalMessage = '';
    $NID = 101;

    if ($tracking == 102 && !in_array($completionResult, ['Resolved', 'Unserviceable', 'Needs Parts Replacement'], true)) {
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Please%20select%20a%20completion%20result%20before%20marking%20done.&toast_type=error";</script>';
        exit();
    }

    if ($tracking == 102 && $completionResult === 'Unserviceable' && $returnReason === '') {
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Return%20reason%20is%20required%20for%20unserviceable%20equipment.&toast_type=error";</script>';
        exit();
    }

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

        if (strcasecmp($requestType, 'Zoom') === 0) {
            $zoomMeetingId = trim((string)($request['zoom_meeting_id'] ?? ''));
            $zoomPassword = trim((string)($request['zoom_password'] ?? ''));

            if ($zoomMeetingId === '' || $zoomPassword === '') {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Zoom%20Meeting%20ID%20and%20password%20are%20required%20before%20marking%20done.&toast_type=error";</script>';
                exit();
            }

            $zoomScheduleDateTime = calendarNormalizeZoomDateTime($request['zoom_schedule_datetime'] ?? ($srfRow['zoom_schedule_datetime'] ?? ''));

            if ($zoomScheduleDateTime === '') {
                $zoomScheduleDateTime = calendarNormalizeZoomDateTime(calendarExtractZoomField($srfRow['description'] ?? '', 'Date & Time'));
            }

            if ($zoomScheduleDateTime === '') {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Original%20Zoom%20schedule%20datetime%20is%20missing.&toast_type=error";</script>';
                exit();
            }

            $zoomTitle = trim((string)($request['zoom_title'] ?? ($srfRow['zoom_title'] ?? '')));
            if ($zoomTitle === '') {
                $zoomTitle = calendarExtractZoomField($srfRow['description'] ?? '', 'Meeting Title');
            }
            if ($zoomTitle === '') {
                $zoomTitle = $ticketNumber;
            }

            $calendarSaved = calendarUpsertEventFromSrf($conn, [
                'source_srf_id' => $srfId,
                'event_datetime' => $zoomScheduleDateTime,
                'remarks' => $zoomTitle,
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

        if ($completionResult === 'Unserviceable') {
            $inventoryId = (int) $equipment_id;
            if ($inventoryId <= 0) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=No%20linked%20equipment%20found%20for%20return%20approval.&toast_type=error";</script>';
                exit();
            }

            $inventoryStmt = $conn->prepare('SELECT id FROM inv_inventory WHERE id = ?');
            $inventoryStmt->bind_param('i', $inventoryId);
            $inventoryStmt->execute();
            $inventoryExists = $inventoryStmt->get_result()->num_rows > 0;
            $inventoryStmt->close();

            if (!$inventoryExists) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Linked%20equipment%20was%20not%20found%20in%20active%20inventory.&toast_type=error";</script>';
                exit();
            }

            $pendingStmt = $conn->prepare("SELECT id FROM inv_return_requests WHERE inventory_id = ? AND status = 'Pending' LIMIT 1");
            $pendingStmt->bind_param('i', $inventoryId);
            $pendingStmt->execute();
            $hasPendingReturn = $pendingStmt->get_result()->num_rows > 0;
            $pendingStmt->close();

            if ($hasPendingReturn) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=This%20equipment%20already%20has%20a%20pending%20return%20request.&toast_type=error";</script>';
                exit();
            }

            $approverStmt = $conn->prepare("SELECT user_id, full_name FROM inv_return_approvers WHERE is_active = 1 ORDER BY is_default DESC, id ASC LIMIT 1");
            $approverStmt->execute();
            $approver = $approverStmt->get_result()->fetch_assoc();
            $approverStmt->close();

            if (!$approver) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=No%20active%20return%20approver%20is%20configured.&toast_type=error";</script>';
                exit();
            }

            $requestedById = isset($_SESSION['idSRF']) ? (int) $_SESSION['idSRF'] : null;
            $requestedByName = $_SESSION['Full_NameSRF'] ?? ($_SESSION['usernameSRF'] ?? 'System');
            $assignedToId = (int) $approver['user_id'];
            $assignedToName = $approver['full_name'];
            $requestStmt = $conn->prepare('INSERT INTO inv_return_requests (inventory_id, requested_by_id, requested_by_name, assigned_to_id, assigned_to_name, return_reason, status) VALUES (?, ?, ?, ?, ?, ?, "Pending")');
            $requestStmt->bind_param('iisiss', $inventoryId, $requestedById, $requestedByName, $assignedToId, $assignedToName, $returnReason);

            if (!$requestStmt->execute()) {
                echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=Unable%20to%20create%20return%20approval%20request.&toast_type=error";</script>';
                exit();
            }

            $requestStmt->close();
            $returnApprovalMessage = ' Equipment marked unserviceable and submitted to ' . $assignedToName . ' for return approval.';
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

        if ($completionResult === 'Unserviceable') {
            $returnDetails = 'Return Approval';
            $returnStatus = 'Marked Unserviceable: return approval request submitted.';
            $returnHistoryStmt = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($returnHistoryStmt) {
                $returnHistoryStmt->bind_param("isssssis", $trackid, $name_s, $returnDetails, $date, $time, $returnStatus, $equipment_id, $office);
                $returnHistoryStmt->execute();
                $returnHistoryStmt->close();
            }
        }

        if ($completionResult === 'Needs Parts Replacement') {
            $partsDetails = 'Completion Result';
            $partsStatus = 'Needs Parts Replacement';
            $partsHistoryStmt = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($partsHistoryStmt) {
                $partsHistoryStmt->bind_param("isssssis", $trackid, $name_s, $partsDetails, $date, $time, $partsStatus, $equipment_id, $office);
                $partsHistoryStmt->execute();
                $partsHistoryStmt->close();
            }
        }


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

            if ($tracking > 0 && $tracking !== 102) {
                triggerSrfRequestNotification($conn, (int)$tracking, $srfId, $status);
            }
        
            // Success: redirect to the request list page with a success message
            $successMessage = rawurlencode('Record Successfully Assigned.' . $returnApprovalMessage);
            echo '<script>window.location.href = "mainmenu.php?dir=requestlist&toast_msg=' . $successMessage . '&toast_type=success";</script>';
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
