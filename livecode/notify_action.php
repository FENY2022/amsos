<?php
// Include the database connection
require_once 'connect.php'; // Update the path as necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $srfId = $_POST['srfId'];
    $remarks = $_POST['remarks'];

    // Validate input
    if (!filter_var($srfId, FILTER_VALIDATE_INT)) {
        echo "<script>
            alert('Invalid SRF ID.');
            window.location.href = 'mainmenu.php?dir=requestlist';
        </script>";
        exit();
    }

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE srf SET Notification_remarks = ?, Notification_read = 1 WHERE id = ?");
    $stmt->bind_param("si", $remarks, $srfId);

    if ($stmt->execute()) {
        echo "<script>
            alert('Notification updated successfully!');
            window.location.href = 'mainmenu.php?dir=requestlist';
        </script>";
    } else {
        error_log("Database Error: " . $stmt->error);
        echo "<script>
            alert('Error: Unable to update notification.');
            window.location.href = 'mainmenu.php?dir=requestlist';
        </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect if accessed directly
    header('Location: mainmenu.php?dir=requestlist');
    exit();
}
?>
