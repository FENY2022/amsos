<?php

session_start(); // Start the session at the very beginning

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php'; // Ensure this path is correct

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // SQL query to delete the record
    $sql = "DELETE FROM srfactionstaff WHERE id = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Success: Set session variables for the toast
            $_SESSION['toast_message'] = "Record deleted successfully!";
            $_SESSION['toast_type'] = "success"; // Use 'success' for green toast
        } else {
            // Error: Set session variables for the toast with error message
            $_SESSION['toast_message'] = "Error deleting record: " . $stmt->error;
            $_SESSION['toast_type'] = "danger"; // Use 'danger' for red toast
            error_log("Error deleting record: " . $stmt->error); // Log the server-side error
        }
        $stmt->close();
    } else {
        // Error preparing the statement
        $_SESSION['toast_message'] = "Error preparing database statement: " . $conn->error;
        $_SESSION['toast_type'] = "danger";
        error_log("Error preparing statement: " . $conn->error); // Log the server-side error
    }
} else {
    // ID not provided
    $_SESSION['toast_message'] = "No record ID provided for deletion.";
    $_SESSION['toast_type'] = "warning"; // Use 'warning' for a yellow toast
}

$conn->close();

// Redirect back to the main menu page
header("Location: mainmenu.php?dir=assignactionstaff");
exit(); // Always exit after a header redirect
?>