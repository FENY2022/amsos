<?php
// fetch_events.php

require_once 'calendarSchedulerdb.php';
require_once 'calendar_event_helpers.php';

header('Content-Type: application/json');

try {
    calendarEnsureEventSchema($conn);

    $stmt = $conn->prepare("SELECT id, source_srf_id, event_date, event_datetime, remarks, zoom_link, meeting_id, password, email, office, divSecUnit FROM events");
    $stmt->execute();
    $result = $stmt->get_result();

    $formattedEvents = [];

    while ($event = $result->fetch_assoc()) {
        $start = !empty($event['event_datetime']) ? str_replace(' ', 'T', $event['event_datetime']) : $event['event_date'];
        $formattedEvent = [
            'id' => $event['id'],
            'title' => $event['remarks'],
            'start' => $start,
            'extendedProps' => [
                'source_srf_id' => $event['source_srf_id'],
                'zoom_link' => $event['zoom_link'],
                'meeting_id' => $event['meeting_id'],
                'password' => $event['password'],
                'email' => $event['email'],
                'office' => $event['office'],
                'divSecUnit' => $event['divSecUnit']
            ]
        ];

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
