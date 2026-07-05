<?php
require_once 'calendarSchedulerdb.php';

function respond_event($success, $message, $code = 200)
{
    http_response_code($code);
    $payload = ['success' => $success, 'message' => $message];

    if ((isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode($payload);
        return;
    }

    echo "<script>alert('" . addslashes($message) . "'); window.location.href = 'mainmenu.php?dir=calendarScheduler';</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_date = trim($_POST['event_date'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $zoom_link = trim($_POST['zoom_link'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($event_date === '' || $remarks === '' || $password === '' || $email === '') {
        respond_event(false, 'Please complete all required fields.', 422);
        exit;
    }

    $sql = "INSERT INTO events (event_date, remarks, zoom_link, password, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        respond_event(false, 'Unable to prepare the event query.', 500);
        exit;
    }

    $stmt->bind_param('sssss', $event_date, $remarks, $zoom_link, $password, $email);

    if ($stmt->execute()) {
        respond_event(true, 'Event added successfully.');
    } else {
        respond_event(false, 'Error saving the event.', 500);
    }

    $stmt->close();
}

$conn->close();
