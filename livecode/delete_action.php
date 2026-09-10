<?php
header('Content-Type: application/json');

// Include your database connection file.
// This file should create and set the $conn variable. Example: new mysqli($host, $user, $password, $dbname);
require_once 'connect.php';

// Retrieve the raw POST data and decode the JSON.
$data = json_decode(file_get_contents('php://input'));

// Check if the 'id' is provided.
if (!isset($data->id)) {
    echo json_encode(['success' => false, 'error' => 'Record ID not provided']);
    exit;
}

// Sanitize and assign the id.
$id = intval($data->id);

// Prepare the SQL delete statement.
$query = "DELETE FROM srf_actiontaken WHERE id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {        
        // Check if any row was actually deleted.
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No record found with the given ID']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Query execution failed']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare the SQL statement']);
}

$conn->close();
?>
