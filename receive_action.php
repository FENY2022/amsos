<?php
// Include the database connection
require_once 'connect.php'; // Adjust the path if necessary
require_once 'srf_request_notification_helpers.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted SRF ID
    $srfId = $_POST['srfId'];

    // Sanitize input
    $srfId = mysqli_real_escape_string($conn, $srfId);

    // Update the `srf` table
    $sql = "UPDATE srf SET status = 'Now Serving' WHERE id = '$srfId'";
    if (mysqli_query($conn, $sql)) {


        // Assign values to variables (make sure these are coming from POST or SESSION as appropriate)
            $srfId      = $_POST['srfId'];
            $name       =  $_SESSION['usernameSRF'];
            $details    = 'Received by RICTU Staff:' . $_SESSION['usernameSRF'];
            $date       = date("F j, Y");
            $time       = date("h:i A");   
            $status     = 'Now Serving';
            $equipment_id = isset($_POST['equipment_id']) ? $_POST['equipment_id'] : '';
            $office     = $_SESSION['OfficeSRF'];

            // Prepare and execute the insert statement for srfhistory
            $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmth->bind_param("isssssis", $srfId, $name, $details, $date, $time, $status, $equipment_id, $office);
            $stmth->execute();
            $stmth->close();

            triggerSrfWaitingListUpdate($conn, (int)$srfId, 'received');


        // Redirect with toast
        echo "<script>
            window.location.href = 'mainmenu.php?dir=requestlist&toast_msg=Successfully%20received!&toast_type=success';
        </script>";
    } else {
        // Handle error
        $errorMsg = urlencode(mysqli_error($conn));
        echo "<script>
            window.location.href = 'mainmenu.php?dir=requestlist&toast_msg=Error%3A%20" . $errorMsg . "&toast_type=error';
        </script>";
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    // Redirect back if accessed directly
    header('Location: previous_page.php'); // Replace 'previous_page.php' with the actual page
    exit();
}




