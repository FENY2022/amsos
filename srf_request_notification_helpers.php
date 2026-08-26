<?php

function ensureSrfRequestNotificationReadsTable(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS srf_request_notification_reads (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        srf_id INT NOT NULL,
        read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_user_srf_read (user_id, srf_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    if (!$conn->query($sql)) {
        error_log('Unable to ensure SRF request notification reads table: ' . $conn->error);
    }
}

function getUnreadSrfRequestNotificationCount(mysqli $conn, int $userId): int
{
    ensureSrfRequestNotificationReadsTable($conn);

    $sql = "SELECT COUNT(*) AS unread_count
            FROM srf s
            LEFT JOIN srf_request_notification_reads r
                ON r.srf_id = s.id AND r.user_id = ?
            WHERE s.tracking = ? AND r.id IS NULL";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Unable to prepare SRF unread notification count query: ' . $conn->error);
        return 0;
    }

    $stmt->bind_param('ii', $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return (int)($row['unread_count'] ?? 0);
}

function getUnreadSrfRequestNotifications(mysqli $conn, int $userId, int $limit = 5): array
{
    ensureSrfRequestNotificationReadsTable($conn);

    $sql = "SELECT s.id, s.ticketNumber, s.name, s.requestType, s.status, s.created_at
            FROM srf s
            LEFT JOIN srf_request_notification_reads r
                ON r.srf_id = s.id AND r.user_id = ?
            WHERE s.tracking = ? AND r.id IS NULL
            ORDER BY s.created_at DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Unable to prepare SRF unread notification list query: ' . $conn->error);
        return array();
    }

    $stmt->bind_param('iii', $userId, $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = array();

    while ($result && ($row = $result->fetch_assoc())) {
        $notifications[] = $row;
    }

    $stmt->close();
    return $notifications;
}

function markSrfRequestNotificationRead(mysqli $conn, int $userId, int $srfId): bool
{
    ensureSrfRequestNotificationReadsTable($conn);

    $checkSql = "SELECT id FROM srf WHERE id = ? AND tracking = ? LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        error_log('Unable to prepare SRF notification ownership check: ' . $conn->error);
        return false;
    }

    $checkStmt->bind_param('ii', $srfId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $canRead = $checkResult && $checkResult->num_rows > 0;
    $checkStmt->close();

    if (!$canRead) {
        return false;
    }

    $insertSql = "INSERT IGNORE INTO srf_request_notification_reads (user_id, srf_id) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        error_log('Unable to prepare SRF notification read insert: ' . $conn->error);
        return false;
    }

    $insertStmt->bind_param('ii', $userId, $srfId);
    $ok = $insertStmt->execute();
    $insertStmt->close();

    return $ok;
}

function markAllSrfRequestNotificationsRead(mysqli $conn, int $userId): bool
{
    ensureSrfRequestNotificationReadsTable($conn);

    $sql = "INSERT IGNORE INTO srf_request_notification_reads (user_id, srf_id)
            SELECT ?, id FROM srf WHERE tracking = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Unable to prepare SRF notification read-all insert: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('ii', $userId, $userId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function triggerSrfRequestNotification(mysqli $conn, int $userId, int $srfId, string $fallbackStatus = ''): bool
{
    if ($userId <= 0 || $srfId <= 0) {
        return false;
    }

    ensureSrfRequestNotificationReadsTable($conn);

    $deleteStmt = $conn->prepare('DELETE FROM srf_request_notification_reads WHERE user_id = ? AND srf_id = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('ii', $userId, $srfId);
        $deleteStmt->execute();
        $deleteStmt->close();
    }

    $sql = 'SELECT id, ticketNumber, name, requestType, status FROM srf WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Unable to prepare SRF notification payload query: ' . $conn->error);
        return false;
    }

    $stmt->bind_param('i', $srfId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return false;
    }

    try {
        require_once __DIR__ . '/vendor/autoload.php';

        if (!class_exists('Pusher\Pusher')) {
            return false;
        }

        $pusherConfig = require __DIR__ . '/pusher_config.php';
        $pusher = new Pusher\Pusher(
            $pusherConfig['app_key'],
            $pusherConfig['app_secret'],
            $pusherConfig['app_id'],
            array(
                'cluster' => $pusherConfig['cluster'],
                'useTLS' => $pusherConfig['useTLS'],
            )
        );

        $pusher->trigger('private-srf-request-user-' . $userId, 'new-srf-request', array(
            'srf_id' => (int)$row['id'],
            'ticketNumber' => $row['ticketNumber'],
            'name' => $row['name'],
            'requestType' => $row['requestType'],
            'status' => $row['status'] !== '' ? $row['status'] : $fallbackStatus,
            'link' => 'mainmenu.php?dir=requestlist',
        ));

        return true;
    } catch (Exception $e) {
        error_log('Pusher SRF request notification error: ' . $e->getMessage());
        return false;
    }
}
