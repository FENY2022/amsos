<?php
// Database configuration
$host     = "localhost";  // Database host
$username = "root";       // Database username
$password = "";           // Database password
$dbname   = "to_caraga";  // Database name to backup

// File name for the backup with .sql extension
$backupFile = $dbname . "_backup_" . date("Y-m-d_H-i-s") . ".sql";

// Path to mysqldump (optional, only needed if it's not in the system's PATH)
$mysqldumpPath = "mysqldump"; // or full path like '/usr/bin/mysqldump'

// Construct the mysqldump command
$command = "$mysqldumpPath --host=$host --user=$username --password=$password $dbname";

// Execute the mysqldump command and capture output and errors
$backupOutput = shell_exec($command . " 2>&1");

// Check if the backup was successful
if ($backupOutput === null) {
    // If the command failed or returned null, show an error message
    header('Content-Type: text/plain');
    echo "Error: Could not execute the backup. Please check the mysqldump path and database credentials.";
    exit();
} elseif (strpos($backupOutput, 'mysqldump:') !== false) {
    // If mysqldump returned an error (e.g., database doesn't exist or wrong credentials)
    header('Content-Type: text/plain');
    echo "Error during backup:\n" . $backupOutput;
    exit();
} else {
    // If successful, send the backup file for download
    header('Content-Type: application/sql'); // Content type for .sql files
    header('Content-Disposition: attachment; filename="' . $backupFile . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output the backup content (SQL dump) for download
    echo $backupOutput;
    exit();
}
?>
