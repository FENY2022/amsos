<?php
// fetch_events.php

require_once 'calendarSchedulerdb.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, event_date, remarks, zoom_link, password, email FROM events");
    $stmt->execute();
    $result = $stmt->get_result();

    $formattedEvents = [];

    while ($event = $result->fetch_assoc()) {
        $formattedEvent = [
            'id' => $event['id'],
            'title' => $event['remarks'],
            'start' => $event['event_date'],
            'extendedProps' => [
                'zoom_link' => $event['zoom_link'],
                'email' => $event['email']
            ]
        ];

        // Remove password for security reasons
        // $formattedEvent['extendedProps']['password'] = $event['password']; // Uncomment only if needed securely

        $formattedEvents[] = $formattedEvent;
    }

    echo json_encode($formattedEvents);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
    error_log("Database Query Error: " . $e->getMessage()); // Log for debugging
}

$conn->close();
?>
