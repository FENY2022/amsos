<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'connect.php'; // Replace with your actual connection file
require_once 'repair_history_helpers.php';
require_once 'srf_request_notification_helpers.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data using POST
    $srfId = $_POST['disapproved'];
    $remarks = $_POST['remarks'];
    $tracking = 0;
    $status = "Disapproved";
    $trackid = intval($_GET['approve']);
    $name = $_SESSION['Full_NameSRF']; // Sanitize input


    // Uncomment these if you need to debug
    // echo $srfId;
    // echo $remarks;
    // echo $tracking;
    // echo $status;

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE srf SET tracking = ?, status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("issi", $tracking, $status, $remarks, $srfId); // Adjust types and parameters as needed

    if ($stmt->execute()) {


        $status = "Disapproved";
        $details = "Disapproved By: " .  $_SESSION['Full_NameSRF'] . "";
        $date = date("Y-m-d");
        $time = date("h:i:s A");
    
        // Prepare the update statement
        $office = $_SESSION['OfficeSRF'];
        $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, office) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmth->bind_param("issssss", $srfId, $name, $details, $date, $time, $status, $office);
        $stmth->execute();
        $stmth->close();

        repairHistoryUpdateSrfRepairAction($conn, $srfId, $status, $name, $remarks, $date, $time);
        triggerSrfWaitingListUpdate($conn, (int)$srfId, 'disapproved');
    
        
        // Success: redirect to the request list page
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist";</script>';
        exit();
    } else {
        // Failure: redirect to the request list page with error status
        echo '<script>window.location.href = "mainmenu.php?dir=requestlist&status=error";</script>';
        exit();
    }

    // Close the statement
    $stmt->close();




}

// Close the database connection
$conn->close();

?>
