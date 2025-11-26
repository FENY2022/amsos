<?php
session_start(); // Ensure the session is started
require_once 'connect.php';

// Get the action value (either 'inventory' or 'submit')
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $srfId = $_POST['srfId'];
    $userId = $_SESSION['idSRF'];  // Get the user from the session
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

    // Handle the inventory action
    if ($action === 'inventory') {
        // Inventory action logic here
        $sql = "UPDATE srfaction SET status = 'Inventory', remarks = ? WHERE trackid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $remarks, $srfId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Inventory status updated successfully.";
        } else {
            echo "Failed to update inventory status.";
        }

        $stmt->close();
    }

    // Handle the submit action (upload files and save remarks)
    if ($action === 'submit') {
        // File upload logic
        $target_dir = "uploads/"; // Ensure this folder exists and has write permissions
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);  // Create directory if it doesn't exist
        }

        $file_path = '';
        if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == 0) {
            $file_name = basename($_FILES["fileToUpload"]["name"]);
            $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
            $uploadOk = 1;

            // Validate file type
            if (!in_array($file_type, $allowed_types)) {
                echo '<script>alert("Sorry, only JPG, JPEG, PNG & PDF files are allowed."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
                $uploadOk = 0;
            }

            // Set the target file path (make sure to sanitize the file name to avoid conflicts)
            $unique_file_name = uniqid() . "_" . $file_name;  // Use a unique ID to prevent file name conflicts
            $target_file = $target_dir . $unique_file_name;

            // Upload the file
            if ($uploadOk && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars($file_name) . " has been uploaded.";
                $file_path = $target_file;  // Save the file path
            } else {
                echo '<script>alert("Sorry, there was an error uploading your file."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
            }
        }

        // Save remarks and file information in the database
        $sql = "INSERT INTO srfaction_details (trackid, user, remarks, file_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $srfId, $userId, $remarks, $file_path);

        if ($stmt->execute()) {
            echo '<script>alert("Documents uploaded successfully."); window.location.href = "mainmenu.php?dir=requestlist";</script>';
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    
        // Update the srf table with remarks
        $sql = "UPDATE srf SET Notification_remarks = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $remarks, $srfId);

        if ($stmt->execute()) {
            echo "Remarks updated successfully.";
        } else {
            echo "Error updating remarks: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>
