<?php
// Include database connection
include 'connect.php'; // Replace with your actual database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['signature_file']) && isset($_POST['id'])) {
        $id = intval($_POST['id']); // Sanitize ID
        $file = $_FILES['signature_file'];

        // Validate the uploaded file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type and size
        if (in_array($file_extension, $allowed_extensions) && $file['size'] <= 5 * 1024 * 1024) {
            $upload_dir = 'srfsigner/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = uniqid('signature_', true) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("UPDATE srfactionstaff SET signature = ? WHERE id = ?");
                $stmt->bind_param('si', $new_file_name, $id);

                if ($stmt->execute()) {
                    // Success: Use a session variable to pass the message
                    session_start();
                    $_SESSION['toast_message'] = 'Signature uploaded and updated successfully!';
                    $_SESSION['toast_type'] = 'success';
                    header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect to avoid re-submission
                    exit();
                } else {
                    // Database update error
                    error_log("Database update error: " . $conn->error, 3, 'errors.log');
                    session_start();
                    $_SESSION['toast_message'] = 'Error updating the database. Please try again later.';
                    $_SESSION['toast_type'] = 'danger';
                    header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect
                    exit();
                }

                $stmt->close();
            } else {
                // Error moving file
                session_start();
                $_SESSION['toast_message'] = 'Error moving the uploaded file. Please try again.';
                $_SESSION['toast_type'] = 'danger';
                header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect
                exit();
            }
        } else {
            // Invalid file type or size
            session_start();
            $_SESSION['toast_message'] = 'Invalid file type or file size exceeds 5MB.';
            $_SESSION['toast_type'] = 'warning'; // Changed to warning for user input error
            header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect
            exit();
        }
    } else {
        // Invalid request - file or ID missing
        session_start();
        $_SESSION['toast_message'] = 'Invalid request. File or ID missing.';
        $_SESSION['toast_type'] = 'danger';
        header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect
        exit();
    }
} else {
    // Invalid request method
    session_start();
    $_SESSION['toast_message'] = 'Invalid request method.';
    $_SESSION['toast_type'] = 'danger';
    header('Location: mainmenu.php?dir=assignactionstaff'); // Redirect
    exit();
}

$conn->close();
?>