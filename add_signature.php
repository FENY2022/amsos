<?php
require_once 'connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $uploadDir = 'srfsigner/'; // Directory to store uploaded signatures

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['signature']['tmp_name'];
        $fileName = $id . '_' . basename($_FILES['signature']['name']);
        $uploadPath = $uploadDir . $fileName;

        // Move file to the designated directory
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Save file path to database
            $stmt = $conn->prepare("UPDATE srfsigner SET signature = ? WHERE id = ?");
            $stmt->bind_param('si', $uploadPath, $id);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Signature uploaded successfully!');
                    window.location.href = 'viewassigntracking.php'; // Change to your desired redirect page
                </script>";
            } else {
                echo "<script>
                    alert('Database update failed.');
                    window.history.back();
                </script>";
            }
        } else {
            echo "<script>
                alert('Failed to move the uploaded file.');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('No file uploaded or an error occurred.');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request method.');
        window.history.back();
    </script>";
}
?>
