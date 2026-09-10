
<?php


// Set the database connection parameters.
$servername = "localhost";
$username = "u645536029_ict_amsos_user";
$password = "9Ad=:C~WJ>";
$database = "u645536029_ict_amsos_db";

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
