<?php
// Start the session at the very beginning of the file
session_start();

// Include the database connection file
include 'connect.php'; // Ensure this path is correct

// Set error reporting for development (remove or adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize the submitted data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0; // Ensure ID is an integer
    $newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    // Initialize variables for toast message and type
    $toastMessage = '';
    $toastType = 'danger'; // Default to danger for errors

    // Validate the input
    if ($id <= 0) {
        $toastMessage = 'Invalid user ID provided.';
    } elseif (empty($newPassword)) {
        $toastMessage = 'New password cannot be empty.';
    } elseif (strlen($newPassword) < 8) {
        $toastMessage = 'Password must be at least 8 characters long.';
    }

    // If there's an validation error, set toast and redirect
    if (!empty($toastMessage)) {
        $_SESSION['toast_message'] = $toastMessage;
        $_SESSION['toast_type'] = $toastType; // This will be 'danger' due to default
        header('Location: mainmenu.php?dir=assignactionstaff');
        exit();
    }

    // Hash the new password for security
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Prepare the SQL query to update username, hashed password, and the plain-text password
    // It's generally NOT recommended to store plain-text passwords (password_dcryp).
    // Consider if 'password_dcryp' is truly necessary for your application's security model.
    $query = "UPDATE useremployee SET username = ?, password = ?, password_dcryp = ? WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        // Bind parameters to the prepared statement
        $stmt->bind_param('sssi', $username, $hashedPassword, $newPassword, $id);

        // Execute the query
        if ($stmt->execute()) {
            $toastMessage = 'Password updated successfully for user: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            $toastType = 'success'; // Success toast
        } else {
            // Log the error for debugging
            error_log('Error updating password: ' . $stmt->error);
            $toastMessage = 'Error updating password. Please try again later.';
            $toastType = 'danger'; // Error toast
        }

        // Close the statement
        $stmt->close();
    } else {
        // Log the error for debugging
        error_log('Error preparing the query: ' . $conn->error);
        $toastMessage = 'Error preparing the database query. Please try again later.';
        $toastType = 'danger'; // Error toast
    }

    // Close the database connection
    $conn->close();

    // Set session variables for the toast before redirecting
    $_SESSION['toast_message'] = $toastMessage;
    $_SESSION['toast_type'] = $toastType;

    // Redirect to the desired page
    header('Location: mainmenu.php?dir=assignactionstaff');
    exit(); // Always exit after a header redirect
} else {
    // If the request method is not POST, set toast for invalid access
    session_start(); // Ensure session is started for this case too
    $_SESSION['toast_message'] = 'Invalid request method. Please use the form to update passwords.';
    $_SESSION['toast_type'] = 'danger';
    header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect anyway
    exit();
}
?>