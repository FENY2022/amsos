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
            echo "<script>alert('Description updated successfully!'); window.location.href='mainmenu.php?dir=requestlist';</script>";
        } else {
            echo "<script>alert('Error updating description!'); window.location.href='mainmenu.php?dir=requestlist';</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Description cannot be empty!'); window.location.href='mainmenu.php?dir=requestlist';</script>";
    }

    $conn->close();
} else {
    echo "<script>alert('Invalid request!'); window.location.href='mainmenu.php?dir=requestlist';</script>";
}
?>
