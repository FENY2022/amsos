<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your database connection
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $old_name = trim($_POST['equipment_old_name']);
    $new_name = trim($_POST['equipment_new_name']);

    // Check for empty inputs
    if (empty($old_name) || empty($new_name)) {
        $_SESSION['error'] = "Both fields are required.";
        header("Location: mainmenu.php?dir=entrydata");
        exit();
    }

    // Prevent editing if old name and new name are the same
    if ($old_name === $new_name) {
        $_SESSION['warning'] = "No changes made. Equipment name is the same.";
        header("Location: mainmenu.php?dir=entrydata");
        exit();
    }

    // Check if the new name already exists
    $check_sql = "SELECT * FROM inv_typeofequipment WHERE equipment_name = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("s", $new_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['error'] = "Equipment name already exists.";
        header("Location: mainmenu.php?dir=entrydata");
        exit();
    }

    // Update the equipment name
    $update_sql = "UPDATE inv_typeofequipment SET equipment_name = ? WHERE equipment_name = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ss", $new_name, $old_name);

    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Equipment name updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update equipment name.";
    }

    header("Location: mainmenu.php?dir=entrydata");
    exit();
} else {
    // Invalid request
    $_SESSION['error'] = "Invalid request method.";
    header("Location: mainmenu.php?dir=entrydata");
    exit();
}
?>