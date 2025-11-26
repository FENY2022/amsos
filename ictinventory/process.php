<?php

require_once "connect.php" ;
// Check if the form is submitted
if (isset($_POST["save"])) {
    // Get form data
    $name = $_POST["name"];
    $position = $_POST["position"];
    $designation = $_POST["designation"];
    $asset_class = $_POST["asset_class"];
    $assigned_to = $_POST["assigned_to"];
    $nature_of_work = $_POST["nature_of_work"];
    $specification = $_POST["specification"];
    $license_type = $_POST["license_type"];
    $year_acquired = $_POST["year_acquired"];
    $production_date = $_POST["production_date"];
    $shelf_life = $_POST["shelf_life"];
    $suitable_specifications = $_POST["suitable_specifications"];
    $total_number_assigned = $_POST["total_number_assigned"];
    $findings = $_POST["findings"];
    $recommendations = $_POST["recommendations"];

    // Replace the database connection details with your actual values

    // Create connection

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to insert data into the database
    $sql = "INSERT INTO addinventory (name, position, designation, asset_class, assigned_to, nature_of_work, specification, license_type, year_acquired, production_date, shelf_life, suitable_specifications, total_number_assigned, findings, recommendations)
    VALUES ('$name', '$position', '$designation', '$asset_class', '$assigned_to', '$nature_of_work', '$specification', '$license_type', '$year_acquired', '$production_date', '$shelf_life', '$suitable_specifications', '$total_number_assigned', '$findings', '$recommendations')";

    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Form submitted successfully!");</script>';
        echo '<script>window.location.href = "dashboard.php";</script>';
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
