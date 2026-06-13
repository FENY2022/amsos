<?php
session_start();
require 'connect.php';
require 'connect_otos.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("=== Login attempt ===");

header('Content-Type: application/json; charset=utf-8');

function json_response($success, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    json_response(false, 'Please fill in both fields.');
}

if (!$conn_otos || $conn_otos->connect_error) {
    error_log("DB connection error (otos): " . ($conn_otos->connect_error ?? 'no connection'));
    json_response(false, 'Database connection error. Please try again later.');
}

$stmt = $conn_otos->prepare('SELECT id, Full_Name, Office, Station, Profile_Link, User_Role, username, password FROM useremployee WHERE username = ?');
if (!$stmt) {
    error_log("Prepare failed (otos): " . $conn_otos->error);
    json_response(false, 'Database query error.');
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    json_response(false, 'Invalid username or password.');
}

$_SESSION["loggedin"] = $user['username'];
$_SESSION['idSRF'] = $user['id'];
$_SESSION['usernameSRF'] = $user['username'];
$_SESSION['Full_NameSRF'] = $user['Full_Name'];
$_SESSION['OfficeSRF'] = $user['Office'];
$_SESSION['StationSRF'] = $user['Station'];
$_SESSION['Profile_LinkSRF'] = $user['Profile_Link'];
$_SESSION['User_RoleSRF'] = $user['User_Role'];
$Station = $user['Station'];

$Endsrd = "";
$stmt2 = $conn->prepare("SELECT * FROM signatory_setup WHERE Station = ? AND date_endservice = ?");
if (!$stmt2) {
    error_log("Prepare failed (local): " . $conn->error);
    json_response(false, 'Station configuration error.');
}

$stmt2->bind_param("ss", $Station, $Endsrd);
$stmt2->execute();
$result2 = $stmt2->get_result();
$signatories = $result2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

if (empty($signatories)) {
    error_log("No signatory found for station: $Station");
    json_response(false, 'No signatory records found for your station.');
}

foreach ($signatories as $row) {
    $_SESSION['StationidSRF'] = htmlspecialchars($row['id']);
    break;
}

json_response(true, 'Login successful. Redirecting...');
?>