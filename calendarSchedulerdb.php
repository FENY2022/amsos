<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$database = "amsos";

// Create a new MySQLi object.
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful.
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


if (session_status() == PHP_SESSION_NONE) {
  session_start();
 }


date_default_timezone_set('Asia/Manila');


?>