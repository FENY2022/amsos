<?php
// Start the session if needed
session_start();

// Include your database connection file
include 'connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the form data
    $id = $_POST['id'];
    $action = 1;

    // Validate the input data
    if (empty($id) || !is_numeric($id)) {
        die('Invalid ID');
    }

    // Sanitize the input data
    $id = intval($id);
    $action = intval($action);

    // Update the notification status in the database
    $query = "UPDATE inv_notification SET action = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $action, $id);

    if ($stmt->execute()) {
        // Successfully updated the status
        echo '<script type="text/javascript">';
        echo 'alert("Notification acknowledged successfully.");';
        echo 'window.location.href = "mainmenu.php?dir=maintenance_report";'; // Replace with your desired URL
        echo '</script>';
        

    } else {
        // Error occurred
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    // If the request method is not POST, redirect or show an error
    echo "Invalid request method.";
}

// Close the database connection
$conn->close();
?>
