<?php
require_once 'connect.php';

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $personelId = $_POST['personelid'];
    $level = $_POST['level'];
    $position = $_POST['position']; // Added position

    // Validate input
    if (empty($personelId) || empty($level) || !is_numeric($level) || $level < 1) {
        // Redirect back to the form with an error message
        echo '<script>alert("Error."); window.location.href = "viewassigntracking.php";</script>';
        exit();
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE srfsigner SET level = ?, position = ? WHERE id = ?");
    $stmt->bind_param('isi', $level, $position, $personelId); // Updated to include position

    if ($stmt->execute()) {
        // Redirect back to the form with a success message
        echo '<script>alert("Record successfully updated."); window.location.href = "viewassigntracking.php";</script>';
    } else {
        // Redirect back with an error message
        echo '<script>alert("Record update failed."); window.location.href = "viewassigntracking.php";</script>';
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
