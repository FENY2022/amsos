<?php
session_start();
require_once 'connect.php';

$idSRF = $_SESSION['idSRF'];

// Get the previous count from the session (default to 0 if not set)
$previousCount = isset($_SESSION['previousCount']) ? $_SESSION['previousCount'] : 0;

$query = "SELECT COUNT(*) AS total FROM srf WHERE tracking = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $idSRF);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$currentCount = $row['total'];

// Store the current count in the session
$_SESSION['previousCount'] = $currentCount;

// Return whether the count has increased
echo json_encode(["newRecord" => $currentCount > $previousCount]);

$stmt->close();
$conn->close();
?>
