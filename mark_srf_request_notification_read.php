<?php

require_once 'connect.php';
require_once 'session_checker.php';
require_once 'srf_request_notification_helpers.php';

$userId = isset($_SESSION['idSRF']) ? (int)$_SESSION['idSRF'] : 0;
$srfId = isset($_GET['srf_id']) ? (int)$_GET['srf_id'] : 0;

if ($userId > 0 && $srfId > 0) {
    markSrfRequestNotificationRead($conn, $userId, $srfId);
}

header('Location: mainmenu.php?dir=requestlist');
exit;
