<?php
require_once 'connect.php';

if (isset($_GET['srfId'])) {
    $srfId = intval($_GET['srfId']);
    $query = $conn->prepare("SELECT sender, message, created_at FROM srf_notification WHERE srfId = ? ORDER BY created_at ASC");
    $query->bind_param("i", $srfId);
    $query->execute();
    $result = $query->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'sender' => htmlspecialchars($row['sender']),
            'message' => stripslashes($row['message']),
            'created_at' => htmlspecialchars($row['created_at'])
        ];
    }
    echo json_encode($messages);
}
?>
