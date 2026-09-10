<?php
$requestHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$parsedHost = parse_url('http://' . $requestHost, PHP_URL_HOST);
$requestHost = strtolower(trim($parsedHost ?: $requestHost, '[]'));

$isLocalServer = in_array($requestHost, array('localhost', '127.0.0.1', '::1'), true);

require_once $isLocalServer ? 'connect_amsos.php' : 'connect.php';
?>
