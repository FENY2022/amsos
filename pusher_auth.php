<?php

require_once 'connect.php';
require_once 'session_checker.php';
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

$socketId = $_POST['socket_id'] ?? '';
$channelName = $_POST['channel_name'] ?? '';
$userId = isset($_SESSION['idSRF']) ? (int)$_SESSION['idSRF'] : 0;
$allowedChannel = 'private-srf-request-user-' . $userId;

if ($userId <= 0 || $socketId === '' || $channelName !== $allowedChannel) {
    http_response_code(403);
    echo json_encode(array('error' => 'Forbidden'));
    exit;
}

$config = require 'pusher_config.php';
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
