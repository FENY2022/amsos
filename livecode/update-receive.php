<?php
require_once 'connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';

    if (!empty($id) && !empty($name) && !empty($date) && !empty($time)) {
        $stmt = $conn->prepare("UPDATE srfhistory SET name = ?, date = ?, time = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $date, $time, $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Missing fields"]);
    }
}
$conn->close();
