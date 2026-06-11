<?php
require_once 'connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $srfId = $_POST['srf_id'];
    $description = trim($_POST['description']);

    if (!empty($srfId) && !empty($description)) {
        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE srf SET description = ? WHERE id = ?");
        $stmt->bind_param("si", $description, $srfId);
        
        if ($stmt->execute()) {
            echo "<script>window.location.href='mainmenu.php?dir=requestlist&toast_msg=Description%20updated%20successfully!&toast_type=success';</script>";
        } else {
            echo "<script>window.location.href='mainmenu.php?dir=requestlist&toast_msg=Error%20updating%20description!&toast_type=error';</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>window.location.href='mainmenu.php?dir=requestlist&toast_msg=Description%20cannot%20be%20empty!&toast_type=warning';</script>";
    }

    $conn->close();
} else {
    echo "<script>window.location.href='mainmenu.php?dir=requestlist&toast_msg=Invalid%20request!&toast_type=error';</script>";
}
?>
