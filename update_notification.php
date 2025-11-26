<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['srf_id'])) {
    $srfId = intval($_POST['srf_id']);

    // Update query
    $query = "UPDATE srf SET Notification_read = 0 WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $srfId);

    if ($stmt->execute()) {
        // Redirect back to the main menu or show success
        header('Location: mainmenu.php?dir=srfactiontaken');
        exit;
    } else {
        echo "Error updating notification.";
    }
}
?>
