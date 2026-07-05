<?php

function calendarColumnExists(mysqli $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $exists = !empty($row['cnt']);
    $stmt->close();

    return $exists;
}

function calendarEnsureEventSchema(mysqli $conn): void
{
    $migrations = [
        'source_srf_id' => "ALTER TABLE `events` ADD COLUMN `source_srf_id` INT NULL AFTER `id`",
        'meeting_id' => "ALTER TABLE `events` ADD COLUMN `meeting_id` VARCHAR(150) DEFAULT NULL AFTER `zoom_link`",
        'office' => "ALTER TABLE `events` ADD COLUMN `office` VARCHAR(255) DEFAULT NULL AFTER `meeting_id`",
        'divSecUnit' => "ALTER TABLE `events` ADD COLUMN `divSecUnit` TEXT DEFAULT NULL AFTER `office`"
    ];

    foreach ($migrations as $column => $sql) {
        if (!calendarColumnExists($conn, 'events', $column)) {
            $conn->query($sql);
        }
    }
}

function calendarUpsertEventFromSrf(mysqli $conn, array $data): bool
{
    calendarEnsureEventSchema($conn);

    $sourceSrfId = (int)($data['source_srf_id'] ?? 0);
    $eventDate = trim((string)($data['event_date'] ?? ''));
    $remarks = trim((string)($data['remarks'] ?? ''));
    $zoomLink = trim((string)($data['zoom_link'] ?? ''));
    $meetingId = trim((string)($data['meeting_id'] ?? ''));
    $password = trim((string)($data['password'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $office = trim((string)($data['office'] ?? ''));
    $divSecUnit = trim((string)($data['divSecUnit'] ?? ''));

    if ($sourceSrfId <= 0 || $eventDate === '' || $remarks === '' || $meetingId === '' || $password === '' || $email === '') {
        return false;
    }

    $existing = $conn->prepare('SELECT id FROM events WHERE source_srf_id = ? LIMIT 1');
    if (!$existing) {
        return false;
    }

    $existing->bind_param('i', $sourceSrfId);
    $existing->execute();
    $result = $existing->get_result();
    $eventId = null;

    if ($row = $result ? $result->fetch_assoc() : null) {
        $eventId = (int)$row['id'];
    }

    $existing->close();

    if ($eventId) {
        $stmt = $conn->prepare('UPDATE events SET event_date = ?, remarks = ?, zoom_link = ?, meeting_id = ?, password = ?, email = ?, office = ?, divSecUnit = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssssssssi', $eventDate, $remarks, $zoomLink, $meetingId, $password, $email, $office, $divSecUnit, $eventId);
    } else {
        $stmt = $conn->prepare('INSERT INTO events (source_srf_id, event_date, remarks, zoom_link, meeting_id, password, email, office, divSecUnit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('issssssss', $sourceSrfId, $eventDate, $remarks, $zoomLink, $meetingId, $password, $email, $office, $divSecUnit);
    }

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
