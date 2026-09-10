<?php
require_once 'connect.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $srfId = $_POST['srfId'];
    $newUsername = $_POST['username'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Fetch the current user record
    $stmt = $conn->prepare("SELECT username, password FROM useremployee WHERE id = ?");
    $stmt->bind_param("i", $srfId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $currentHashedPassword = $user['password'];

        // Verify the current password
        if (!password_verify($currentPassword, $currentHashedPassword)) {
            echo "Current password is incorrect.";
            exit;
        }

        // Validate new password if provided
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                echo "New passwords do not match.";
                exit;
            }

            // Hash the new password
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        } else {
            // If no new password is provided, keep the old password
            $newHashedPassword = $currentHashedPassword;
        }

        // Update the username and password in the database
        $updateStmt = $conn->prepare("UPDATE useremployee SET username = ?, password = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $newUsername, $newHashedPassword, $srfId);

        if ($updateStmt->execute()) {
            echo "User details updated successfully.";
        } else {
            echo "Error updating user details: " . $conn->error;
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
