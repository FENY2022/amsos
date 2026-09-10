<?php
require_once 'connect.php';

if (isset($_GET['srfId'])) {
    $srfId = intval($_GET['srfId']);

    // Fetch messages
    $query = $conn->prepare("SELECT sender, message, created_at FROM srf_notification WHERE srfId = ? ORDER BY created_at ASC");
    $query->bind_param("i", $srfId);
    $query->execute();
    $result = $query->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'sender' => htmlspecialchars($row['sender']),
            'message' => html_entity_decode($row['message']),
            'created_at' => htmlspecialchars($row['created_at'])
        ];
    }

    $query->close();

    // Update notification read status
    $stmt = $conn->prepare("UPDATE srf SET Notification_read = 0 WHERE id = ?");
    $stmt->bind_param("i", $srfId);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Error updating record: ' . $stmt->error]);
        $stmt->close();
        exit;
    }

    $stmt->close();

    // Return messages as JSON
    echo json_encode($messages);
} else {
    echo json_encode(['error' => 'srfId parameter is missing']);
}
?>
