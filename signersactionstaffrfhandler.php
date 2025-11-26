<?php
// Start the session at the very beginning of the file
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'connect.php'; // Adjust the path as needed

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $personelid = $_POST['personelid'];
    $name = $_POST['name'];
    $office = $_POST['office'];
    $role = $_POST['role'];

    // Validate and sanitize input
    $personelid = htmlspecialchars(trim($personelid));
    $name = htmlspecialchars(trim($name));
    $level = ""; // Assuming 'level' is intentionally empty based on your original code
    $office = htmlspecialchars(trim($office));
    $stationid = ""; // Assuming 'stationid' is intentionally empty
    $role = htmlspecialchars(trim($role));
    $station = ""; // Assuming 'station' is intentionally empty

    // Initialize variables for toast message and type
    $toastMessage = '';
    $toastType = 'danger'; // Default to danger for errors

    // Basic validation (add more as needed)
    if (empty($personelid) || empty($name) || empty($office) || empty($role)) {
        $toastMessage = 'All fields are required. Please fill in all information.';
        $toastType = 'warning'; // Use warning for input validation
    } else {
        // Prepare SQL statement for inserting a new record
        $sql = "INSERT INTO srfactionstaff (personelid, name, level, Office, Station, role, stationid) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Check if bind_param has the correct number and types of arguments
            // 'ssssssi' implies personelid, name, level, Office, Station, role are strings, and stationid is an integer.
            // Ensure this matches your actual database schema and variable types.
            $stmt->bind_param("ssssssi", $personelid, $name, $level, $office, $station, $role, $stationid);

            if ($stmt->execute()) {
                $toastMessage = "Record inserted successfully for " . htmlspecialchars($name);
                $toastType = 'success'; // Success toast
            } else {
                // Log the error for debugging
                error_log("Error inserting record: " . $stmt->error);
                $toastMessage = "Error inserting record: " . $stmt->error;
                $toastType = 'danger'; // Error toast
            }

            $stmt->close();
        } else {
            // Log the error for debugging
            error_log("Error preparing statement: " . $conn->error);
            $toastMessage = "Error preparing the database query: " . $conn->error;
            $toastType = 'danger'; // Error toast
        }
    }

    // Close connection
    $conn->close();

    // Set session variables for the toast before redirecting
    $_SESSION['toast_message'] = $toastMessage;
    $_SESSION['toast_type'] = $toastType;

    // Redirect back to the main menu page
    header('Location: mainmenu.php?dir=assignactionstaff');
    exit(); // Always exit after a header redirect
} else {
    // Invalid request method, set toast and redirect
    session_start(); // Ensure session is started even if method is not POST
    $_SESSION['toast_message'] = 'Invalid request method. Please submit the form correctly.';
    $_SESSION['toast_type'] = 'danger';
    header('Location: mainmenu.php?dir=assignactionstaff');
    exit();
}
?>