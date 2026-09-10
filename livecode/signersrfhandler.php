<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include database connection
require_once 'connect.php'; // Adjust the path as needed

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $personelid = $_POST['personelid'];
    $name = $_POST['name'];
    $level = $_POST['level'];
    $office = $_POST['office'];
    $stationid = $_POST['stationid'];
    $role = $_POST['role'];
    $station = $_POST['Station'];
    $position = $_POST['position'];

    // Validate and sanitize input
    $personelid = htmlspecialchars(trim($personelid));
    $name = htmlspecialchars(trim($name));
    $level = filter_var($level, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    $office = htmlspecialchars(trim($office));
    $stationid = htmlspecialchars(trim($stationid));
    $role = htmlspecialchars(trim($role));
    $station = htmlspecialchars(trim($station));

    // Check if input is valid
    if ($level === false) {
        die('Invalid level input.');
    }


    // Prepare SQL statement for inserting a new record
    $sql = "INSERT INTO srfsigner (personelid, name, level, Office, Station, role, stationid, position) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssis", $personelid, $name, $level, $office, $station, $role, $stationid, $position);

        if ($stmt->execute()) {
     
            echo '<script>alert("Record inserted successfully."); window.location.href = "mainmenu.php?dir=assigntracking";</script>';
       

        } else {
            echo "Error inserting record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
