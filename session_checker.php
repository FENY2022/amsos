<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != $_SESSION['usernameSRF']) {
    // Destroy the session
    session_unset(); // Clear session variables
    session_destroy(); // Destroy the session
    // Redirect to the login page using JavaScript
    echo "<script>window.top.location.href = 'logout.php';</script>";
    exit();
}
?>
