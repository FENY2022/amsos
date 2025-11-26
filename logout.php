<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
   }
   
session_unset();
session_destroy();
header("Location: login.php"); // Redirect to login page after logging out
exit();
?>
