<?php
require_once 'connect.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->id)) {
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $data->id);

    if ($stmt->execute()) {
        echo "Event deleted successfully.";
    } else {
        echo "Error deleting event.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
