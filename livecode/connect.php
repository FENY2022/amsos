
<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

date_default_timezone_set('Asia/Manila');

if (isset($conn) && $conn instanceof mysqli && @$conn->ping()) {
  return;
}

mysqli_report(MYSQLI_REPORT_OFF);

$requestHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$parsedHost = parse_url('http://' . $requestHost, PHP_URL_HOST);
$requestHost = strtolower(trim($parsedHost ?: $requestHost, '[]'));
$isLocalServer = in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true);

// Use local AMSOS data during XAMPP development; keep live credentials for production.
if ($isLocalServer) {
  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "amsos";
} else {
  $servername = "153.92.15.60";
  $username = "u645536029_ict_amsos_user";
  $password = "9Ad=:C~WJ>";
  $database = "u645536029_ict_amsos_db";
}

// Persistent connections reduce repeated MySQL logins that can hit hourly limits.
$conn = new mysqli('p:' . $servername, $username, $password, $database);

// Check if the connection was successful.
if ($conn->connect_error) {
  http_response_code(503);
  if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'AMSOS database is temporarily unavailable. Please try again later.']);
    exit;
  }
  die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

?>
