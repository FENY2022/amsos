<?php

require_once "connect.php";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $srfId = $_POST['srfId'];
    $documentName = $_POST['documentName'];

    // Handle file upload
    $targetDir = "attached_documents/";
    $targetFile = $targetDir . basename($_FILES["documentFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (e.g., max 5MB)
    if ($_FILES["documentFile"]["size"] > 20000000) { 
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats (e.g., pdf, docx, jpg)
    $allowedTypes = array("pdf", "docx", "jpg", "jpeg", "png");
    if (!in_array($fileType, $allowedTypes)) {
        echo "Sorry, only PDF, DOCX, JPG, JPEG & PNG files are allowed.";
        $uploadOk = 0;
    }

    // Upload file if everything is ok
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["documentFile"]["tmp_name"], $targetFile)) {
            // File uploaded successfully, now update the database

            // Get the current documents from the database
            $stmt = $conn->prepare("SELECT documents FROM srf WHERE id = ?");
            $stmt->bind_param("i", $srfId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $currentDocuments = $row['documents'];

            // Prepare the new document entry
            $newDocument = basename($_FILES["documentFile"]["name"]);
            if (!empty($currentDocuments)) {
                $updatedDocuments = $currentDocuments . ',' . $newDocument;
            } else {
                $updatedDocuments = $documentName.$newDocument;
            }

            // Update the documents field in the database
            $stmt = $conn->prepare("UPDATE srf SET documents = ? WHERE id = ?");
            $stmt->bind_param("si", $updatedDocuments, $srfId);

            if ($stmt->execute()) {
                echo "The file " . htmlspecialchars(basename($_FILES["documentFile"]["name"])) . " has been uploaded and the database has been updated.";
            } else {
                echo "Error updating the database: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>