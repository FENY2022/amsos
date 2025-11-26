<?php


// Include the database connection file
require_once 'connect.php'; // Replace with the actual path to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form inputs
    $srfId = intval($_POST['srfId']);
    $message = trim($_POST['message']);
    $sender = $_SESSION['Full_NameSRF'] ?? 'Unknown'; // Use session variable or a default sender name

    // Validate inputs
    if (empty($srfId) || empty($message)) {
        echo "Error: Missing required fields.";
        exit;
    }

    // Get the current date and time
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    // Prepare and execute the SQL query for srf_notification
    $query = $conn->prepare("INSERT INTO srf_notification (srfId, sender, message, date, time) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("issss", $srfId, $sender, $message, $currentDate, $currentTime);

    if ($query->execute()) {
        // Update the `srf` table
        $stmt = $conn->prepare("UPDATE srf SET Notification_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $srfId);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        // Insert into srfhistory
        $name = $sender;
        $status = $message;
        $details = "Chat";
        $date = date("Y-m-d");
        $time = date("h:i:s A");

        // Check if equipment_id exists in GET parameters
        $equipment_id = "";
        // Prepare the SQL statement for srfhistory
        $office = $_SESSION['OfficeSRF'] ?? '';
        $stmth = $conn->prepare("INSERT INTO srfhistory (trackid, name, details, date, time, status, equipment_id, office) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmth->bind_param("issssssss", $srfId, $name, $details, $date, $time, $status, $equipment_id, $office);

        if ($stmth->execute()) {
            echo "History record inserted successfully";
        } else {
            echo "Error inserting history record: " . $stmth->error;
        }

        // Redirect back to the chat page or show a success message
        header("Location: requestlist.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    // If accessed directly, show an error message
    echo "Invalid request method.";
}
?>
