<?php
require_once 'connect.php';

// Check if the delete parameter is present in the URL
if (isset($_GET['delete'])) {
    // Get the record ID to delete
    $id = intval($_GET['delete']);

    // Validate ID
    if ($id > 0) {
        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM srfsigner WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            // Redirect to the page with a success message
            echo '<script>alert("Record deleted successfully."); window.location.href = "viewassigntracking.php";</script>';

        } else {
            // Redirect to the page with an error message
    
            echo '<script>alert("Record Failed to delete record."); window.location.href = "viewassigntracking.php";</script>';
        }

        // Close the statement
        $stmt->close();
    } else {
        // Redirect to the page with an error message for invalid ID

        echo '<script>alert("Invalid ID."); window.location.href = "viewassigntracking.php";</script>';
    }
}

// Close the database connection
$conn->close();
?>
