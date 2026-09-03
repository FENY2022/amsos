<?php

require_once 'connect.php';
require_once 'session_checker.php';

header('Content-Type: application/json');

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(503);
    echo json_encode(array('error' => 'Pusher dependency is not installed'));
    exit;
}

require_once $autoload;

$socketId = $_POST['socket_id'] ?? '';
$channelName = $_POST['channel_name'] ?? '';
$userId = isset($_SESSION['idSRF']) ? (int)$_SESSION['idSRF'] : 0;
$office = trim((string)($_SESSION['OfficeSRF'] ?? ''));
$allowedChannels = array('private-srf-request-user-' . $userId);

if ($office !== '') {
    $allowedChannels[] = 'private-srf-waiting-office-' . sha1($office);
}

if ($userId <= 0 || $socketId === '' || !in_array($channelName, $allowedChannels, true)) {
    http_response_code(403);
    echo json_encode(array('error' => 'Forbidden'));
    exit;
}

$config = require 'pusher_config.php';
if (!class_exists('Pusher\Pusher')) {
    http_response_code(503);
    echo json_encode(array('error' => 'Pusher dependency is not available'));
    exit;
}

try {
    $pusher = new Pusher\Pusher(
        $config['app_key'],
        $config['app_secret'],
        $config['app_id'],
        array(
            'cluster' => $config['cluster'],
            'useTLS' => $config['useTLS'],
        )
    );

    echo $pusher->authorizeChannel($channelName, $socketId);
} catch (Throwable $e) {
    http_response_code(503);
    error_log('Pusher auth failed: ' . $e->getMessage());
    echo json_encode(array('error' => 'Pusher authentication failed'));
}
