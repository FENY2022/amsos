<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != $_SESSION['usernameSRF']) {
    // Destroy the session
    session_unset(); // Clear session variables
    session_destroy(); // Destroy the session
    // Redirect to the login page or another page
    header("Location: logout.php");
exit();
}
?>
