
<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

date_default_timezone_set('Asia/Manila');

if (isset($conn) && $conn instanceof mysqli && @$conn->ping()) {
  return;
}

// Set the database connection parameters.
$servername = "153.92.15.60";
$username = "u645536029_ict_amsos_user";
$password = "9Ad=:C~WJ>";
$database = "u645536029_ict_amsos_db";

// Persistent connections reduce repeated MySQL logins that can hit hourly limits.
$conn = new mysqli('p:' . $servername, $username, $password, $database);

// Check if the connection was successful.
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

?>
